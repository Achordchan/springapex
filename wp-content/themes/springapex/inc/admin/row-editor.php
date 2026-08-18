<?php
/**
 * 可增删行的编辑器，替代竖线分隔的文本框。
 *
 * The industry solution screen used to ask the operator to type rows like
 * `Title | Description | icon-key | product-slug,product-slug | image path`.
 * Every part of that fails silently: a stray `|`, a mistyped icon key, a product
 * slug that no longer exists, an image path that was never uploaded. Nothing on
 * screen tells them, and the front end just drops the row or the picture.
 *
 * This renders one card per row with a real control per field, plus add / remove /
 * reorder. Rows post as `$field[<index>][<key>]`, so PHP sees a plain list.
 *
 * Column types:
 *   text      single-line input
 *   textarea  multi-line input
 *   icon      <select> of the keys springapex_icon_map() actually resolves
 *   image     Media Library picker (stores an attachment id)
 *   products  checkboxes of the real published products (stores slugs)
 *
 * A column may also set 'half' => true to share its line with the next half column,
 * which keeps a two-short-field row (label / value) from stacking into a tall card,
 * and 'default' => '…' for the value a newly added row starts with. Without a default
 * an icon column opens on whatever sorts first, which is the arrow nobody wants.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post types whose edit screen renders a row editor. Anything added here gets the
 * stylesheet, the script and the Media Library; anything missing renders as an
 * unstyled list of inputs whose buttons do nothing.
 *
 * @return string[]
 */
function springapex_row_editor_post_types(): array
{
    return ['spring_solution', 'spring_product'];
}

add_action('admin_enqueue_scripts', static function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array((string) $screen->post_type, springapex_row_editor_post_types(), true)) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'springapex-row-editor',
        SPRINGAPEX_URI . '/assets/css/row-editor.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-row-editor',
        SPRINGAPEX_URI . '/assets/js/row-editor.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
});

/**
 * @param array<int, array<string, mixed>> $rows
 * @param array<int, array{key: string, label: string, type: string, help?: string, half?: bool, default?: string}> $columns
 */
function springapex_render_row_editor(string $field, array $rows, array $columns, string $intro): void
{
    $rows = array_values(array_filter($rows, 'is_array'));
    ?>
    <div class="sa-rows" data-sa-rows data-field="<?php echo esc_attr($field); ?>">
        <p class="description"><?php echo esc_html($intro); ?></p>
        <div class="sa-rows__list" data-sa-rows-list>
            <?php foreach ($rows as $index => $row) {
                springapex_render_row_editor_row($field, (int) $index, $row, $columns);
            } ?>
        </div>
        <p class="sa-rows__empty" data-sa-rows-empty<?php echo $rows ? ' hidden' : ''; ?>>
            还没有条目，这一块在前台不会显示。
        </p>
        <p>
            <button type="button" class="button" data-sa-rows-add>添加一条</button>
        </p>
        <?php
        // The blank row the Add button clones. `__index__` is swapped for the real
        // position on insert. Inside <template> so its inputs never post.
        ?>
        <template data-sa-rows-template>
            <?php springapex_render_row_editor_row($field, null, [], $columns); ?>
        </template>
    </div>
    <?php
}

/**
 * @param array<string, mixed> $row
 * @param array<int, array{key: string, label: string, type: string, help?: string, half?: bool, default?: string}> $columns
 */
