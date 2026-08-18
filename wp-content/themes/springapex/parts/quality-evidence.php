<?php
if (!defined('ABSPATH')) {
    exit;
}

$verification = springapex_get('capabilities.verification', []);
$specs = is_array($verification['specs'] ?? null) ? $verification['specs'] : [];
$title = (string) ($args['title'] ?? __('Verification matched to your drawing and application.', 'springapex'));
if (!$specs) {
    return;
}
?>
<section class="section sa-evidence sa-evidence--custom">
  <div class="container container-wide">
    <div class="sa-evidence__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('QUALITY EVIDENCE', 'springapex'); ?></p>
        <h2><?php echo esc_html($title); ?></h2>
      </div>
      <p><?php esc_html_e('Dimensions, materials and verification scope follow your drawing. The ranges below show what can be customized for each application.', 'springapex'); ?></p>
    </div>
    <div class="sa-evidence__custom">
      <figure class="sa-evidence__diagram" data-reveal="up">
        <?php echo springapex_image((string) ($verification['image'] ?? ''), (string) ($verification['image_alt'] ?? __('Spring dimension reference diagram', 'springapex')), [
            'width' => 960,
            'height' => 960,
            'sizes' => '(max-width: 960px) 100vw, 540px',
        ]); ?>
      </figure>
      <div class="sa-evidence__data" data-reveal="up">
        <div class="spec-table-wrap">
          <table class="spec-table">
            <tbody>
              <?php foreach ($specs as $row) : ?>
                <tr>
                  <th scope="row"><?php echo esc_html((string) $row['label']); ?></th>
                  <td><?php echo esc_html((string) $row['value']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <a class="btn btn-primary sa-evidence__upload" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
          <?php echo springapex_icon('upload', 'icon icon-sm'); ?>
          <?php esc_html_e('Upload a PDF Drawing', 'springapex'); ?>
        </a>
      </div>
    </div>
  </div>
</section>
