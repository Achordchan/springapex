<?php
/**
 * 网站内容 field search.
 *
 * Content is split across many screens, so a plain in-page filter would only
 * cover the screen you are already on. Instead we build one index of every
 * field across all screens — label, English key/path, help text, section and
 * screen name, and the current value — and let the operator search it from the
 * overview or any sub-screen. Results link straight to the field's section and
 * highlight it on arrival, so "which page is the phone number on again?" is one
 * keystroke instead of a hunt.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multibyte helpers with single-byte fallbacks. The theme only requires PHP 8.0
 * and mbstring is an optional extension, so opening 网站内容 must not fatal on a
 * host without it — degrade to byte functions instead.
 */
function springapex_admin_strlen(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value);
    }
    // Count UTF-8 code points, not bytes, so length checks match how mb_* would
    // measure Han text; fall back to bytes only if the string is not valid UTF-8.
    $count = preg_match_all('/./us', $value);
    return $count === false ? strlen($value) : $count;
}

function springapex_admin_substr(string $value, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length);
    }
    // Slice on UTF-8 code-point boundaries so truncation never splits a
    // multibyte character into invalid bytes (which WP escaping would drop).
    if (preg_match_all('/./us', $value, $m)) {
        return implode('', array_slice($m[0], $start, $length));
    }
    return substr($value, $start, $length ?? PHP_INT_MAX);
}

function springapex_admin_strtolower(string $value): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
}

/**
 * Flatten any stored value (scalar, list, or nested repeater rows) into one
 * searchable string, so searching the content — not just the label — works.
 */
function springapex_admin_search_stringify(mixed $value, int $depth = 0): string
{
    if (is_scalar($value)) {
        return trim((string) $value);
    }
    if (is_array($value) && $depth < 5) {
        $parts = [];
        foreach ($value as $item) {
            $piece = springapex_admin_search_stringify($item, $depth + 1);
            if ($piece !== '') {
                $parts[] = $piece;
            }
        }
        return implode(' ', $parts);
    }
    return '';
}

/**
 * One flat entry per field. With no argument this covers every screen (the
 * overview's global search); pass a screen key to limit it to that one screen
 * (a sub-screen only searches its own page). Cached per scope for the request.
 */
function springapex_admin_search_index(?string $only_screen = null): array
{
    static $cache = [];
    $cache_key = $only_screen ?? '*';
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $index = [];
    foreach (springapex_admin_screens() as $screen_key => $screen) {
        if ($only_screen !== null && (string) $screen_key !== $only_screen) {
            continue;
        }
        $screen_label = (string) ($screen['label'] ?? $screen_key);
        $sections = array_values((array) ($screen['sections'] ?? []));

        foreach ($sections as $i => $section) {
            $section_title = (string) ($section['title'] ?? '');
            $anchor = springapex_admin_section_id((string) $screen_key, (int) $i);

            foreach ((array) ($section['fields'] ?? []) as $field) {
                $path = (string) ($field['path'] ?? '');
                if ($path === '') {
                    continue;
                }
                $label = (string) ($field['label'] ?? '');
                $help = (string) ($field['help'] ?? '');

                // Repeater sub-field labels help you find "the list that has a
                // 'job title' column" even when no row is filled in yet.
                $sub_labels = '';
                if ((string) ($field['type'] ?? '') === 'repeater') {
                    foreach ((array) ($field['fields'] ?? []) as $sub) {
                        $sub_labels .= ' ' . (string) ($sub['label'] ?? '');
                    }
                }

                $content = springapex_admin_search_stringify(springapex_get($path));
                $snippet = $content;
                if (springapex_admin_strlen($snippet) > 90) {
                    $snippet = springapex_admin_substr($snippet, 0, 90) . '…';
                }

                $haystack = springapex_admin_strtolower(trim(
                    $label . ' ' . $path . ' ' . $help . ' '
                    . $section_title . ' ' . $screen_label . ' '
                    . $sub_labels . ' ' . $content
                ));

                $index[] = [
                    'screen' => (string) $screen_key,
                    'anchor' => $anchor,
                    'screenLabel' => $screen_label,
                    'section' => $section_title,
                    'label' => $label !== '' ? $label : $path,
                    'path' => $path,
                    'snippet' => $snippet,
                    'text' => $haystack,
                ];
            }
        }
    }

    $cache[$cache_key] = $index;
    return $index;
}

/**
 * The search box. On the overview ($screen = null) it searches every screen and
 * results link out to each field's page; on a sub-screen it searches only that
 * screen and jumps in place. Each box carries its own index inline; the link
 * base is printed once per request.
 */
function springapex_admin_render_search(?string $screen = null): void
{
    static $base_printed = false;

    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $index = springapex_admin_search_index($screen);
    $is_global = $screen === null;
    $label = $is_global ? '搜索所有页面的内容字段' : '搜索本页字段';
    $placeholder = $is_global
        ? '输入字段名或页面上的文字，快速定位到任意页面（例如“电话”“hero”“质量”）'
        : '输入字段名或本页的文字，快速定位（例如“标题”“按钮”“图片”）';
    ?>
    <div class="sa-search" data-sa-search data-sa-search-scope="<?php echo esc_attr((string) $screen); ?>">
        <label class="sa-search__label"><?php echo esc_html($label); ?></label>
        <div class="sa-search__row">
            <input type="search" class="sa-input sa-search__input"
                   data-sa-search-input autocomplete="off"
                   placeholder="<?php echo esc_attr($placeholder); ?>">
            <button type="button" class="button sa-search__clear" data-sa-search-clear hidden>清空</button>
        </div>
        <p class="sa-search__hint" data-sa-search-hint></p>
        <ul class="sa-search__results" data-sa-search-results hidden></ul>
        <script type="application/json" data-sa-search-data><?php
            echo (string) wp_json_encode($index, $flags);
        ?></script>
    </div>
    <?php
    if (!$base_printed) {
        $base_printed = true;
        printf(
            '<script>window.SA_ADMIN_SEARCH = {base: %s};</script>',
            (string) wp_json_encode(admin_url('admin.php?page=' . SPRINGAPEX_ADMIN_SLUG . '-'), $flags)
        );
    }
}
