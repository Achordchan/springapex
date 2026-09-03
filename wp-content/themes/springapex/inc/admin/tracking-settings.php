<?php
/**
 * 「跟踪代码」设置页。
 *
 * 运营在这里维护 GTM 容器号和三段自定义代码，换容器号或加一段客服代码不再需要
 * 改主题文件走一次部署。前台输出逻辑在 inc/analytics.php，两边共用
 * SPRINGAPEX_TRACKING_OPTION。
 *
 * 关于「后台能粘贴任意 HTML/JS」这件事：这就是本页的功能本身。第三方统计和客服
 * 代码全都是 script 标签，能被 wp_kses 清洗掉的代码也就跑不起来了，所以这里不做
 * 标记过滤——做了等于交付一个不工作的功能。
 *
 * 写入门槛用 WP 原生的 unfiltered_html 能力把关，与核心的「自定义 HTML」小工具、
 * 以及主题与插件文件编辑器同级。单站点里只有管理员具备该能力，而管理员本来就能
 * 直接编辑主题文件，所以这里没有制造新的权限提升面。多站点下只有超级管理员具备，
 * 普通站点管理员提交会被明确拒绝并保留原值，而不是静默清洗成半截脚本——半截脚本
 * 比拒绝难查得多。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_TRACKING_ADMIN_SLUG = 'springapex-content-tracking';

add_action('admin_init', static function (): void {
    register_setting('springapex_tracking', SPRINGAPEX_TRACKING_OPTION, [
        'type' => 'array',
        'sanitize_callback' => 'springapex_sanitize_tracking_settings',
        'default' => [],
    ]);
});

add_filter('option_page_capability_springapex_tracking', static fn(): string => SPRINGAPEX_ADMIN_CAP);

add_action('admin_menu', static function (): void {
    add_submenu_page(
        SPRINGAPEX_ADMIN_SLUG,
        '跟踪代码',
        '跟踪代码',
        SPRINGAPEX_ADMIN_CAP,
        SPRINGAPEX_TRACKING_ADMIN_SLUG,
        'springapex_render_tracking_settings_page'
    );
}, 21);

/**
 * 保存端校验。
 *
 * 两类字段的门槛不同：容器号是受格式约束的纯标识符，不含标记，任何能进这个页面
 * 的人都能改；三段自定义代码是原始标记，要求 unfiltered_html。
 *
 * 校验不通过时一律「保留原值 + 明确报错」，不静默丢弃：运营配好的代码被无声抹掉，
 * 现场只剩「统计怎么没了」，比当场报错难查得多。
 */
function springapex_sanitize_tracking_settings(mixed $input): array
{
    $result = springapex_tracking_settings();
    if (!is_array($result['slots'] ?? null)) {
        $result['slots'] = [];
    }
    if (!is_array($input)) {
        return $result;
    }

    if (array_key_exists('gtm_id', $input)) {
        $container_id = strtoupper(trim((string) $input['gtm_id']));
        if ($container_id === '' || preg_match('/^GTM-[A-Z0-9]+$/', $container_id) === 1) {
            $result['gtm_id'] = $container_id;
        } else {
            add_settings_error(
                SPRINGAPEX_TRACKING_OPTION,
                'springapex_tracking_gtm_id',
                'GTM 容器号格式不对，应当形如 GTM-XXXXXXX。容器号未改动，同页其它修改已保存。',
                'error'
            );
        }
    }

    $submitted = is_array($input['slots'] ?? null) ? $input['slots'] : [];
    if ($submitted === []) {
        return $result;
    }

    if (!current_user_can('unfiltered_html')) {
        add_settings_error(
            SPRINGAPEX_TRACKING_OPTION,
            'springapex_tracking_cap',
            '你的账号没有「发布未过滤 HTML」的权限，三个代码框未改动。请让管理员来改这部分。',
            'error'
        );

        return $result;
    }

    foreach (array_keys(springapex_tracking_slots()) as $slot) {
        if (array_key_exists($slot, $submitted)) {
            $result['slots'][$slot] = trim((string) $submitted[$slot]);
        }
    }

    return $result;
}

