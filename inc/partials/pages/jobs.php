<?php
/**
 * Template Part: Jobs Content
 * Pfad: inc/partials/pages/jobs.php
 */

$data = isset($args) && is_array($args) ? $args : [];

$title     = isset($data['title']) ? (string) $data['title'] : 'Aktuelle Jobangebote';
$subtitle  = isset($data['subtitle']) ? (string) $data['subtitle'] : '';
$bgword    = isset($data['bgword']) ? (string) $data['bgword'] : 'JOBS';
$plus_url  = isset($data['plus_url']) ? (string) $data['plus_url'] : '';
$minus_url = isset($data['minus_url']) ? (string) $data['minus_url'] : '';
$items     = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
?>

<section
  id="jobs"
  class="ks-sec ks-py-56"
  style="
    --acc-plus:url('<?php echo esc_url($plus_url); ?>');
    --acc-minus:url('<?php echo esc_url($minus_url); ?>');
  "
>
  <div class="container container--1100">

    <!-- Title + Watermark (global wie ks-utils) -->
    <div class="ks-title-wrap" data-bgword="<?php echo esc_attr($bgword); ?>">
      <div class="ks-kicker">KARRIERE</div>
      <h2 class="ks-dir__title"><?php echo esc_html($title); ?></h2>

      <?php if (!empty($subtitle)) : ?>
        <p class="ks-text-center ks-mb-16" style="max-width:820px;margin-left:auto;margin-right:auto;">
          <?php echo esc_html($subtitle); ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Accordion: Details/summary mit globalem ks-utils Look -->
    <div class="ks-acc">
      <?php foreach ($items as $i => $item) :
        $t = isset($item['title']) ? (string) $item['title'] : '';
        $b = isset($item['body']) ? (string) $item['body'] : '';
        if (!$t) continue;
      ?>
        <details class="ks-acc" <?php echo $i === 0 ? 'open' : ''; ?>>
          <summary><?php echo esc_html($t); ?></summary>
          <div class="ks-acc__body">
            <?php echo wp_kses_post($b); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>

  </div>
</section>








