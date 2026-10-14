<?php
/**
 * REST access to 后台「SEO / TDK」路由页设置。
 *
 * 首页、列表页、隐私、条款等路由页的 TDK 存在 springapex_seo_settings option，
 * core 的 /wp/v2/settings 不暴露它，页面自身的 _springapex_seo_* meta 前台也不读。
 * 这里提供与后台表单同一套清洗规则的读写接口：
 *
 *   GET  /wp-json/springapex/v1/seo   各路由的当前值、内置默认值和是否已自定义
 *   POST /wp-json/springapex/v1/seo   {"routes": {"terms": {"description": "..."}}}
 *                                     局部更新，只改传入的路由和字段
 *
 * 字段为 title、description、keywords。传空字符串或与内置默认相同的值，
 * 都存为空（恢复默认），与后台表单的归一化一致。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'springapex_rest_seo_routes');

function springapex_rest_seo_routes(): void
{
    $permission = static fn(): bool => current_user_can(SPRINGAPEX_ADMIN_CAP);

    register_rest_route('springapex/v1', '/seo', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'springapex_rest_seo_get',
            'permission_callback' => $permission,
        ],
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'springapex_rest_seo_update',
            'permission_callback' => $permission,
            'args' => [
                'routes' => [
                    'required' => true,
                    'type' => 'object',
                ],
            ],
        ],
    ]);
}

/** @return array<string, array<string, mixed>> */
function springapex_rest_seo_snapshot(): array
{
    $settings = springapex_seo_saved_settings();
    $routes = [];
    foreach (springapex_seo_route_definitions() as $route => $definition) {
        $saved = is_array($settings['routes'][$route] ?? null) ? $settings['routes'][$route] : [];
        $values = springapex_seo_route_values($route);
        $routes[$route] = [
            'label' => (string) ($definition['label'] ?? ''),
            'path' => (string) ($definition['path'] ?? ''),
            'title' => $values['title'],
            'description' => $values['description'],
            'keywords' => $values['keywords'],
            'default_title' => (string) ($definition['title'] ?? ''),
            'default_description' => (string) ($definition['description'] ?? ''),
            'custom_title' => trim((string) ($saved['title'] ?? '')) !== '',
            'custom_description' => trim((string) ($saved['description'] ?? '')) !== '',
        ];
    }
    return $routes;
}

function springapex_rest_seo_get(): WP_REST_Response
{
    return new WP_REST_Response(['routes' => springapex_rest_seo_snapshot()]);
}

function springapex_rest_seo_update(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $submitted = $request->get_param('routes');
    if (!is_array($submitted) || $submitted === []) {
        return new WP_Error('springapex_seo_empty', 'routes must be a non-empty object.', ['status' => 400]);
    }

    $definitions = springapex_seo_route_definitions();
    $fields = ['title', 'description', 'keywords'];
    foreach ($submitted as $route => $row) {
        if (!isset($definitions[$route])) {
            return new WP_Error('springapex_seo_unknown_route', sprintf('Unknown route: %s', (string) $route), ['status' => 400]);
        }
        if (!is_array($row)) {
            return new WP_Error('springapex_seo_invalid_row', sprintf('Route %s must be an object.', (string) $route), ['status' => 400]);
        }
        foreach ($row as $field => $value) {
            if (!in_array($field, $fields, true) || !is_string($value)) {
                return new WP_Error(
                    'springapex_seo_invalid_field',
                    sprintf('Route %s accepts string fields title, description and keywords only.', (string) $route),
                    ['status' => 400]
                );
            }
        }
    }

    // 以已存的自定义值为底，叠加本次提交，再走后台表单同一个清洗函数。
    // 已存值为空表示「用默认」，这里填回默认值，清洗时会再归一化为空。
    $settings = springapex_seo_saved_settings();
    $input = ['routes' => []];
    foreach ($definitions as $route => $definition) {
        $saved = is_array($settings['routes'][$route] ?? null) ? $settings['routes'][$route] : [];
        $row = [
            'title' => trim((string) ($saved['title'] ?? '')) ?: (string) $definition['title'],
            'description' => trim((string) ($saved['description'] ?? '')) ?: (string) $definition['description'],
            'keywords' => (string) ($saved['keywords'] ?? ''),
        ];
        foreach ((array) ($submitted[$route] ?? []) as $field => $value) {
            $value = trim((string) $value);
            $row[$field] = ($value === '' && $field !== 'keywords') ? (string) $definition[$field] : $value;
        }
        $input['routes'][$route] = $row;
    }

    $clean = springapex_sanitize_seo_settings($input);
    if ($clean !== $settings) {
        update_option('springapex_seo_settings', $clean);
    }

    $snapshot = springapex_rest_seo_snapshot();
    return new WP_REST_Response([
        'saved' => true,
        'routes' => array_intersect_key($snapshot, $submitted),
    ]);
}
