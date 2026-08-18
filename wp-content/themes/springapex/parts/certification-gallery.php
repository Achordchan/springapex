<?php
if (!defined('ABSPATH')) {
    exit;
}

$certificates = is_array($args['certificates'] ?? null) ? $args['certificates'] : [];
$gallery_id = sanitize_key((string) ($args['id'] ?? 'springapex-certificates'));
$variant = sanitize_key((string) ($args['variant'] ?? 'default'));
$is_strip = $variant === 'strip';
$viewer = !empty($args['viewer']);
if (!$certificates) {
    return;
}
?>
<div class="sa-certificate-gallery<?php echo $is_strip ? ' sa-certificate-gallery--strip' : ''; ?>" id="<?php echo esc_attr($gallery_id); ?>">
  <?php if (!$is_strip) : ?>
    <div class="sa-certificate-gallery__head">
      <div>
        <p class="section-kicker"><?php esc_html_e('VERIFIED CERTIFICATES', 'springapex'); ?></p>
        <h3><?php esc_html_e('Open the current certificate documents.', 'springapex'); ?></h3>
      </div>
      <p><?php esc_html_e('Select a certificate to view the supplied original document.', 'springapex'); ?></p>
    </div>
  <?php endif; ?>
  <div class="sa-certificate-gallery__grid"<?php echo $is_strip ? ' data-certificate-carousel' : ''; ?>>
    <?php foreach ($certificates as $certificate) : ?>
      <?php
      $document = trim((string) ($certificate['document'] ?? ''));
      $document_url = springapex_file_url($document, 'assets/documents');
      ?>
      <?php if ($viewer) : ?>
        <button
          class="sa-certificate-card sa-certificate-card--viewer"
          type="button"
          data-certificate-open
          data-certificate-src="<?php echo esc_url($document_url); ?>"
          data-certificate-title="<?php echo esc_attr((string) ($certificate['name'] ?? '')); ?>"
          data-certificate-scope="<?php echo esc_attr((string) ($certificate['scope'] ?? '')); ?>"
          data-certificate-validity="<?php echo esc_attr((string) ($certificate['valid_until'] ?? '')); ?>"
          aria-haspopup="dialog"
          aria-controls="<?php echo esc_attr($gallery_id); ?>-viewer"
        >
      <?php else : ?>
        <a class="sa-certificate-card" href="<?php echo esc_url($document_url); ?>" target="_blank" rel="noopener noreferrer">
      <?php endif; ?>
        <span class="sa-certificate-card__media">
          <?php echo springapex_image((string) ($certificate['image'] ?? ''), (string) ($certificate['name'] ?? ''), [
              'width' => 640,
              'height' => 900,
              'sizes' => '(max-width: 700px) 74px, 92px',
          ]); ?>
        </span>
        <span class="sa-certificate-card__copy">
          <strong><?php echo esc_html((string) ($certificate['name'] ?? '')); ?></strong>
          <small><?php echo esc_html((string) ($certificate['scope'] ?? '')); ?></small>
          <span><?php echo esc_html((string) ($certificate['valid_until'] ?? '')); ?></span>
        </span>
        <?php echo springapex_icon($viewer ? 'search' : 'arrow-right', 'icon icon-sm sa-certificate-card__arrow'); ?>
      <?php if ($viewer) : ?>
        </button>
      <?php else : ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <?php if ($viewer) : ?>
    <dialog class="sa-certificate-viewer" id="<?php echo esc_attr($gallery_id); ?>-viewer" data-certificate-dialog aria-labelledby="<?php echo esc_attr($gallery_id); ?>-viewer-title">
      <div class="sa-certificate-viewer__shell">
        <header class="sa-certificate-viewer__header">
          <div>
            <p class="section-kicker"><?php esc_html_e('CERTIFICATE', 'springapex'); ?></p>
            <h3 id="<?php echo esc_attr($gallery_id); ?>-viewer-title" data-certificate-dialog-title></h3>
            <p data-certificate-dialog-meta></p>
          </div>
          <button class="sa-certificate-viewer__close" type="button" data-certificate-close aria-label="<?php esc_attr_e('Close certificate', 'springapex'); ?>">
            <?php echo springapex_icon('close', 'icon'); ?>
          </button>
        </header>
        <div class="sa-certificate-viewer__document">
          <img data-certificate-dialog-image src="" alt="" width="1400" height="1980">
        </div>
      </div>
    </dialog>
  <?php endif; ?>
</div>
