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
    // 询盘是访客提交的只读数据，没有可编辑字段。核心默认的「发布」框会给它一个
    // 私密可见性下拉 + 更新/移到回收站按钮，让人误以为要在这里设状态、能改内容——
    // 全部去掉，换成一个只有「返回列表 / 移到回收站」的精简操作框。
    remove_meta_box('submitdiv', 'spring_inquiry', 'side');
    remove_meta_box('slugdiv', 'spring_inquiry', 'normal');

    add_meta_box(
        'springapex-inquiry-view',
        '询盘内容（只读）',
        'springapex_render_inquiry_view',
        'spring_inquiry',
        'normal',
        'high'
    );

    add_meta_box(
        'springapex-inquiry-actions',
        '操作',
        'springapex_render_inquiry_actions',
        'spring_inquiry',
        'side',
        'high'
    );
});

/** 只读询盘的操作框：返回列表 + 移到回收站（无状态、无更新）。 */
function springapex_render_inquiry_actions(WP_Post $post): void
{
    $list_url = admin_url('edit.php?post_type=spring_inquiry');
    $trash_url = current_user_can('delete_post', $post->ID) ? get_delete_post_link($post->ID) : '';
    ?>
    <div class="sa-iv-actions">
      <a class="button" href="<?php echo esc_url($list_url); ?>">返回询盘列表</a>
      <?php if ($trash_url !== '') : ?>
        <a class="sa-iv-actions__trash submitdelete" href="<?php echo esc_url($trash_url); ?>"
           onclick="return confirm('确定把这条询盘移到回收站吗？');">移到回收站</a>
      <?php endif; ?>
    </div>
    <?php
}

/** 邮件通知状态的统一文案；兼容邮件模板改造前落库的 '1'/'0' 旧值。 */
function springapex_inquiry_mail_status_label(string $status): string
{
    return match ($status) {
        'sent', '1' => '已通知邮件',
        'pending' => '待通知',
        'failed', '0' => '邮件发送失败',
        default => '',
    };
}

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

// 询盘只读：去掉批量「编辑」（它能批量改状态），只保留「移到回收站」。
// 行内快速编辑已在 post_row_actions 里移除，这里补齐批量入口。
add_filter('bulk_actions-edit-spring_inquiry', static function (array $actions): array {
    unset($actions['edit']);

    return $actions;
});

// 标题后的「私密」状态徽标（private post_status）对询盘语境毫无意义：
// 询盘天生就是后台私密数据，不对外公开，这个标签只是噪音。
add_filter('display_post_states', static function (array $post_states, WP_Post $post): array {
    if ((string) $post->post_type !== 'spring_inquiry') {
        return $post_states;
    }
    unset($post_states['private']);

    return $post_states;
}, 10, 2);

// 列表顶部的状态筛选链接：所有询盘都是 private，「私密(N)」与「全部(N)」
// 完全等价，纯属重复。其余发布态在询盘里也不会出现，一并去掉，只留「全部」
// 和「回收站」。
add_filter('views_edit-spring_inquiry', static function (array $views): array {
    unset(
        $views['private'],
        $views['publish'],
        $views['draft'],
        $views['pending'],
        $views['future'],
        $views['sticky']
    );

    return $views;
});

// 列表列的定义在 inc/contact.php，与来源筛选、下载处理放在一起（单一来源）。

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
    $mail_status = springapex_inquiry_mail_status_label($m('_springapex_mail_sent'));
    $source = [
        '询盘类型' => $m('_springapex_type'),
        '来源页面' => $m('_springapex_source_url') ?: $m('_springapex_source_path'),
        '相关产品' => $m('_springapex_product'),
        '相关行业' => $m('_springapex_industry'),
        '提交时间' => get_post_time('Y-m-d H:i:s', true, $post),
        '邮件通知' => $mail_status ?: '—',
    ];

    // 新格式为 id => {label, value}；同时兼容此前的 label => value 记录。
    // 使用行列表而非 label 作为数组键，确保同名字段都能完整展示。
    $custom_raw = get_post_meta($post_id, '_springapex_custom_fields', true);
    $custom = [];
    if (is_array($custom_raw)) {
        foreach ($custom_raw as $field_id => $stored) {
            if (is_array($stored)) {
                $label = trim((string) ($stored['label'] ?? $field_id));
                $value = is_scalar($stored['value'] ?? null) ? trim((string) $stored['value']) : '';
            } else {
                $label = trim((string) $field_id);
                $value = is_scalar($stored) ? trim((string) $stored) : '';
            }
            if ($label !== '' && $value !== '') {
                $custom[] = ['label' => $label, 'value' => $value];
            }
        }
    }

    $render_table = static function (string $title, array $pairs) use ($post_id): void {
        $rows = [];
        foreach ($pairs as $label => $stored) {
            if (is_array($stored)) {
                $row_label = trim((string) ($stored['label'] ?? ''));
                $row_value = is_scalar($stored['value'] ?? null) ? trim((string) $stored['value']) : '';
            } else {
                $row_label = trim((string) $label);
                $row_value = is_scalar($stored) ? trim((string) $stored) : '';
            }
            if ($row_label !== '' && $row_value !== '') {
                $rows[] = ['label' => $row_label, 'value' => $row_value];
            }
        }
        if (!$rows) {
            return;
        }
        echo '<h3 class="sa-iv__head">', esc_html($title), '</h3><table class="sa-iv__table"><tbody>';
        foreach ($rows as $row) {
            echo '<tr><th>', esc_html($row['label']), '</th><td>', esc_html($row['value']), '</td></tr>';
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
