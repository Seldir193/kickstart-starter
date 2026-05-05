<?php

if (!defined('ABSPATH')) {
  exit;
}

$data = isset($args) && is_array($args) ? $args : [];

$plus_url = isset($data['plus_url']) ? (string) $data['plus_url'] : '';
$minus_url = isset($data['minus_url']) ? (string) $data['minus_url'] : '';
$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
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
    <div class="ks-title-wrap" data-bgword="JOBS" data-i18n="jobs.section.watermark" data-i18n-attr="data-bgword">
      <div class="ks-kicker" data-i18n="jobs.section.kicker">Karriere</div>

      <h2 class="ks-dir__title" data-i18n="jobs.section.title">
        Offene Positionen
      </h2>

      <p class="ks-text-center ks-mb-16 jobs-section__lead" data-i18n="jobs.section.lead">
        Wähle eine passende Position aus und erfahre mehr über Aufgaben, Anforderungen und Vorteile.
      </p>
    </div>

    <div class="ks-accs">
      <?php foreach ($items as $index => $item) :
        $title = isset($item['title']) ? (string) $item['title'] : '';
        $body = isset($item['body']) ? (string) $item['body'] : '';

        if ($title === '') {
          continue;
        }
      ?>
        <details class="ks-acc" <?php echo $index === 0 ? 'open' : ''; ?>>
          <summary><?php echo esc_html($title); ?></summary>

          <div class="ks-acc__body">
            <?php echo wp_kses_post($body); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>














