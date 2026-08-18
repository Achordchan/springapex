<?php
/**
 * Field renderer for the 网站内容 admin screens.
 *
 * Turns the plain-data definitions in schema.php into form markup. Field names
 * follow the content path, so `home.hero.title` posts as
 * springapex_content[home][hero][title] and a save handler can walk it back
 * into the content array without a per-field mapping table.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * springapex_content[a][b][c] for a dot path, with an optional row index
 * appended by the repeater renderer.
 */
function springapex_admin_field_name(string $path): string
{
    $name = 'springapex_content';
    foreach (explode('.', $path) as $part) {
        $name .= '[' . $part . ']';
    }
    return $name;
}

function springapex_admin_field_id(string $path): string
{
    return 'sa-' . preg_replace('/[^a-z0-9]+/i', '-', $path);
}

/**
 * Best-effort preview URL for an image value, which may be an attachment ID,
 * a filename under $base, or ['id' => int, 'file' => string].
 */
function springapex_admin_image_url(mixed $value, string $base = 'assets/images/'): string
{
    if (is_array($value)) {
        $id = (int) ($value['id'] ?? 0);
        if ($id > 0) {
            $url = wp_get_attachment_image_url($id, 'medium');
            if ($url) {
                return $url;
            }
        }
        $value = (string) ($value['file'] ?? '');
    }

    if (is_int($value) || (is_string($value) && ctype_digit($value) && $value !== '')) {
        return (string) wp_get_attachment_image_url((int) $value, 'medium');
    }

    $file = ltrim((string) $value, '/');
    if ($file === '') {
        return '';
    }
    if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
        return $file;
    }

    return springapex_asset($base . $file);
}

function springapex_admin_image_value(mixed $value): string
{
    if (is_array($value)) {
        $id = (int) ($value['id'] ?? 0);
        return $id > 0 ? (string) $id : (string) ($value['file'] ?? '');
    }
    return is_scalar($value) ? (string) $value : '';
}

/**
 * Render one field. $value is the current content value at this path.
 */
function springapex_admin_render_field(array $field, mixed $value, string $name_path): void
{
    $type = (string) ($field['type'] ?? 'text');

    if ($type === 'repeater') {
        springapex_admin_render_repeater($field, is_array($value) ? $value : [], $name_path);
        return;
    }

    $name = springapex_admin_field_name($name_path);
    $id = springapex_admin_field_id($name_path);
    $label = (string) ($field['label'] ?? '');
    $help = (string) ($field['help'] ?? '');
    ?>
    <div class="sa-field sa-field--<?php echo esc_attr($type); ?>" data-sa-field-path="<?php echo esc_attr($name_path); ?>">
        <label class="sa-field__label" for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
        <div class="sa-field__control">
            <?php
            switch ($type) {
                case 'textarea':
                    printf(
                        '<textarea id="%s" name="%s" rows="%d" class="sa-input">%s</textarea>',
                        esc_attr($id),
                        esc_attr($name),
                        (int) ($field['rows'] ?? 3),
                        esc_textarea(is_scalar($value) ? (string) $value : '')
                    );
                    break;

                case 'lines':
                    $lines = is_array($value) ? implode("\n", array_map('strval', $value)) : (string) $value;
                    printf(
                        '<textarea id="%s" name="%s" rows="%d" class="sa-input sa-input--lines">%s</textarea>',
                        esc_attr($id),
                        esc_attr($name),
                        max(4, min(14, substr_count($lines, "\n") + 2)),
                        esc_textarea($lines)
                    );
                    break;

                case 'icon':
                    springapex_admin_render_icon_picker($id, $name, (string) (is_scalar($value) ? $value : ''));
                    break;

                case 'image':
                    springapex_admin_render_image_field($id, $name, $value, springapex_admin_image_base($field), springapex_admin_image_dimension($name_path));
                    break;

                case 'youtube':
                    springapex_admin_render_youtube_field($id, $name, (string) (is_scalar($value) ? $value : ''));
                    break;

                case 'route':
                    springapex_admin_render_route_field($id, $name, (string) (is_scalar($value) ? $value : ''));
                    break;

                default:
                    $input_type = in_array($type, ['url', 'email', 'tel'], true) ? $type : 'text';
                    printf(
                        '<input type="%s" id="%s" name="%s" value="%s" class="sa-input" />',
                        esc_attr($input_type),
                        esc_attr($id),
                        esc_attr($name),
                        esc_attr(is_scalar($value) ? (string) $value : '')
                    );
            }

            if ($help !== '') {
                printf('<p class="sa-field__help">%s</p>', esc_html($help));
            }
            ?>
        </div>
    </div>
    <?php
}

