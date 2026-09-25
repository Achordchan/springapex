<?php
/**
 * 新闻作者：新闻详情页右侧最上方的署名卡片（头像、姓名、职位、简介）。
 *
 * 作者是独立条目（spring_news_author，注册在 inc/post-types.php），不是
 * WordPress 用户：署名的人不一定有后台账号，为署名去建账号既要邮箱，又平白
 * 多出能登录的入口；何况现有新闻的发布者都是同一个管理员。关于页的团队成员
 * 是另一份内容，这里不共用。卡片不展示任何联系方式，所以也不收。
 *
 * 姓名 = 标题；头像 = 特色图像；职位、简介 = 下面两个 meta。新闻只存选中作者
 * 的 ID（SPRINGAPEX_NEWS_AUTHOR_META，见 inc/news-meta.php）。前台只认已发布
 * 的作者：移入回收站后卡片随即消失，还原后回来。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_NEWS_AUTHOR_ROLE_META = '_springapex_author_role';
const SPRINGAPEX_NEWS_AUTHOR_BIO_META = '_springapex_author_bio';

add_action('add_meta_boxes_spring_news_author', static function (): void {
    add_meta_box(
        'springapex-news-author-profile',
        '作者信息',
        'springapex_render_news_author_meta_box',
        'spring_news_author',
        'normal',
        'high'
    );
});

function springapex_render_news_author_meta_box(WP_Post $post): void
{
    $post_id = (int) $post->ID;
    wp_nonce_field('springapex_save_news_author', 'springapex_news_author_nonce');
    ?>
    <p class="description">在新闻「基础信息」里选中这位作者后，详情页右侧最上方会显示作者卡片：头像、姓名、职位和简介。姓名填在上方标题栏，头像在右侧「头像」框上传。下面的文字会原样出现在前台，请用英文填写。</p>
    <p>
      <label for="springapex-author-role"><strong>职位 / 头衔</strong></label>
      <input class="widefat" type="text" id="springapex-author-role"
          name="springapex_author_role" placeholder="Senior Spring Engineer"
          value="<?php echo esc_attr((string) get_post_meta($post_id, SPRINGAPEX_NEWS_AUTHOR_ROLE_META, true)); ?>">
    </p>
    <p class="description">显示在姓名下方。留空就只显示姓名。</p>
    <p>
      <label for="springapex-author-bio"><strong>简介</strong></label>
      <textarea class="widefat" rows="3" id="springapex-author-bio"
          name="springapex_author_bio"><?php echo esc_textarea((string) get_post_meta($post_id, SPRINGAPEX_NEWS_AUTHOR_BIO_META, true)); ?></textarea>
    </p>
    <p class="description">可选。一两句话介绍专业背景，显示在头像下方；留空就不显示这一段。</p>
    <?php
}

add_action('save_post_spring_news_author', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_news_author_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_news_author') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    update_post_meta($post_id, SPRINGAPEX_NEWS_AUTHOR_ROLE_META, sanitize_text_field(
        springapex_admin_request_scalar($_POST['springapex_author_role'] ?? '')
    ));
    update_post_meta($post_id, SPRINGAPEX_NEWS_AUTHOR_BIO_META, sanitize_textarea_field(
        springapex_admin_request_scalar($_POST['springapex_author_bio'] ?? '')
    ));
});

add_filter('enter_title_here', static function (string $text, mixed $post): string {
    return $post instanceof WP_Post && $post->post_type === 'spring_news_author'
        ? '作者姓名（英文，前台原样显示）'
        : $text;
}, 10, 2);

add_filter('admin_post_thumbnail_html', static function (string $content, mixed $post_id): string {
    if (get_post_type((int) $post_id) !== 'spring_news_author') {
        return $content;
    }

    return $content . '<p class="description">建议上传正方形照片（至少 240×240 像素），前台裁成圆形显示。没有头像时显示姓名首字母。</p>';
}, 10, 2);

// ---- 作者列表：头像、职位，以及被多少篇新闻署名（移入回收站前看得到影响面）。

add_filter('manage_spring_news_author_posts_columns', static function (array $columns): array {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox">',
        'springapex_avatar' => '头像',
        'title' => '姓名',
        'springapex_role' => '职位',
        'springapex_usage' => '署名新闻',
    ];
});

add_action('manage_spring_news_author_posts_custom_column', static function (string $column, int $post_id): void {
    if ($column === 'springapex_avatar') {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        echo $thumbnail_id > 0
            ? wp_get_attachment_image($thumbnail_id, [40, 40], false, ['alt' => ''])
            : '<span aria-hidden="true">—</span><span class="screen-reader-text">未上传头像</span>';
        return;
    }
    if ($column === 'springapex_role') {
        $role = (string) get_post_meta($post_id, SPRINGAPEX_NEWS_AUTHOR_ROLE_META, true);
        echo $role !== '' ? esc_html($role) : '<span aria-hidden="true">—</span>';
        return;
    }
    if ($column === 'springapex_usage') {
        $count = springapex_news_author_usage_count($post_id);
        echo $count > 0 ? esc_html(sprintf('%d 篇', $count)) : '<span aria-hidden="true">—</span><span class="screen-reader-text">没有新闻选用</span>';
    }
}, 10, 2);

add_action('admin_enqueue_scripts', static function (string $hook): void {
    if ($hook !== 'edit.php') {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || (string) $screen->post_type !== 'spring_news_author') {
        return;
    }

    wp_enqueue_style(
        'springapex-news-author-admin',
        SPRINGAPEX_URI . '/assets/css/news-author-admin.css',
        [],
        SPRINGAPEX_VERSION
    );
});

/** 选用这位作者的新闻篇数（不含回收站）。 */
function springapex_news_author_usage_count(int $author_id): int
{
    $query = new WP_Query([
        'post_type' => 'spring_news',
        'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
        'meta_query' => [
            ['key' => SPRINGAPEX_NEWS_AUTHOR_META, 'value' => (string) $author_id],
        ],
        'fields' => 'ids',
        'posts_per_page' => 1,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    return (int) $query->found_posts;
}

/**
 * 新闻编辑页下拉框的选项：已发布的作者，按姓名排序。
 *
 * @return array<int, string> 作者 ID ⇒ 「姓名 · 职位」
 */
function springapex_news_author_choices(): array
{
    $choices = [];
    foreach (get_posts([
        'post_type' => 'spring_news_author',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]) as $author) {
        $name = trim((string) $author->post_title);
        $role = (string) get_post_meta((int) $author->ID, SPRINGAPEX_NEWS_AUTHOR_ROLE_META, true);
        $label = $name !== '' ? $name : '（未填姓名，前台不显示）';
        $choices[(int) $author->ID] = $role !== '' ? $label . ' · ' . $role : $label;
    }

    return $choices;
}

/**
 * 新闻里存的作者 ID。只要仍是作者条目就保留，不管是否已发布：作者被临时移入
 * 回收站时，编辑并保存新闻不会把选择悄悄清掉，作者还原后卡片照常回来。
 */
function springapex_sanitize_news_author_id(mixed $value): int
{
    $author_id = is_scalar($value) ? absint($value) : 0;

    return $author_id > 0 && get_post_type($author_id) === 'spring_news_author' ? $author_id : 0;
}

/**
 * 前台卡片数据；没选、作者未发布或没填姓名时为 null（不显示卡片）。
 *
 * @return array{name: string, role: string, bio: string, initials: string, avatar: array{id: int, file: string}}|null
 */
function springapex_news_author_profile(int $author_id): ?array
{
    if ($author_id <= 0) {
        return null;
    }

    $author = get_post($author_id);
    if (
        !($author instanceof WP_Post) ||
        $author->post_type !== 'spring_news_author' ||
        $author->post_status !== 'publish'
    ) {
        return null;
    }

    $name = trim((string) $author->post_title);
    if ($name === '') {
        return null;
    }

    return [
        'name' => (string) get_the_title($author),
        'role' => (string) get_post_meta($author_id, SPRINGAPEX_NEWS_AUTHOR_ROLE_META, true),
        'bio' => (string) get_post_meta($author_id, SPRINGAPEX_NEWS_AUTHOR_BIO_META, true),
        'initials' => springapex_news_author_initials($name),
        'avatar' => ['id' => (int) get_post_thumbnail_id($author), 'file' => ''],
    ];
}

/** 没有头像时的占位：前两个词的首字母，如 "Daniel Wu" ⇒ "DW"。 */
function springapex_news_author_initials(string $name): string
{
    $initials = '';
    $count = 0;
    foreach (preg_split('/\s+/u', trim($name)) ?: [] as $word) {
        if ($count >= 2) {
            break;
        }
        if (preg_match('/^[\p{L}\p{N}]/u', $word, $match)) {
            $initials .= $match[0];
            $count++;
        }
    }

    return strtoupper($initials);
}
