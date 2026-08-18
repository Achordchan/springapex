<?php
if (!defined('ABSPATH')) {
    exit;
}

$guide_args = is_array($args ?? null) ? $args : [];
$visible = !empty($guide_args['visible']);
$guides = array_slice((array) springapex_get('resources.items', []), 0, 3);
if (!$guides) {
    return;
}
?>
<div
  class="sa-drawing-guidance<?php echo $visible ? '' : ' is-collapsed'; ?>"
  data-drawing-guidance
  <?php echo $visible ? '' : 'hidden'; ?>
>
  <button
    class="sa-drawing-guidance__trigger"
    type="button"
    data-drawing-guide-open
    aria-haspopup="dialog"
    aria-expanded="false"
    aria-controls="springapex-drawing-guide"
  >
    <span class="sa-drawing-guidance__trigger-icon"><?php echo springapex_icon('form', 'icon'); ?></span>
    <span class="sa-drawing-guidance__trigger-copy">
      <small><?php esc_html_e('DRAWING GUIDE', 'springapex'); ?></small>
      <strong><?php esc_html_e('Prepare your upload', 'springapex'); ?></strong>
    </span>
    <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
  </button>

  <dialog
    class="sa-drawing-guide-dialog"
    id="springapex-drawing-guide"
    data-drawing-guide-dialog
    aria-labelledby="drawing-guide-dialog-title"
  >
    <div class="sa-drawing-guide-dialog__shell">
      <header class="sa-drawing-guide-dialog__header">
        <div>
          <p class="section-kicker"><?php esc_html_e('BEFORE YOU UPLOAD', 'springapex'); ?></p>
          <h3 id="drawing-guide-dialog-title"><?php esc_html_e('Prepare your upload', 'springapex'); ?></h3>
        </div>
        <button class="sa-drawing-guide-dialog__close" type="button" data-drawing-guide-close aria-label="<?php esc_attr_e('Close drawing preparation guide', 'springapex'); ?>">
          <?php echo springapex_icon('close', 'icon'); ?>
        </button>
      </header>

      <div class="sa-drawing-guide-dialog__body">
        <p class="sa-drawing-guide-dialog__intro"><?php esc_html_e('Three checks that help engineering review your drawing faster.', 'springapex'); ?></p>

        <div class="sa-drawing-guidance__list">
          <?php foreach ($guides as $guide) : ?>
            <details>
              <summary>
                <span>
                  <small><?php echo esc_html((string) ($guide['type'] ?? 'Guide')); ?></small>
                  <strong><?php echo esc_html((string) ($guide['title'] ?? '')); ?></strong>
                </span>
                <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
              </summary>
              <p><?php echo esc_html((string) ($guide['summary'] ?? '')); ?></p>
              <?php if (!empty($guide['points']) && is_array($guide['points'])) : ?>
                <ul>
                  <?php foreach ($guide['points'] as $point) : ?>
                    <li><?php echo esc_html((string) $point); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </details>
          <?php endforeach; ?>
        </div>

        <a class="text-link sa-drawing-guidance__link" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
          <?php esc_html_e('Open all engineering resources', 'springapex'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </div>
    </div>
  </dialog>
</div>