function springapex_admin_render_image_field(string $id, string $name, mixed $value, string $base = 'assets/images/', string $dimensions = ''): void
{
    $url = springapex_admin_image_url($value, $base);
    $stored = springapex_admin_image_value($value);
    ?>
    <?php if ($dimensions !== '') : ?>
        <p class="sa-image__dims"><span class="sa-image__dims-tag">推荐尺寸</span><?php echo esc_html($dimensions); ?></p>
    <?php endif; ?>
    <div class="sa-image" data-sa-image>
        <div class="sa-image__preview<?php echo $url === '' ? ' is-empty' : ''; ?>" data-sa-image-preview>
            <?php if ($url !== '') : ?>
                <img src="<?php echo esc_url($url); ?>" alt="" />
            <?php else : ?>
                <span class="sa-image__placeholder">未选择图片</span>
            <?php endif; ?>
        </div>
        <div class="sa-image__actions">
            <button type="button" class="button" data-sa-image-select>选择图片</button>
            <button type="button" class="button-link sa-image__remove<?php echo $stored === '' ? ' is-hidden' : ''; ?>" data-sa-image-remove>移除</button>
            <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($stored); ?>" data-sa-image-value />
            <?php if ($stored !== '' && !ctype_digit($stored)) : ?>
                <code class="sa-image__file"><?php echo esc_html($stored); ?></code>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function springapex_admin_render_youtube_field(string $id, string $name, string $value): void
{
    ?>
    <div class="sa-youtube">
        <input type="text" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>"
               value="<?php echo esc_attr($value); ?>" class="sa-input sa-input--short"
               placeholder="5LUKHmIHPDY" data-sa-youtube />
        <?php if ($value !== '') : ?>
            <a class="sa-youtube__check" href="https://www.youtube.com/watch?v=<?php echo esc_attr($value); ?>"
               target="_blank" rel="noopener">打开检查 ↗</a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Route picker: a dropdown of real site destinations instead of a free-text
 * path. An existing value that is no longer in the list is preserved as a
 * locked "keep current" option so an edit elsewhere never silently drops it.
 */
function springapex_admin_render_route_field(string $id, string $name, string $value): void
{
    $groups = springapex_admin_route_options();
    $known = in_array($value, springapex_admin_route_values(), true);
    ?>
    <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="sa-input sa-input--short sa-route">
        <?php if ($value !== '' && !$known) : ?>
            <option value="<?php echo esc_attr($value); ?>" selected>保留当前：<?php echo esc_html($value); ?></option>
        <?php endif; ?>
        <?php foreach ($groups as $group_label => $options) : ?>
            <optgroup label="<?php echo esc_attr($group_label); ?>">
                <?php foreach ($options as $opt_value => $opt_label) : ?>
                    <option value="<?php echo esc_attr((string) $opt_value); ?>" <?php selected($value, (string) $opt_value); ?>>
                        <?php echo esc_html($opt_label . '（' . (string) $opt_value . '）'); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>
    <?php
}

function springapex_admin_render_icon_picker(string $id, string $name, string $value): void
{
    $icons = springapex_admin_icon_choices();
    ?>
    <div class="sa-icon-field">
        <span class="sa-icon-field__preview" data-sa-icon-preview>
            <?php echo springapex_icon($value !== '' ? $value : 'arrow-right', 'icon'); ?>
        </span>
        <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="sa-input sa-input--short">
            <?php if ($value !== '' && !isset($icons[$value])) : ?>
                <option value="<?php echo esc_attr($value); ?>" selected><?php echo esc_html($value); ?>（图标不存在）</option>
            <?php endif; ?>
            <?php foreach ($icons as $icon => $url) : ?>
                <option value="<?php echo esc_attr($icon); ?>" <?php selected($icon, $value); ?>><?php echo esc_html($icon); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * A repeater: collapsible rows plus a hidden template row the JS clones.
 * Row indexes in the template use __i__, replaced on insert.
 */
function springapex_admin_render_repeater(array $field, array $rows, string $name_path): void
{
    $item_label = (string) ($field['item_label'] ?? '项目');
    $title_key = (string) ($field['title_key'] ?? '');
    $help = (string) ($field['help'] ?? '');
    ?>
    <div class="sa-repeater" data-sa-repeater data-path="<?php echo esc_attr($name_path); ?>">
        <input type="hidden" name="<?php echo esc_attr(springapex_admin_field_name($name_path)); ?>" value="" />
        <div class="sa-repeater__head">
            <span class="sa-repeater__title"><?php echo esc_html((string) ($field['label'] ?? '')); ?></span>
            <span class="sa-repeater__count"><?php echo count($rows); ?> 项</span>
        </div>
        <?php if ($help !== '') : ?>
            <p class="sa-field__help sa-repeater__help"><?php echo esc_html($help); ?></p>
        <?php endif; ?>

        <div class="sa-repeater__rows" data-sa-rows>
            <?php foreach (array_values($rows) as $index => $row) :
                $row = is_array($row) ? $row : [];
                $row_title = $title_key !== '' ? (string) ($row[$title_key] ?? '') : '';
                springapex_admin_render_repeater_row($field, $row, $name_path . '.' . $index, $item_label, $row_title, $index + 1);
            endforeach; ?>
        </div>

        <?php
        // Emptying a list is a legitimate edit, so the empty state has to say
        // both what is gone from the front end and how to get an item back.
        // JS toggles it on add/delete; PHP decides the initial state.
        ?>
        <p class="sa-repeater__empty" data-sa-empty<?php echo $rows === [] ? '' : ' hidden'; ?>>
            这一栏目前没有任何<?php echo esc_html($item_label); ?>，前台不会显示这个区块。点下面的「添加<?php echo esc_html($item_label); ?>」新增一条。
        </p>

        <button type="button" class="button sa-repeater__add" data-sa-add>+ 添加<?php echo esc_html($item_label); ?></button>

        <template data-sa-template>
            <?php springapex_admin_render_repeater_row($field, [], $name_path . '.__i__', $item_label, '', 0); ?>
        </template>
    </div>
    <?php
}

function springapex_admin_render_repeater_row(array $field, array $row, string $name_path, string $item_label, string $row_title, int $position): void
{
    ?>
    <div class="sa-row" data-sa-row>
        <?php
        // Which stored row this one started as, so the sanitizer can fall back
        // to the right original value after the user reorders or deletes rows.
        // Empty for template/new rows.
        $origin = $position > 0 ? (string) ($position - 1) : '';
        ?>
        <input type="hidden" name="<?php echo esc_attr(springapex_admin_field_name($name_path . '.__row')); ?>" value="<?php echo esc_attr($origin); ?>" />
        <div class="sa-row__head">
            <button type="button" class="sa-row__toggle" data-sa-toggle aria-expanded="false">
                <span class="sa-row__handle" aria-hidden="true">⋮⋮</span>
                <span class="sa-row__index" data-sa-index><?php echo $position > 0 ? esc_html((string) $position) : ''; ?></span>
                <span class="sa-row__label" data-sa-row-title><?php echo esc_html($row_title !== '' ? $row_title : '新' . $item_label); ?></span>
                <span class="sa-row__chevron" aria-hidden="true"></span>
            </button>
            <div class="sa-row__tools">
                <button type="button" class="sa-row__tool" data-sa-move="up" title="上移">↑</button>
                <button type="button" class="sa-row__tool" data-sa-move="down" title="下移">↓</button>
                <button type="button" class="sa-row__tool sa-row__tool--danger" data-sa-remove title="删除">删除</button>
            </div>
        </div>
        <div class="sa-row__body" hidden>
            <?php foreach ((array) ($field['fields'] ?? []) as $sub) :
                $key = (string) ($sub['path'] ?? '');
                if ($key === '') {
                    continue;
                }
                springapex_admin_render_field($sub, $row[$key] ?? null, $name_path . '.' . $key);
            endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Anchor for a section card. Sections carry no key of their own, so position
 * within the screen is the identifier — stable as long as the schema is.
 */
function springapex_admin_section_id(string $screen_key, int $index): string
{
    return 'sa-sec-' . $screen_key . '-' . ($index + 1);
}

/**
 * One line of "what is currently in here", shown on a collapsed section so the
 * operator can find the right panel without opening every one: the first piece
 * of real copy, then each list's item count.
 */
function springapex_admin_section_summary(array $section): string
{
    $parts = [];
    $preview = '';

    foreach ((array) ($section['fields'] ?? []) as $field) {
        $path = (string) ($field['path'] ?? '');
        if ($path === '') {
            continue;
        }
        $type = (string) ($field['type'] ?? 'text');
        $value = springapex_get($path);

        if ($type === 'repeater') {
            $label = (string) ($field['label'] ?? '列表');
            $parts[] = $label . ' ' . count(is_array($value) ? $value : []) . ' 项';
            continue;
        }

        if ($preview === '' && in_array($type, ['text', 'textarea'], true)
            && is_scalar($value) && trim((string) $value) !== '') {
            $preview = trim((string) $value);
        }
    }

    if ($preview !== '') {
        if (springapex_admin_strlen($preview) > 26) {
            $preview = springapex_admin_substr($preview, 0, 26) . '…';
        }
        array_unshift($parts, $preview);
    }

    return implode(' · ', $parts);
}

/**
 * Render a whole screen.
 */
function springapex_admin_render_screen(string $key): void
{
    $screens = springapex_admin_screens();
    if (!isset($screens[$key])) {
        return;
    }
    $screen = $screens[$key];
    $sections = array_values((array) $screen['sections']);
    // Several screens are 3000–4500px tall. Below three sections the operator
    // can see everything by scrolling once, so the jump bar would be noise.
    $show_jump = count($sections) >= 3;
    // Sections collapse so the screen opens as a short overview instead of a
    // wall of inputs. A single-section screen would then show nothing at all,
    // so that one stays open.
    $open_all = count($sections) === 1;
    ?>
    <div class="wrap sa-admin">
        <div class="sa-admin__header">
            <div>
                <h1 class="sa-admin__title"><?php echo esc_html((string) $screen['title']); ?></h1>
                <?php if (!empty($screen['intro'])) : ?>
                    <p class="sa-admin__intro"><?php echo esc_html((string) $screen['intro']); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($screen['preview'])) : ?>
                <a class="button sa-admin__preview" href="<?php echo esc_url(home_url((string) $screen['preview'])); ?>" target="_blank" rel="noopener">
                    查看前台页面 ↗
                </a>
            <?php endif; ?>
        </div>

        <?php springapex_admin_render_search($key); ?>

        <?php if ($show_jump) : ?>
            <nav class="sa-jump" aria-label="本页板块">
                <span class="sa-jump__label">本页板块</span>
                <?php foreach ($sections as $index => $section) : ?>
                    <a class="sa-jump__link" href="#<?php echo esc_attr(springapex_admin_section_id($key, $index)); ?>">
                        <?php echo esc_html((string) ($section['title'] ?? '')); ?>
                    </a>
                <?php endforeach; ?>
                <button type="button" class="button-link sa-jump__all" data-sa-toggle-all aria-expanded="false">全部展开</button>
            </nav>
        <?php endif; ?>

        <form class="sa-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="springapex_save_content" />
            <input type="hidden" name="screen" value="<?php echo esc_attr($key); ?>" />
            <?php wp_nonce_field('springapex_save_content_' . $key); ?>

            <?php foreach ($sections as $index => $section) :
                $summary = springapex_admin_section_summary($section);
                ?>
                <details class="sa-card" id="<?php echo esc_attr(springapex_admin_section_id($key, $index)); ?>" data-sa-card <?php echo $open_all ? 'open' : ''; ?>>
                    <summary class="sa-card__head">
                        <?php
                        // The whole heading sits inside the h2: <summary> accepts
                        // either phrasing content or exactly one heading element,
                        // and the section headings are worth keeping.
                        ?>
                        <h2 class="sa-card__title">
                            <span class="sa-card__num" aria-hidden="true"><?php echo (int) ($index + 1); ?></span>
                            <span class="sa-card__name"><?php echo esc_html((string) ($section['title'] ?? '')); ?></span>
                            <?php if ($summary !== '') : ?>
                                <span class="sa-card__meta"><?php echo esc_html($summary); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($section['desc'])) : ?>
                                <span class="sa-card__desc"><?php echo esc_html((string) $section['desc']); ?></span>
                            <?php endif; ?>
                        </h2>
                    </summary>
                    <div class="sa-card__body">
                        <?php foreach ((array) ($section['fields'] ?? []) as $field) :
                            $path = (string) ($field['path'] ?? '');
                            if ($path === '') {
                                continue;
                            }
                            springapex_admin_render_field($field, springapex_get($path), $path);
                        endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>

            <div class="sa-savebar">
                <span class="sa-savebar__hint">改完记得保存，然后到前台刷新确认效果。</span>
                <button type="submit" class="button button-primary button-hero">保存修改</button>
                <button type="submit" name="springapex_reset_content" value="1" class="button sa-savebar__reset" data-sa-reset>恢复默认</button>
            </div>
        </form>
    </div>
    <?php
}
