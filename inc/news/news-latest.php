<?php

if (!function_exists('ks_news_latest_excerpt')) {
  function ks_news_latest_excerpt(array $news_item): string {
    $excerpt = $news_item['excerpt'] ?? '';

    if (!$excerpt && !empty($news_item['content'])) {
      $excerpt = wp_trim_words(wp_strip_all_tags($news_item['content']), 22);
    }

    return esc_html($excerpt);
  }
}

if (!function_exists('ks_news_latest_cover')) {
  function ks_news_latest_cover(array $news_item): string {
    if (empty($news_item['coverImage'])) {
      return '';
    }

    return esc_url($news_item['coverImage']);
  }
}

if (!function_exists('ks_news_latest_date_iso')) {
  function ks_news_latest_date_iso(string $raw_date): string {
    $timestamp = strtotime($raw_date);

    if (!$timestamp) {
      return '';
    }

    return date('Y-m-d', $timestamp);
  }
}

if (!function_exists('ks_news_latest_date')) {
  function ks_news_latest_date(string $date, string $raw_date): string {
    $date_iso = ks_news_latest_date_iso($raw_date);

    if (!$date || !$date_iso) {
      return '';
    }

    return '<time class="ks-news-item__date" datetime="' . esc_attr($date_iso) .
      '" data-i18n-date="' . esc_attr($date_iso) . '">' . esc_html($date) . '</time>';
  }
}

if (!function_exists('ks_news_latest_category_i18n_key')) {
  function ks_news_latest_category_i18n_key(string $category): string {
    $map = [
      'Allgemein' => 'news.category.general',
      'News' => 'news.category.news',
      'Partnerverein' => 'news.category.partnerClub',
      'Projekte' => 'news.category.projects',
    ];

    return $map[$category] ?? '';
  }
}
if (!function_exists('ks_news_latest_meta')) {
  function ks_news_latest_meta(string $category, string $date, string $raw_date): string {
    $category_key = ks_news_latest_category_i18n_key($category);

    ob_start(); ?>
      <div class="ks-news-item__meta">
        <?php if ($category): ?>
          <span
            class="ks-news-item__category"
            <?php echo $category_key ? 'data-i18n="' . esc_attr($category_key) . '"' : ''; ?>
          >
            <?= esc_html($category) ?>
          </span>
          <span class="ks-news-item__separator" aria-hidden="true">·</span>
        <?php endif; ?>

        <?= ks_news_latest_date($date, $raw_date) ?>
      </div>
    <?php
    return ob_get_clean();
  }
}

if (!function_exists('ks_news_latest_thumb')) {
  function ks_news_latest_thumb(string $cover, string $title, string $detail): string {
    if (!$cover) {
      return '';
    }

    ob_start(); ?>
      <a class="ks-news-item__thumb" href="<?= esc_url($detail) ?>">
        <img src="<?= esc_url($cover) ?>" alt="<?= esc_attr($title) ?>" loading="lazy">
      </a>
    <?php
    return ob_get_clean();
  }
}

if (!function_exists('ks_news_latest_more_link')) {
  function ks_news_latest_more_link(string $detail): string {
    ob_start(); ?>
      <p class="ks-news-item__more">
        <a class="ks-link-more" href="<?= esc_url($detail) ?>">
          <span class="ks-link-more__text" data-i18n="news.readMore">MEHR LESEN</span>
        </a>
      </p>
    <?php
    return ob_get_clean();
  }
}

if (!function_exists('ks_news_latest_card')) {
  function ks_news_latest_card(array $news_item, bool $thumbs): string {
    $title = esc_html($news_item['title'] ?? '');
    $raw_date = (string)($news_item['date'] ?? '');
    $date = ks_news_format_date($raw_date);
    $category = (string)($news_item['category'] ?? '');
    $detail = ks_news_detail_url($news_item['slug'] ?? '');
    $excerpt = ks_news_latest_excerpt($news_item);
    $cover = $thumbs ? ks_news_latest_cover($news_item) : '';

    ob_start(); ?>
      <article class="ks-news-item">
        <?= ks_news_latest_meta($category, $date, $raw_date) ?>
        <?= ks_news_latest_thumb($cover, $title, $detail) ?>

        <h3 class="ks-news-item__title">
          <a href="<?= esc_url($detail) ?>"><?= $title ?></a>
        </h3>

        <div class="ks-news-item__excerpt"><?= $excerpt ?></div>
        <?= ks_news_latest_more_link($detail) ?>
      </article>
    <?php
    return ob_get_clean();
  }
}

if (!function_exists('ks_news_latest_shortcode')) {
  function ks_news_latest_shortcode($atts = []): string {
    ks_news_enqueue_assets();

    $atts = shortcode_atts(['limit' => 3, 'thumbs' => 0], $atts, 'ks_news_latest');
    $limit = max(1, min(12, (int)$atts['limit']));
    $thumbs = ((string)$atts['thumbs'] === '1');

    $data = ks_news_fetch(add_query_arg([
      'limit' => $limit,
      'page' => 1,
      'published' => 'true',
    ], ks_news_api_base()));

    if (!$data || empty($data['items'])) {
      return '<p data-i18n="news.empty">Keine Beiträge gefunden.</p>';
    }

    ob_start(); ?>
      <section class="ks-news-latest ks-sec ks-py-56">
        <div class="container">
          <div class="ks-grid-3">
            <?php foreach ($data['items'] as $news_item): ?>
              <?= ks_news_latest_card($news_item, $thumbs) ?>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php
    return ob_get_clean();
  }
}