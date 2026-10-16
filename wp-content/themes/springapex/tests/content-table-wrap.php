<?php
/**
 * 正文表格包一层横向滚动容器：普通表格、嵌套表格、已包过的表格、没有表格的正文。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

// helpers.php 顶部会加载 downloads.php；这里只取出要测的函数，避免拉起整个主题。
$source = (string) file_get_contents(__DIR__ . '/../inc/helpers.php');
preg_match('/function springapex_wrap_content_tables\(.*?\n}\n/s', $source, $match);
assert_same(1, count($match), 'springapex_wrap_content_tables() exists in inc/helpers.php.');
eval($match[0]);

$plain = '<p>Intro</p><p>No tables here.</p>';
assert_same($plain, springapex_wrap_content_tables($plain), 'Content without tables is returned unchanged.');

assert_same(
    '<p>A</p><div class="sa-table-scroll"><table class="x"><tr><td>1</td></tr></table></div><p>B</p>',
    springapex_wrap_content_tables('<p>A</p><table class="x"><tr><td>1</td></tr></table><p>B</p>'),
    'A table is wrapped in a scroll container.'
);

assert_same(
    '<div class="sa-table-scroll"><TABLE><tr><td>1</td></tr></TABLE></div><div class="sa-table-scroll"><table><tr><td>2</td></tr></table></div>',
    springapex_wrap_content_tables('<TABLE><tr><td>1</td></tr></TABLE><table><tr><td>2</td></tr></table>'),
    'Each table gets its own wrapper, case-insensitively.'
);

assert_same(
    '<div class="sa-table-scroll"><table><tr><td><table><tr><td>in</td></tr></table></td></tr></table></div>',
    springapex_wrap_content_tables('<table><tr><td><table><tr><td>in</td></tr></table></td></tr></table>'),
    'Only the outermost of nested tables is wrapped.'
);

$already = '<div class="sa-table-scroll"><table><tr><td>1</td></tr></table></div>';
assert_same($already, springapex_wrap_content_tables($already), 'An already wrapped table is not wrapped twice.');

assert_same(
    '<p><tablet></p>',
    springapex_wrap_content_tables('<p><tablet></p>'),
    'Tags that only start with "table" are ignored.'
);

assert_same(
    "<div class=\"sa-table-scroll\"><table><tr><td>1</td></tr></table ></div><h2>Next</h2><div class=\"sa-table-scroll\"><table><tr><td>2</td></tr></TABLE\n></div>",
    springapex_wrap_content_tables("<table><tr><td>1</td></tr></table ><h2>Next</h2><table><tr><td>2</td></tr></TABLE\n>"),
    'Closing tags with whitespace before ">" still close the wrapper.'
);

$script = '<p>A</p><script>const markup = "<table><tr><td>Value</td></tr></table>";</script><!-- <table> --><p data-x="<table>">B</p><textarea><table></table></textarea>';
assert_same($script, springapex_wrap_content_tables($script), 'Tables inside scripts, comments, attribute values and textareas are left alone.');

assert_same(
    '<script>var t = "<table>";</script><div class="sa-table-scroll"><table><tr><td>1</td></tr></table></div>',
    springapex_wrap_content_tables('<script>var t = "<table>";</script><table><tr><td>1</td></tr></table>'),
    'A real table after a script is still wrapped.'
);

echo "content table wrap: ok\n";
