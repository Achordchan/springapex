<?php
/**
 * 「相关产品」选择器：从真实的产品条目里勾选，而不是手打 slug。
 *
 * Shared by the news meta box and the industry-solution meta box. Both store the
 * value as a list of slugs, because that is the shape the templates and the seed
 * arrays already use (`springapex_product($slug)`).
 *
 * A hand-typed slug field fails silently — one typo and the card just disappears
 * with no feedback — so no screen in this admin should offer one.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Published products, in the order the operator sees them in their own list.
 *
 * @return WP_Post[]
 */
function springapex_product_picker_options(): array
{
    return get_posts([
        'post_type' => 'spring_product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);
}

/**
 * Checkbox list of every product.
 *
 * @param string   $field    Request key; posts as `$field[]`.
 * @param string[] $selected Currently stored slugs.
 * @param string   $intro    Consequence text: where the picks show up on the site.
 */
function springapex_render_product_picker(string $field, array $selected, string $intro): void
{
    $products = springapex_product_picker_options();

    if (!$products) {
        ?>
        <p>还没有任何产品条目，所以这里没得选。先到「产品」里添加产品，再回来勾选。</p>
        <?php
        return;
    }
    ?>
    <p class="description"><?php echo esc_html($intro); ?></p>
    <?php
    // Presence marker: without it, unchecking the last box posts nothing at all and
    // the save handler cannot tell "emptied" from "this form was never shown".
    ?>
    <input type="hidden" name="<?php echo esc_attr($field); ?>_present" value="1">
    <ul class="springapex-product-picker">
        <?php foreach ($products as $product) :
            $slug = (string) $product->post_name;
            ?>
            <li>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($field); ?>[]"
                        value="<?php echo esc_attr($slug); ?>"
                        <?php checked(in_array($slug, $selected, true)); ?>>
                    <?php echo esc_html(get_the_title($product)); ?>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * Submitted slugs, reduced to the ones that still resolve to a published product,
 * so a renamed or deleted product cannot leave a dead card on the front end.
 *
 * @return string[]
 */
function springapex_sanitize_product_slugs(mixed $submitted): array
{
    $valid = [];
    foreach ((array) $submitted as $slug) {
        $slug = sanitize_title(is_scalar($slug) ? (string) $slug : '');
        if ($slug === '' || in_array($slug, $valid, true)) {
            continue;
        }
        $product = get_page_by_path($slug, OBJECT, 'spring_product');
        if ($product instanceof WP_Post && (string) $product->post_status === 'publish') {
            $valid[] = $slug;
        }
    }

    return $valid;
}
