<?php
/**
 * REST access to 后台「客户询盘 → 表单设置」里的三套询盘表单字段。
 *
 * 字段配置存在 springapex_form_schema option，core 接口不暴露它。这里提供与
 * 后台保存同一套规范化（springapex_form_schema_from_input）的读写接口：
 *
 *   GET  /wp-json/springapex/v1/form-schema   三个表单当前生效的配置
 *   POST /wp-json/springapex/v1/form-schema   {"forms": {"product": {"fields": [...]}}}
 *                                             只改传入的表单和键
 *
 * 每个表单可传 enabled、turnstile（布尔）和 fields（字段数组）。下拉的 options 是
 * {"值": "显示文字"} 对象，与 GET 返回的形状一致。fields 整份替换：
 * 改一个选项也要先 GET，再把该表单的整份字段列表发回。锁定字段（姓名/邮箱/留言）
 * 缺失时照后台规则补回。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'springapex_rest_form_schema_routes');

function springapex_rest_form_schema_routes(): void
{
    // 与后台「表单设置」页同一权限。
    $permission = static fn(): bool => current_user_can('manage_options');

    register_rest_route('springapex/v1', '/form-schema', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'springapex_rest_form_schema_get',
            'permission_callback' => $permission,
        ],
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'springapex_rest_form_schema_update',
            'permission_callback' => $permission,
            'args' => [
                'forms' => [
                    'required' => true,
                    'type' => 'object',
                ],
            ],
        ],
    ]);
}

function springapex_rest_form_schema_get(): WP_REST_Response
{
    return new WP_REST_Response([
        'forms' => springapex_form_schema(),
        'field_types' => array_keys(springapex_form_field_types()),
        'locked_fields' => springapex_form_locked_fields(),
    ]);
}

function springapex_rest_form_schema_update(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $submitted = springapex_rest_form_schema_validate($request->get_param('forms'));
    if ($submitted instanceof WP_Error) {
        return $submitted;
    }

    // 先读一次生效配置：结构版本落后时 springapex_form_schema() 会就地迁移并写回，
    // 放在循环外做完，循环里读到的就是迁移后的值，CAS 基准与合并底稿一致。
    springapex_form_schema();

    // 读-合并-写走 compare-and-swap（inc/locks.php），与 rest-seo 同一做法：
    // 后台或另一个请求同时保存时，基于旧快照的这次写入会失败并重读重算，
    // 不会把对方刚存的表单整份覆盖。重试耗尽回 409，不回 saved: true。
    $saved = false;
    for ($attempt = 0; $attempt < 5 && !$saved; $attempt++) {
        wp_cache_delete('springapex_form_schema', 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');

        $exists = springapex_option_row_exists('springapex_form_schema');
        $stored = $exists ? get_option('springapex_form_schema', []) : [];
        // springapex_form_schema() 读的是刚载入缓存的同一份 $stored。
        $clean = springapex_rest_form_schema_merge(springapex_form_schema(), $submitted);

        if ($exists && $clean === $stored) {
            $saved = true;
        } elseif (!$exists) {
            $saved = springapex_add_option_if_absent('springapex_form_schema', $clean, false);
        } else {
            $saved = springapex_update_option_if_unchanged('springapex_form_schema', $stored, $clean, false);
        }
    }
    if (!$saved) {
        return new WP_Error(
            'springapex_form_schema_not_saved',
            'Form settings were not saved; they kept changing or the database write failed. Retry the request.',
            ['status' => 409]
        );
    }

    // 与后台保存一致：本次保存即当前结构版本，上一代配置退役。
    update_option('springapex_form_schema_version', SPRINGAPEX_FORM_SCHEMA_VERSION, false);
    delete_option('springapex_form_config');
    delete_option('springapex_form_fields');

    return new WP_REST_Response([
        'saved' => true,
        'forms' => array_intersect_key(springapex_form_schema(), $submitted),
    ]);
}

/**
 * 校验并整理提交的表单。未知表单/键、非布尔开关、无效或重复的字段 id 都报 400，
 * 而不是像后台那样静默丢弃：接口调用方看不到页面，丢了字段也不会知道。
 *
 * @return array<string, array<string, mixed>>|WP_Error
 */
function springapex_rest_form_schema_validate(mixed $forms): array|WP_Error
{
    if (!is_array($forms) || $forms === []) {
        return new WP_Error('springapex_form_schema_empty', 'forms must be a non-empty object.', ['status' => 400]);
    }

    $defaults = springapex_form_schema_defaults();
    $types = springapex_form_field_types();
    $clean = [];
    foreach ($forms as $form => $entry) {
        $form = (string) $form;
        if (!isset($defaults[$form])) {
            return new WP_Error('springapex_form_schema_unknown_form', sprintf('Unknown form: %s', $form), ['status' => 400]);
        }
        if (!is_array($entry) || $entry === []) {
            return new WP_Error('springapex_form_schema_invalid_form', sprintf('Form %s must be a non-empty object.', $form), ['status' => 400]);
        }

        $allowed = isset($defaults[$form]['enabled']) ? ['enabled', 'turnstile', 'fields'] : ['turnstile', 'fields'];
        foreach ($entry as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                return new WP_Error(
                    'springapex_form_schema_invalid_key',
                    sprintf('Form %s accepts %s only.', $form, implode(', ', $allowed)),
                    ['status' => 400]
                );
            }
            if ($key !== 'fields' && !is_bool($value)) {
                return new WP_Error('springapex_form_schema_invalid_flag', sprintf('Form %s: %s must be true or false.', $form, $key), ['status' => 400]);
            }
        }

        if (array_key_exists('fields', $entry)) {
            // 不用 array_is_list()：它要 PHP 8.1，主题仍声明支持 PHP 8.0。
            if (!is_array($entry['fields']) || array_values($entry['fields']) !== $entry['fields']) {
                return new WP_Error('springapex_form_schema_invalid_fields', sprintf('Form %s: fields must be an array.', $form), ['status' => 400]);
            }
            $defaults_by_id = [];
            foreach ($defaults[$form]['fields'] as $field) {
                $defaults_by_id[$field['id']] = $field;
            }
            $seen = [];
            foreach ($entry['fields'] as $index => $row) {
                if (!is_array($row)) {
                    return new WP_Error('springapex_form_schema_invalid_field', sprintf('Form %s: field %d must be an object.', $form, $index), ['status' => 400]);
                }
                $normalized = springapex_normalize_form_field($row, $defaults_by_id, $types);
                if ($normalized === null) {
                    return new WP_Error(
                        'springapex_form_schema_invalid_field',
                        sprintf('Form %s: field %d has an invalid id.', $form, $index),
                        ['status' => 400]
                    );
                }
                if (isset($seen[$normalized['id']])) {
                    return new WP_Error(
                        'springapex_form_schema_duplicate_field',
                        sprintf('Form %s: field id %s appears twice.', $form, $normalized['id']),
                        ['status' => 400]
                    );
                }
                $seen[$normalized['id']] = true;
                $entry['fields'][$index] = $row;
            }
        }
        $clean[$form] = $entry;
    }
    return $clean;
}

/**
 * 以当前生效配置为底，叠加本次提交的表单和键，再走后台保存同一个整理函数。
 *
 * @param array<string, array<string, mixed>> $current
 * @param array<string, array<string, mixed>> $submitted
 * @return array<string, array<string, mixed>>
 */
function springapex_rest_form_schema_merge(array $current, array $submitted): array
{
    foreach ($submitted as $form => $entry) {
        foreach ($entry as $key => $value) {
            $current[$form][$key] = $value;
        }
    }
    return springapex_form_schema_from_input($current);
}
