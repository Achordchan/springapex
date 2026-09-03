<?php
/** WordPress administrator page for read-only infrastructure status. */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_SYSTEM_STATUS_SLUG = 'springapex-system-status';

add_action('admin_menu', 'springapex_system_status_menu', 20);
function springapex_system_status_menu(): void
{
    add_submenu_page(
        'tools.php',
        '系统与存储',
        '系统与存储',
        'manage_options',
        SPRINGAPEX_SYSTEM_STATUS_SLUG,
        'springapex_render_system_status_page'
    );
}

add_action('admin_enqueue_scripts', 'springapex_system_status_assets');
function springapex_system_status_assets(string $hook): void
{
    if (!str_contains($hook, SPRINGAPEX_SYSTEM_STATUS_SLUG)) {
        return;
    }
    wp_enqueue_style(
        'springapex-admin',
        SPRINGAPEX_URI . '/assets/css/admin.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-system-status',
        SPRINGAPEX_URI . '/assets/css/system-status.css',
        ['springapex-admin'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-system-status',
        SPRINGAPEX_URI . '/assets/js/system-status.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
}

function springapex_system_status_transient_key(string $suffix): string
{
    return 'springapex_system_status_' . $suffix . '_' . get_current_user_id();
}

add_action('admin_post_springapex_run_system_status_check', 'springapex_run_system_status_check');
function springapex_run_system_status_check(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('你没有权限运行系统连接检测。', '权限不足', ['response' => 403]);
    }
    check_admin_referer('springapex_run_system_status_check');

    $page_url = admin_url('tools.php?page=' . SPRINGAPEX_SYSTEM_STATUS_SLUG);
    $lock_key = springapex_system_status_transient_key('lock');
    if (get_transient($lock_key) !== false) {
        wp_safe_redirect(add_query_arg('springapex_check', 'busy', $page_url));
        exit;
    }

    set_transient($lock_key, 1, 30);
    // 顺带刷新附件存储统计缓存，让检测后的页面显示最新占用。
    delete_transient('springapex_attachment_footprint_v1');
    try {
        $result = [
            'checked_at' => time(),
            's3' => springapex_system_status_s3_probe(),
            'cdn' => springapex_system_status_cdn_probe(),
        ];
        set_transient(springapex_system_status_transient_key('result'), $result, 15 * MINUTE_IN_SECONDS);
    } finally {
        delete_transient($lock_key);
    }

    wp_safe_redirect(add_query_arg('springapex_check', 'done', $page_url));
    exit;
}

function springapex_system_status_state_label(string $state): string
{
    return match ($state) {
        'ok' => '正常',
        'warning' => '注意',
        'error' => '异常',
        'ready' => '已配置',
        default => '未配置',
    };
}

function springapex_system_status_card(string $title, string $value, string $description, string $state): void
{
    ?>
    <article class="sa-system-status__summary-card sa-system-status__summary-card--<?php echo esc_attr($state); ?>">
      <div class="sa-system-status__summary-head">
        <h2><?php echo esc_html($title); ?></h2>
        <span><?php echo esc_html(springapex_system_status_state_label($state)); ?></span>
      </div>
      <strong><?php echo esc_html($value); ?></strong>
      <p><?php echo esc_html($description); ?></p>
    </article>
    <?php
}

function springapex_system_status_probe_card(string $title, array $probe): void
{
    $state = in_array((string) ($probe['state'] ?? ''), ['ok', 'warning', 'error', 'neutral'], true)
        ? (string) $probe['state']
        : 'neutral';
    ?>
    <article class="sa-system-status__probe sa-system-status__probe--<?php echo esc_attr($state); ?>">
      <header>
        <h3><?php echo esc_html($title); ?></h3>
        <span><?php echo esc_html((string) ($probe['label'] ?? '未执行')); ?></span>
      </header>
      <p><?php echo esc_html((string) ($probe['message'] ?? '')); ?></p>
      <?php if (!empty($probe['details']) && is_array($probe['details'])) : ?>
        <dl>
          <?php foreach ($probe['details'] as $label => $value) : ?>
            <div><dt><?php echo esc_html((string) $label); ?></dt><dd><?php echo esc_html((string) $value); ?></dd></div>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>
    </article>
    <?php
}

function springapex_render_system_status_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('你没有权限查看系统与存储状态。', '权限不足', ['response' => 403]);
    }

    $snapshot = springapex_system_status_snapshot();
    $probe = get_transient(springapex_system_status_transient_key('result'));
    $probe = is_array($probe) ? $probe : null;
    $environment = (string) ($snapshot['environment']['type'] ?? 'production');
    $production = $environment === 'production';
    $s3_enabled = !empty($snapshot['s3']['enabled']);
    $cdn_enabled = !empty($snapshot['cdn']['enabled']);
    $uploads_enabled = !empty($snapshot['private_uploads']['enabled']);
    $images_ok = !empty($snapshot['images']['imagick'])
        && !empty($snapshot['images']['webp'])
        && !empty($snapshot['images']['avif']);
    $diagnostic = springapex_system_status_diagnostic_report($snapshot, $probe);
    $diagnostic_json = wp_json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $check_state = sanitize_key((string) wp_unslash($_GET['springapex_check'] ?? ''));
    ?>
    <div class="wrap sa-admin sa-system-status">
      <div class="sa-admin__header sa-system-status__header">
        <div>
          <h1 class="sa-admin__title">系统与存储</h1>
          <p class="sa-admin__intro">查看当前 WordPress 实例使用的 S3、CDN、私有附件和图片处理状态。这里不保存 AWS 密钥，也不能修改存储桶、CloudFront、备份和生命周期规则。</p>
        </div>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="springapex_run_system_status_check">
          <?php wp_nonce_field('springapex_run_system_status_check'); ?>
          <button type="submit" class="button button-primary">运行连接检测</button>
        </form>
      </div>

      <?php if ($check_state === 'busy') : ?>
        <div class="notice notice-warning inline"><p>检测正在进行，请稍后刷新页面查看结果。</p></div>
      <?php endif; ?>

      <div class="sa-notice">
        <strong>控制边界：</strong>
        <span>页面默认只读取本机状态。点击检测时会在私有 S3 前缀下创建一个不含业务数据的临时对象，完成写入、读取后立即删除；CDN 检测只发出 HEAD 请求。</span>
      </div>

      <section class="sa-system-status__summary" aria-label="基础设施状态概览">
        <?php
        springapex_system_status_card(
            'S3 私有存储',
            $s3_enabled ? '已配置' : ($production ? '缺少配置' : '本地未配置'),
            $s3_enabled
                ? sprintf('%s · %d 个删除重试', (string) $snapshot['s3']['region'], (int) $snapshot['s3']['retry_count'])
                : '存储桶与区域由 wp-config.php 提供。',
            $s3_enabled ? 'ready' : ($production ? 'error' : 'neutral')
        );
        springapex_system_status_card(
            'CDN 静态资源',
            $cdn_enabled ? (string) $snapshot['cdn']['host'] : ($production ? '缺少配置' : '本地未配置'),
            $cdn_enabled ? '当前主题资源版本 ' . SPRINGAPEX_VERSION : 'CDN 地址由服务器常量提供。',
            $cdn_enabled ? 'ready' : ($production ? 'error' : 'neutral')
        );
        springapex_system_status_card(
            '询盘附件保护',
            (string) $snapshot['private_uploads']['mode'],
            $uploads_enabled ? '附件不会通过公开媒体 URL 暴露。' : '图纸上传会保持禁用，普通询盘仍可保存。',
            $uploads_enabled ? 'ok' : ($production ? 'error' : 'neutral')
        );
        springapex_system_status_card(
            '图片处理',
            $images_ok ? 'WebP + AVIF' : '支持不完整',
            sprintf(
                'Imagick %s，WebP %s，AVIF %s。',
                !empty($snapshot['images']['imagick']) ? '可用' : '不可用',
                !empty($snapshot['images']['webp']) ? '可用' : '不可用',
                !empty($snapshot['images']['avif']) ? '可用' : '不可用'
            ),
            $images_ok ? 'ok' : 'warning'
        );
        $storage = is_array($snapshot['storage'] ?? null) ? $snapshot['storage'] : [];
        $storage_files = (int) ($storage['files'] ?? 0);
        $storage_bytes = (int) ($storage['bytes'] ?? 0);
        $storage_trashed = (int) ($storage['trashed_files'] ?? 0);
        springapex_system_status_card(
            '询盘附件存储',
            $storage_files > 0 ? size_format($storage_bytes, 1) : '0 B',
            $storage_files > 0
                ? sprintf(
                    '%d 个附件 · %d 封询盘%s',
                    $storage_files,
                    (int) ($storage['inquiries'] ?? 0),
                    $storage_trashed > 0 ? '；回收站 ' . $storage_trashed . ' 个待清理' : ''
                )
                : '目前没有询盘上传过图纸。',
            $storage_files > 0 ? 'ok' : 'neutral'
        );
        ?>
      </section>

      <?php
      $storage = is_array($snapshot['storage'] ?? null) ? $snapshot['storage'] : [];
      $trash_days = (int) ($snapshot['trash']['empty_days'] ?? 30);
      $inquiry_counts = (array) wp_count_posts('spring_inquiry');
      $inquiry_total = array_sum(array_map('intval', $inquiry_counts));
      $storage_bytes = (int) ($storage['bytes'] ?? 0);
      $monthly_cost = ($storage_bytes / GB_IN_BYTES) * 0.023; // S3 标准存储粗估
      $cost_text = $storage_bytes <= 0
          ? '$0.00/月'
          : ($monthly_cost < 0.01 ? '不到 $0.01/月' : sprintf('约 $%.2f/月', $monthly_cost));
      $retry_count = (int) ($snapshot['s3']['retry_count'] ?? 0);
      $next_retry = (int) ($snapshot['s3']['next_retry'] ?? 0);
      ?>
      <section class="sa-card">
        <header class="sa-card__head">
          <h2 class="sa-card__title">存储占用与清理</h2>
          <p class="sa-card__desc">询盘图纸存放在 S3。删除询盘会连带清理对应文件，不会无限堆积——下面是当前占用与清理机制。</p>
        </header>
        <div class="sa-card__body">
          <table class="widefat striped sa-system-status__table">
            <tbody>
              <tr><th>附件总占用</th><td><?php echo esc_html(size_format($storage_bytes, 1) ?: '0 B'); ?> · 共 <?php echo (int) ($storage['files'] ?? 0); ?> 个文件（其中 S3 对象 <?php echo (int) ($storage['s3_files'] ?? 0); ?> 个）</td></tr>
              <tr><th>有附件的询盘</th><td><?php echo (int) ($storage['inquiries'] ?? 0); ?> 封（询盘总数 <?php echo (int) $inquiry_total; ?> 封）</td></tr>
              <tr><th>回收站待清理</th><td><?php echo (int) ($storage['trashed_files'] ?? 0); ?> 个附件 · <?php echo esc_html(size_format((int) ($storage['trashed_bytes'] ?? 0), 1) ?: '0 B'); ?>（永久删除后释放，此前仍在计费）</td></tr>
              <tr><th>回收站自动清空</th><td><?php echo $trash_days > 0 ? esc_html($trash_days . ' 天后自动永久删除，届时清理对应 S3 文件') : '已关闭：删除即永久删除并立刻清理 S3'; ?></td></tr>
              <tr><th>S3 删除重试队列</th><td><?php echo $retry_count; ?> 项<?php echo $next_retry > 0 ? '；下次：' . esc_html(wp_date('Y-m-d H:i:s', $next_retry)) : ''; ?></td></tr>
              <tr><th>估算月度存储成本</th><td><?php echo esc_html($cost_text); ?> <span class="description">（S3 标准存储约 $0.023/GB 粗估，未含流量与取回）</span></td></tr>
              <tr><th>统计时间</th><td><?php echo esc_html(wp_date('Y-m-d H:i:s', (int) ($storage['generated_at'] ?? time()))); ?><?php echo !empty($storage['truncated']) ? '（数量较多，以上为前 20000 封的下限统计）' : ''; ?> · 点右上角「运行连接检测」可刷新</td></tr>
            </tbody>
          </table>
          <div class="sa-notice">
            <strong>清理机制：</strong>
            <span>后台删除询盘分两步——先进「回收站」（图纸仍在 S3、仍计费），永久删除或 <?php echo (int) $trash_days; ?> 天后自动清空回收站时，系统才删掉对应 S3 文件；删除若遇网络故障会自动排队重试。保留中的询盘，其图纸会一直占用存储。</span>
          </div>
        </div>
      </section>

      <?php if ($probe !== null) : ?>
        <section class="sa-card">
          <header class="sa-card__head">
            <h2 class="sa-card__title">最近一次连接检测</h2>
            <p class="sa-card__desc">检测时间：<?php echo esc_html(wp_date('Y-m-d H:i:s', (int) ($probe['checked_at'] ?? time()))); ?></p>
          </header>
          <div class="sa-card__body sa-system-status__probe-grid">
            <?php springapex_system_status_probe_card('S3 写入 / 读取 / 删除', is_array($probe['s3'] ?? null) ? $probe['s3'] : []); ?>
            <?php springapex_system_status_probe_card('CDN 当前版本资源', is_array($probe['cdn'] ?? null) ? $probe['cdn'] : []); ?>
          </div>
        </section>
      <?php endif; ?>

      <section class="sa-card">
        <header class="sa-card__head">
          <h2 class="sa-card__title">存储与交付配置</h2>
          <p class="sa-card__desc">仅展示脱敏后的有效配置来源，不显示凭据。</p>
        </header>
        <div class="sa-card__body">
          <table class="widefat striped sa-system-status__table">
            <tbody>
              <tr><th>运行环境</th><td><?php echo esc_html($environment); ?> · <?php echo esc_html((string) $snapshot['environment']['site_host']); ?></td></tr>
              <tr><th>S3 存储桶</th><td><?php echo esc_html((string) $snapshot['s3']['bucket']); ?></td></tr>
              <tr><th>S3 区域 / 私有前缀</th><td><?php echo esc_html((string) $snapshot['s3']['region']); ?> · <?php echo esc_html((string) $snapshot['s3']['prefix']); ?></td></tr>
              <tr><th>AWS 凭据来源</th><td><?php echo esc_html((string) $snapshot['s3']['credentials']); ?>；WordPress 不保存 Access Key。</td></tr>
              <tr><th>S3 删除重试</th><td><?php echo (int) $snapshot['s3']['retry_count']; ?> 项<?php echo (int) $snapshot['s3']['next_retry'] > 0 ? '；下次：' . esc_html(wp_date('Y-m-d H:i:s', (int) $snapshot['s3']['next_retry'])) : ''; ?></td></tr>
              <tr><th>CDN 资源基址</th><td><code><?php echo esc_html((string) $snapshot['cdn']['asset_base']); ?></code></td></tr>
              <tr><th>版本化资源</th><td><?php echo !empty($snapshot['cdn']['versioned']) ? '是，当前版本路径不会被覆盖' : '否或未配置'; ?></td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="sa-card">
        <header class="sa-card__head">
          <h2 class="sa-card__title">上传、部署与备份</h2>
          <p class="sa-card__desc">WordPress 能读取本进程配置，但不能读取 Nginx、CloudFront 和宝塔任务的全部状态。</p>
        </header>
        <div class="sa-card__body">
          <table class="widefat striped sa-system-status__table">
            <tbody>
              <tr><th>PHP 单文件限制</th><td><?php echo esc_html((string) $snapshot['uploads']['upload_max']); ?></td></tr>
              <tr><th>PHP 请求限制</th><td><?php echo esc_html((string) $snapshot['uploads']['post_max']); ?></td></tr>
              <tr><th>WordPress 实际上限</th><td><?php echo esc_html(size_format((int) $snapshot['uploads']['wordpress_max'])); ?></td></tr>
              <tr><th>满足主题建议值</th><td><?php echo !empty($snapshot['uploads']['meets_recommendation']) ? '是' : '否，需要核对 10 MB / 12 MB 配置'; ?></td></tr>
              <tr><th>代码部署</th><td><?php echo esc_html((string) $snapshot['operations']['deployment']); ?></td></tr>
              <tr><th>服务器备份</th><td><?php echo esc_html((string) $snapshot['operations']['backup']); ?></td></tr>
              <tr><th>最近运行结果</th><td><?php echo esc_html((string) $snapshot['operations']['status_feed']); ?></td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <details class="sa-card sa-system-status__details">
        <summary class="sa-card__head"><h2 class="sa-card__title"><span class="sa-card__name">哪些配置不能在这里修改</span></h2></summary>
        <div class="sa-card__body">
          <ul>
            <li>Access Key、Secret Key、Session Token 和 EC2 IAM Role。</li>
            <li>S3 Bucket、Region、生命周期、版本控制和公开访问策略。</li>
            <li>CloudFront Distribution、Origin、WAF、缓存行为和全站失效。</li>
            <li>备份恢复、备份删除、Nginx、证书和宝塔定时任务。</li>
          </ul>
          <p>这些配置继续由 AWS、服务器配置和仓库部署流程管理，避免 WordPress 数据库成为第二套基础设施真相。</p>
        </div>
      </details>

      <details class="sa-card sa-system-status__details">
        <summary class="sa-card__head"><h2 class="sa-card__title"><span class="sa-card__name">脱敏诊断信息</span></h2></summary>
        <div class="sa-card__body">
          <p class="description">可复制给开发或运维人员。内容不包含 AWS 密钥、完整存储桶名称或客户附件。</p>
          <textarea class="large-text code sa-system-status__report" rows="18" readonly data-system-status-report><?php echo esc_textarea(is_string($diagnostic_json) ? $diagnostic_json : '{}'); ?></textarea>
          <div class="sa-system-status__copy-row">
            <button type="button" class="button" data-system-status-copy>复制诊断信息</button>
            <span role="status" aria-live="polite" data-system-status-copy-result></span>
          </div>
        </div>
      </details>
    </div>
    <?php
}
