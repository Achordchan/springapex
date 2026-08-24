<?php
if (!defined('ABSPATH')) {
    exit;
}

$why_choose = springapex_get('about.why_choose', []);
if (!is_array($why_choose) || !$why_choose) {
    return;
}
$media_items = array_values(array_filter(
    (array) ($why_choose['media'] ?? []),
    static function (mixed $item): bool {
        if (!is_array($item)) {
            return false;
        }
        $image = $item['image'] ?? '';
        if (is_array($image)) {
            return (int) ($image['id'] ?? 0) > 0 || trim((string) ($image['file'] ?? '')) !== '';
        }
        return is_scalar($image) && trim((string) $image) !== '';
    }
));
$media_sizes = count($media_items) === 1
    ? '(max-width: 1080px) 100vw, 46vw'
    : '(max-width: 1080px) 50vw, 46vw';
?>
<section class="section sa-why-choose" aria-labelledby="sa-why-choose-title">
  <div class="container container-wide">
    <header class="sa-why-choose__header" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($why_choose['eyebrow'] ?? 'WHY CHOOSE US')); ?></p>
      <h2 id="sa-why-choose-title"><?php echo esc_html((string) ($why_choose['title'] ?? '')); ?></h2>
    </header>

    <div class="sa-why-choose__layout<?php echo $media_items ? '' : ' sa-why-choose__layout--without-media'; ?>">
      <?php if ($media_items) : ?>
      <div class="sa-why-choose__media<?php echo count($media_items) === 1 ? ' sa-why-choose__media--single' : ''; ?>" data-reveal-group>
        <?php foreach ($media_items as $media) : ?>
          <figure>
            <?php echo springapex_image((string) ($media['image'] ?? ''), (string) ($media['alt'] ?? ''), [
                'width' => 1586,
                'height' => 992,
                'sizes' => $media_sizes,
            ]); ?>
            <figcaption><?php echo esc_html((string) ($media['label'] ?? '')); ?></figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <ol class="sa-why-choose__list" data-reveal-group>
        <?php foreach (($why_choose['items'] ?? []) as $index => $item) : ?>
          <li>
            <span class="sa-why-choose__icon" aria-hidden="true">
              <?php echo springapex_image((string) ($item['icon_image'] ?? ''), '', [
                  'class' => 'sa-why-choose__icon-image',
                  'width' => 110,
                  'height' => 110,
              ]); ?>
            </span>
            <span class="sa-why-choose__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
            <span class="sa-why-choose__divider" aria-hidden="true"></span>
            <span class="sa-why-choose__copy">
              <strong><?php echo esc_html((string) ($item['title'] ?? '')); ?></strong>
              <span><?php echo esc_html((string) ($item['text'] ?? '')); ?></span>
            </span>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>

    <div class="sa-why-choose__outcomes" data-reveal="up">
      <h3><?php echo esc_html((string) ($why_choose['outcomes_title'] ?? '')); ?></h3>
      <div>
        <?php foreach (($why_choose['outcomes'] ?? []) as $outcome) : ?>
          <span>
            <span class="sa-why-choose__outcome-icon" aria-hidden="true"><?php echo springapex_icon((string) ($outcome['icon'] ?? 'check-shield')); ?></span>
            <strong><?php echo esc_html((string) ($outcome['text'] ?? '')); ?></strong>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