function springapex_render_tracking_settings_page(): void
{
    if (!current_user_can(SPRINGAPEX_ADMIN_CAP)) {
        wp_die('你没有权限编辑跟踪代码。', '权限不足', ['response' => 403]);
    }

    $settings = springapex_tracking_settings();
    $container_id = array_key_exists('gtm_id', $settings)
        ? (string) $settings['gtm_id']
        : SPRINGAPEX_TRACKING_DEFAULT_GTM_ID;
    $slots = is_array($settings['slots'] ?? null) ? $settings['slots'] : [];
    $can_edit_code = current_user_can('unfiltered_html');
    $environment = wp_get_environment_type();
    $index = 0;
    ?>
    <div class="wrap sa-admin">
      <div class="sa-admin__header">
        <div>
          <h1 class="sa-admin__title">跟踪代码</h1>
          <p class="sa-admin__intro">统计、广告转化、在线客服这类第三方代码在这里粘贴，保存后立刻对所有页面生效，不需要改主题文件。</p>
        </div>
      </div>

      <?php settings_errors(); ?>

      <?php if ($environment !== 'production'): ?>
        <div class="notice notice-warning inline">
          <p>当前站点声明的运行环境是 <code><?php echo esc_html($environment); ?></code>，本页所有代码都<strong>不会</strong>输出到页面。这是为了避免测试环境把数据打进客户的统计后台；生产站不受影响。</p>
        </div>
      <?php endif; ?>

      <?php if (defined('SPRINGAPEX_GTM_ID')): ?>
        <div class="notice notice-warning inline">
          <p>wp-config.php 里定义了 <code>SPRINGAPEX_GTM_ID</code>，它会<strong>覆盖</strong>下面填写的容器号。要让本页的容器号生效，请先让技术同事去掉那行配置。</p>
        </div>
      <?php endif; ?>

      <?php if (!$can_edit_code): ?>
        <div class="notice notice-warning inline">
          <p>你的账号没有「发布未过滤 HTML」的权限，下面三个代码框是只读的。容器号仍可修改。</p>
        </div>
      <?php endif; ?>

      <div class="sa-notice">
        <strong>提示：</strong>
        <span>GTM 装好之后，再来的 Google Analytics、Google Ads 转化、Meta Pixel 等代码，正常做法是在 GTM 后台里添加，网站这边不用再动。只有明确要求「粘贴到网站源码里」的代码才需要用下面的三个框。</span>
      </div>

      <form method="post" action="options.php">
        <?php settings_fields('springapex_tracking'); ?>

        <details class="sa-card" data-sa-card open>
          <summary class="sa-card__head">
            <h2 class="sa-card__title">
              <span class="sa-card__num" aria-hidden="true">1</span>
              <span class="sa-card__name">Google 跟踪代码管理器（GTM）</span>
              <span class="sa-card__meta"><?php echo $container_id === '' ? '未启用' : esc_html($container_id); ?></span>
            </h2>
          </summary>
          <div class="sa-card__body">
            <p class="sa-card__desc">填容器号即可，两段官方代码（head 主脚本与 body 的 noscript 兜底）由主题自动生成并放到正确位置，不用手动粘贴。</p>
            <label>
              <strong>容器号</strong><br>
              <input
                type="text"
                name="<?php echo esc_attr(SPRINGAPEX_TRACKING_OPTION); ?>[gtm_id]"
                value="<?php echo esc_attr($container_id); ?>"
                class="regular-text code"
                placeholder="GTM-XXXXXXX"
                spellcheck="false"
                autocomplete="off"
              >
            </label>
            <p class="description">在 GTM 后台「管理 &rarr; 容器设置」里可以看到，形如 <code>GTM-XXXXXXX</code>。<strong>留空表示不加载 GTM。</strong></p>
          </div>
        </details>

        <?php foreach (springapex_tracking_slots() as $slot => $definition): ?>
          <?php
          $index++;
          $value = (string) ($slots[$slot] ?? '');
          $field = SPRINGAPEX_TRACKING_OPTION . '[slots][' . $slot . ']';
          ?>
          <details class="sa-card" data-sa-card<?php echo $value !== '' ? ' open' : ''; ?>>
            <summary class="sa-card__head">
              <h2 class="sa-card__title">
                <span class="sa-card__num" aria-hidden="true"><?php echo (int) ($index + 1); ?></span>
                <span class="sa-card__name"><?php echo esc_html((string) $definition['label']); ?></span>
                <span class="sa-card__meta"><?php echo $value === '' ? '空' : '已填 ' . number_format(strlen($value)) . ' 字符'; ?></span>
              </h2>
            </summary>
            <div class="sa-card__body">
              <p class="sa-card__desc"><?php echo esc_html((string) $definition['help']); ?></p>
              <textarea
                name="<?php echo esc_attr($field); ?>"
                rows="8"
                class="large-text code"
                spellcheck="false"
                autocomplete="off"
                <?php echo $can_edit_code ? '' : 'readonly'; ?>
              ><?php echo esc_textarea($value); ?></textarea>
              <p class="description">原样粘贴第三方给的代码，包含 <code>&lt;script&gt;</code> 标签在内，不要删改。留空表示这一处不输出任何内容。</p>
            </div>
          </details>
        <?php endforeach; ?>

        <p class="submit"><?php submit_button('保存跟踪代码', 'primary', 'submit', false); ?></p>
      </form>
    </div>
    <?php
}
