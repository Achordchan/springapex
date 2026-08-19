<?php
/**
 * 客户询盘的只读查看界面。
 *
 * 询盘是访客提交的数据，不是可编辑的内容：CPT 已去掉 title/editor
 * 支持，post.php 上只有一个「询盘内容」只读面板（客户信息、规格、
 * 来源、正文、图纸下载）。列表页的行操作把「编辑」改成「查看」，
 * 并移除快速编辑。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', static function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php', 'edit.php'], true)) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || (string) $screen->post_type !== 'spring_inquiry') {
        return;
    }
    wp_enqueue_style(
        'springapex-inquiry-view',
        SPRINGAPEX_URI . '/assets/css/inquiry-view.css',
        [],
        SPRINGAPEX_VERSION
    );
});

add_action('add_meta_boxes_spring_inquiry', static function (): void {
    add_meta_box(
        'springapex-inquiry-view',
        '询盘内容（只读）',
        'springapex_render_inquiry_view',
        'spring_inquiry',
        'normal',
        'high'
    );
});

add_filter('post_row_actions', static function (array $actions, WP_Post $post): array {
    if ((string) $post->post_type !== 'spring_inquiry') {
        return $actions;
    }
    if (isset($actions['edit'])) {
        $actions['edit'] = sprintf(
            '<a href="%s" aria-label="%s">查看</a>',
            esc_url(get_edit_post_link($post->ID)),
            esc_attr(sprintf('查看「%s」', get_the_title($post->ID)))
        );
    }
    unset($actions['inline'], $actions['inline hide-if-no-js']);

    return $actions;
}, 10, 2);

// 状态列：「私密」（private post_status）对询盘语境毫无意义，
// 换成邮件通知状态——运营者真正关心的信号。
add_filter('display_post_states', static function (array $post_states, WP_Post $post): array {
    if ((string) $post->post_type !== 'spring_inquiry') {
        return $post_states;
    }
    unset($post_states['private']);
    $mail = (string) get_post_meta((int) $post->ID, '_springapex_mail_sent', true);
    if ($mail === 'sent') {
        $post_states['springapex_mail'] = '已通知邮件';
    } elseif ($mail === 'pending') {
        $post_states['springapex_mail'] = '<span style="color:#d63638;">待通知</span>';
    }

    return $post_states;
}, 10, 2);

function springapex_render_inquiry_view(object $post): void
{
    $post_id = (int) $post->ID;
    $m = static function (string $key): string {
        return trim((string) get_post_meta(get_the_ID(), $key, true));
    };

    $contact = [
        '姓名' => $m('_springapex_name'),
        '邮箱' => $m('_springapex_email'),
        '公司' => $m('_springapex_company'),
        '电话' => $m('_springapex_phone'),
        '国家/地区' => $m('_springapex_country'),
    ];
    $specs = [
        $m('_springapex_dimension_label_1') ?: '线径' => $m('_springapex_wire_diameter'),
        $m('_springapex_dimension_label_2') ?: '外径' => $m('_springapex_outside_diameter'),
        $m('_springapex_dimension_label_3') ?: '自由长度' => $m('_springapex_free_length'),
        '数量' => $m('_springapex_quantity'),
        '材料' => $m('_springapex_material'),
        '工作环境' => $m('_springapex_operating_environment'),
    ];
    $source = [
        '询盘类型' => $m('_springapex_type'),
        '来源页面' => $m('_springapex_source_url') ?: $m('_springapex_source_path'),
        '相关产品' => $m('_springapex_product'),
        '相关行业' => $m('_springapex_industry'),
        '提交时间' => get_post_time('Y-m-d H:i:s', true, $post),
        '邮件通知' => $m('_springapex_mail_sent') === 'sent' ? '已发送' : ($m('_springapex_mail_sent') === 'pending' ? '待发送' : $m('_springapex_mail_sent')),
    ];

    // 「表单设置」新增的自定义字段：label => value，按运营者配置动态展示。
    $custom_raw = get_post_meta($post_id, '_springapex_custom_fields', true);
    $custom = [];
    if (is_array($custom_raw)) {
        foreach ($custom_raw as $label => $value) {
            $value = is_scalar($value) ? trim((string) $value) : '';
            if ((string) $label !== '' && $value !== '') {
                $custom[(string) $label] = $value;
            }
        }
    }

    $render_table = static function (string $title, array $pairs) use ($post_id): void {
        $rows = array_filter($pairs, static fn (string $v): bool => $v !== '');
        if (!$rows) {
            return;
        }
        echo '<h3 class="sa-iv__head">', esc_html($title), '</h3><table class="sa-iv__table"><tbody>';
        foreach ($rows as $label => $value) {
            echo '<tr><th>', esc_html((string) $label), '</th><td>', esc_html($value), '</td></tr>';
        }
        echo '</tbody></table>';
    };
    ?>
    <div class="sa-iv">
      <?php $render_table('客户信息', $contact); ?>
      <?php $render_table('自定义字段', $custom); ?>
      <?php $render_table('规格参数', $specs); ?>
      <?php $render_table('来源', $source); ?>

      <?php if ((string) $post->post_content !== '') : ?>
        <h3 class="sa-iv__head">留言</h3>
        <div class="sa-iv__message"><?php echo esc_html((string) $post->post_content); ?></div>
      <?php endif; ?>

      <?php $files = springapex_inquiry_private_files($post_id); ?>
      <?php if ($files) : ?>
        <h3 class="sa-iv__head">图纸（<?php echo esc_html((string) count($files)); ?> 个文件）</h3>
        <ul class="sa-iv__files">
          <?php foreach ($files as $index => $metadata) : ?>
            <?php
            $filename = sanitize_file_name((string) ($metadata['original_name'] ?? 'drawing')) ?: 'drawing';
            $download_url = wp_nonce_url(
                admin_url('admin-post.php?action=springapex_download_inquiry_file&inquiry_id=' . $post_id . '&file=' . (int) $index),
                'springapex_download_inquiry_' . $post_id . '_' . (int) $index
            );
            ?>
            <li>
              <a href="<?php echo esc_url($download_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($filename); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php
}