function springapex_render_row_editor_row(string $field, ?int $index, array $row, array $columns): void
{
    $i = $index === null ? '__index__' : (string) $index;
    $name = static fn(string $key): string => sprintf('%s[%s][%s]', $field, $i, $key);
    $id = static fn(string $key): string => sanitize_key($field . '-' . $i . '-' . $key);
    ?>
    <div class="sa-row" data-sa-row>
        <div class="sa-row__head">
            <span class="sa-row__num" data-sa-row-num></span>
            <span class="sa-row__actions">
                <button type="button" class="button-link" data-sa-row-up aria-label="上移">↑</button>
                <button type="button" class="button-link" data-sa-row-down aria-label="下移">↓</button>
                <button type="button" class="button-link sa-row__remove" data-sa-row-remove>删除这条</button>
            </span>
        </div>
        <?php foreach ($columns as $column) :
            $key = (string) $column['key'];
            $type = (string) $column['type'];
            $value = $row[$key] ?? '';
            ?>
            <div class="sa-row__field sa-row__field--<?php echo esc_attr($type); ?><?php echo !empty($column['half']) ? ' sa-row__field--half' : ''; ?>">
                <label for="<?php echo esc_attr($id($key)); ?>"><?php echo esc_html((string) $column['label']); ?></label>
                <?php
                switch ($type) {
                    case 'textarea':
                        printf(
                            '<textarea class="widefat" rows="2" id="%s" name="%s">%s</textarea>',
                            esc_attr($id($key)),
                            esc_attr($name($key)),
                            esc_textarea(is_scalar($value) ? (string) $value : '')
                        );
                        break;

                    case 'icon':
                        $icon = is_scalar($value) ? (string) $value : '';
                        if ($icon === '') {
                            $icon = (string) ($column['default'] ?? '');
                        }
                        springapex_render_row_editor_icon($id($key), $name($key), $icon);
                        break;

                    case 'image':
                        springapex_render_row_editor_image($id($key), $name($key), $row);
                        break;

                    case 'products':
                        springapex_render_row_editor_products($name($key), (array) $value);
                        break;

                    default:
                        printf(
                            '<input class="widefat" type="text" id="%s" name="%s" value="%s">',
                            esc_attr($id($key)),
                            esc_attr($name($key)),
                            esc_attr(is_scalar($value) ? (string) $value : '')
                        );
                }
                ?>
                <?php if (!empty($column['help'])) : ?>
                    <span class="description"><?php echo esc_html((string) $column['help']); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function springapex_render_row_editor_icon(string $id, string $name, string $selected): void
{
    $keys = array_keys(springapex_icon_map());
    sort($keys);
    // A stored key the theme has no icon for renders as a plain arrow on the public
    // page. Listing it here — rather than letting the <select> quietly fall back to
    // its first option — is the only place the operator can ever notice.
    $unknown = $selected !== '' && !in_array($selected, $keys, true);
    ?>
    <select class="widefat" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>">
        <?php if ($unknown) : ?>
            <option value="<?php echo esc_attr($selected); ?>" selected>
                <?php echo esc_html($selected); ?>（这个图标不存在，前台会显示一个箭头，请换一个）
            </option>
        <?php endif; ?>
        <?php foreach ($keys as $key) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($selected, $key); ?>>
                <?php echo esc_html($key); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Media Library picker. Stores an attachment id; the legacy relative path that
 * shipped in the seed rides along in a hidden field so the database-free preview/
 * build keeps working until the operator replaces the picture.
 *
 * @param array<string, mixed> $row
 */
function springapex_render_row_editor_image(string $id, string $name, array $row): void
{
    $attachment_id = (int) ($row['image_id'] ?? 0);
    $legacy = (string) ($row['image'] ?? '');
    $preview = $attachment_id > 0 ? wp_get_attachment_image_url($attachment_id, 'thumbnail') : '';
    if ($preview === '' && $legacy !== '') {
        $preview = springapex_asset('assets/images/' . ltrim($legacy, '/'));
    }
    // The attachment id is the real value and the path is legacy, so the id needs a
    // name of its own: `…[image]` becomes `…[image_id]`, a standalone field just
    // gets the suffix.
    $id_name = str_ends_with($name, ']')
        ? (string) preg_replace('/\[([^\[\]]+)\]$/', '[$1_id]', $name)
        : $name . '_id';
    ?>
    <div class="sa-row__image" data-sa-image>
        <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id_name); ?>"
            value="<?php echo esc_attr((string) $attachment_id); ?>" data-sa-image-id>
        <input type="hidden" name="<?php echo esc_attr($name); ?>"
            value="<?php echo esc_attr($legacy); ?>" data-sa-image-legacy>
        <img src="<?php echo esc_url($preview); ?>" alt="" data-sa-image-preview<?php echo $preview === '' ? ' hidden' : ''; ?>>
        <span class="sa-row__image-actions">
            <button type="button" class="button" data-sa-image-pick>选择图片</button>
            <button type="button" class="button-link" data-sa-image-clear<?php echo $preview === '' ? ' hidden' : ''; ?>>不用图片</button>
        </span>
        <span class="description">不选图片就显示上面选的图标。</span>
    </div>
    <?php
}

/**
 * @param string[] $selected
 */
function springapex_render_row_editor_products(string $name, array $selected): void
{
    $selected = array_map('strval', $selected);
    $products = springapex_product_picker_options();

    if (!$products) {
        echo '<p class="description">还没有任何产品条目，先到「产品」里添加。</p>';
        return;
    }
    ?>
    <ul class="sa-row__products">
        <?php foreach ($products as $product) :
            $slug = (string) $product->post_name;
            ?>
            <li><label>
                <input type="checkbox" name="<?php echo esc_attr($name); ?>[]"
                    value="<?php echo esc_attr($slug); ?>"
                    <?php checked(in_array($slug, $selected, true)); ?>>
                <?php echo esc_html(get_the_title($product)); ?>
            </label></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * Submitted rows, cleaned per column type. Rows whose first column is empty are
 * dropped, which is how the operator deletes one without a confirm dialog.
 *
 * @param array<int, array{key: string, label: string, type: string, help?: string, half?: bool, default?: string}> $columns
 * @return array<int, array<string, mixed>>
 */
function springapex_sanitize_row_editor(mixed $submitted, array $columns): array
{
    if (!is_array($submitted)) {
        return [];
    }

    $first_key = (string) ($columns[0]['key'] ?? '');
    $rows = [];

    foreach ($submitted as $raw) {
        if (!is_array($raw)) {
            continue;
        }
        $row = [];
        foreach ($columns as $column) {
            $key = (string) $column['key'];
            $value = $raw[$key] ?? '';

            switch ((string) $column['type']) {
                case 'textarea':
                    $row[$key] = sanitize_textarea_field(is_scalar($value) ? (string) $value : '');
                    break;

                case 'icon':
                    $value = sanitize_key(is_scalar($value) ? (string) $value : '');
                    // An unknown key is kept, not corrected: rewriting it here would
                    // clear the warning the picker shows without anyone having decided
                    // what the icon should actually be. Only a missing value defaults.
                    $row[$key] = $value !== '' ? $value : (string) ($column['default'] ?? 'target');
                    break;

                case 'image':
                    $row[$key] = sanitize_text_field(is_scalar($value) ? (string) $value : '');
                    $attachment_id = (int) ($raw[$key . '_id'] ?? 0);
                    $row[$key . '_id'] = get_post_type($attachment_id) === 'attachment' ? $attachment_id : 0;
                    break;

                case 'products':
                    $row[$key] = springapex_sanitize_product_slugs($value);
                    break;

                default:
                    $row[$key] = sanitize_text_field(is_scalar($value) ? (string) $value : '');
            }
        }

        if ($first_key !== '' && trim((string) ($row[$first_key] ?? '')) === '') {
            continue;
        }
        $rows[] = $row;
    }

    return $rows;
}
