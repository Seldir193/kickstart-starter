<?php

$news_latest_file = get_stylesheet_directory() . '/inc/news/news-latest.php';

if (file_exists($news_latest_file)) {
  require_once $news_latest_file;
}

if (!function_exists('ks_news_enqueue_file_style')) {
  function ks_news_enqueue_file_style(string $handle, string $path, array $deps): void {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $abs = $theme_dir . $path;

    if (!file_exists($abs) || wp_style_is($handle, 'enqueued')) {
      return;
    }

    wp_enqueue_style($handle, $theme_uri . $path, $deps, filemtime($abs));
  }
}

if (!function_exists('ks_news_enqueue_assets')) {
  function ks_news_enqueue_assets(): void {
    ks_news_enqueue_file_style('ks-utils', '/assets/css/ks-utils.css', ['kickstart-style']);
    ks_news_enqueue_file_style('ks-home', '/assets/css/ks-home.css', ['kickstart-style', 'ks-utils']);
    ks_news_enqueue_file_style('ks-dir', '/assets/css/ks-dir.css', ['ks-home']);

    if (wp_style_is('ks-news', 'registered') || wp_style_is('ks-news', 'enqueued')) {
      wp_enqueue_style('ks-news');
      return;
    }

    ks_news_enqueue_file_style('ks-news', '/assets/css/ks-news.css', [
  'kickstart-style',
  'ks-utils',
  'ks-home',
  'ks-dir',
  'ks-page-hero',
]);
  }
}

if (!function_exists('ks_news_api_base')) {
  function ks_news_api_base(): string {
    $api_base = get_option('ks_news_api', '');

    if (is_string($api_base) && $api_base) {
      return rtrim($api_base, '/');
    }

    return 'http://localhost:5000/api/news';
  }
}

if (!function_exists('ks_news_fetch')) {
  function ks_news_fetch(string $url) {
    $response = wp_remote_get($url, ['timeout' => 10]);

    if (is_wp_error($response)) {
      return null;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $status_code >= 200 && $status_code < 300 ? ($data ?: null) : null;
  }
}

if (!function_exists('ks_news_format_date')) {
  function ks_news_format_date($iso): string {
    $timestamp = strtotime($iso ?: '');

    if (!$timestamp) {
      return '';
    }

    return date_i18n(get_option('date_format'), $timestamp);
  }
}

if (!function_exists('ks_news_detail_url')) {
  function ks_news_detail_url(string $slug): string {
    $encoded_slug = urlencode($slug);
    $detail_page = get_page_by_path('news-detail');
    $fallback = home_url('/index.php/news-detail/');
    $detail_base = $detail_page ? get_permalink($detail_page->ID) : $fallback;

    return esc_url(add_query_arg('slug', $encoded_slug, $detail_base));
  }
}

if (!function_exists('ks_news_archive_url')) {
  function ks_news_archive_url(): string {
    $new_page = get_page_by_path('new');

    if ($new_page) {
      return get_permalink($new_page->ID);
    }

    $news_page = get_page_by_path('news');
    return $news_page ? get_permalink($news_page->ID) : home_url('/index.php/new/');
  }
}

if (!function_exists('ks_news_sidebar_url')) {
  function ks_news_sidebar_url(array $args): string {
    return esc_url(add_query_arg($args, ks_news_archive_url()));
  }
}

if (!function_exists('ks_news_get_items')) {
  function ks_news_get_items(array $data): array {
    if (!$data || empty($data['items']) || !is_array($data['items'])) {
      return [];
    }

    return $data['items'];
  }
}

if (!function_exists('ks_news_get_adjacent_index')) {
  function ks_news_get_adjacent_index(array $items, string $current_slug): int {
    foreach ($items as $index => $item) {
      if (!empty($item['slug']) && (string)$item['slug'] === $current_slug) {
        return $index;
      }
    }

    return -1;
  }
}

if (!function_exists('ks_news_get_more_items')) {
  function ks_news_get_more_items(array $items, string $current_slug): array {
    $more_items = [];

    foreach ($items as $item) {
      $slug = (string)($item['slug'] ?? '');
      if (!$slug || $slug === $current_slug) {
        continue;
      }

      $more_items[] = $item;
      if (count($more_items) >= 3) {
        break;
      }
    }

    return $more_items;
  }
}

if (!function_exists('ks_news_get_adjacent_and_more')) {
  function ks_news_get_adjacent_and_more(string $api, string $current_slug, int $pool_limit = 200): array {
    $data = ks_news_fetch(add_query_arg([
      'limit' => $pool_limit,
      'page' => 1,
      'published' => 'true',
    ], $api));

    $items = ks_news_get_items($data);
    $index = ks_news_get_adjacent_index($items, $current_slug);

    return [
      'prev' => $index >= 0 && $index < count($items) - 1 ? ($items[$index + 1] ?? null) : null,
      'next' => $index > 0 ? ($items[$index - 1] ?? null) : null,
      'more' => ks_news_get_more_items($items, $current_slug),
    ];
  }
}

if (!function_exists('ks_news_latest_sidebar_items')) {
  function ks_news_latest_sidebar_items(string $api): string {
    $data = ks_news_fetch(add_query_arg(['limit' => 4, 'page' => 1, 'published' => 'true'], $api));
    $items = ks_news_get_items($data);

    if (!$items) {
      return '<li data-i18n="news.emptyList">Keine Einträge.</li>';
    }

    return implode('', array_map('ks_news_latest_sidebar_item', $items));
  }
}

if (!function_exists('ks_news_latest_sidebar_item')) {
  function ks_news_latest_sidebar_item(array $item): string {
    $title = esc_html($item['title'] ?? '');
    $date = esc_html(ks_news_format_date($item['date'] ?? null));
    $category = esc_html($item['category'] ?? '');
    $detail = ks_news_detail_url($item['slug'] ?? '');
    $meta = ($category ? $category . ' · ' : '') . $date;

    return '<li class="ks-latest-item"><div class="ks-latest-meta">' . $meta .
      '</div><a class="ks-latest-link" href="' . $detail . '">' . $title . '</a></li>';
  }
}

if (!function_exists('ks_news_sidebar_categories')) {
  function ks_news_sidebar_categories(string $active_category): string {
    $categories = ['Allgemein', 'News', 'Partnerverein', 'Projekte'];
    $html = '';

    foreach ($categories as $category) {
      $url = ks_news_sidebar_url(['category' => $category, 'pg' => 1, 'tag' => null]);
      $class = $active_category === $category ? ' class="current"' : '';
      $html .= '<li><a' . $class . ' href="' . $url . '">' . esc_html($category) . '</a></li>';
    }

    return $html;
  }
}

if (!function_exists('ks_news_tag_link')) {
  function ks_news_tag_link(array $tag, string $active_tag): string {
    $raw_name = $tag['name'] ?? '';

    if (!$raw_name) {
      return '';
    }

    $url = ks_news_sidebar_url(['tag' => $raw_name, 'pg' => 1, 'category' => null]);
    $class = $active_tag === $raw_name ? ' class="current"' : '';

    return '<a' . $class . ' href="' . $url . '">' . esc_html($raw_name) . '</a>';
  }
}

if (!function_exists('ks_news_sidebar_extra_tags')) {
  function ks_news_sidebar_extra_tags(array $tags, string $active_tag): string {
    if (!$tags) {
      return '';
    }

    $links = implode('', array_map(fn($tag) => ks_news_tag_link($tag, $active_tag), $tags));

    return '<details class="ks-tag-more"><summary class="ks-tag-more__btn">' .
      '<span class="ks-tag-more__closed" data-i18n="news.showAllTags">ALLE SCHLAGWÖRTER ANZEIGEN</span>' .
      '<span class="ks-tag-more__open" data-i18n="news.showLess">WENIGER ANZEIGEN</span>' .
      '</summary>' . $links . '</details>';
  }
}

if (!function_exists('ks_news_sidebar_tags')) {
  function ks_news_sidebar_tags(string $api, string $active_tag): string {
    $taxonomy = ks_news_fetch(trailingslashit($api) . 'taxonomy');
    $tags = $taxonomy && !empty($taxonomy['tags']) && is_array($taxonomy['tags']) ? $taxonomy['tags'] : [];

    if (!$tags) {
      return '<span data-i18n="news.emptyTags">Keine Schlagwörter.</span>';
    }

    $top_tags = array_slice($tags, 0, 12);
    $extra_tags = array_slice($tags, 12);
    $top_links = implode('', array_map(fn($tag) => ks_news_tag_link($tag, $active_tag), $top_tags));

    return $top_links . ks_news_sidebar_extra_tags($extra_tags, $active_tag);
  }
}

if (!function_exists('ks_news_sidebar_ad')) {
  function ks_news_sidebar_ad(): string {
    $image = esc_url(get_stylesheet_directory_uri() . '/assets/img/ads/mfs.png');

    return '<section class="widget ks-widget-ad"><h3 class="widget-title" data-i18n="news.adTitle">WERBUNG</h3>' .
      '<a class="ad" href="https://selcuk-kocyigit.de" target="_blank" rel="noopener">' .
      '<img src="' . $image . '" alt="Werbung" loading="lazy" width="320" height="250"></a></section>';
  }
}

if (!function_exists('ks_news_render_sidebar')) {
  function ks_news_render_sidebar(string $api, string $active_category = '', string $active_tag = ''): string {
    return '<aside class="ks-news-sidebar">' .
      '<section class="widget"><h3 class="widget-title" data-i18n="news.latestPosts">LETZTE BEITRÄGE</h3><ul class="widget-list">' .
      ks_news_latest_sidebar_items($api) . '</ul></section>' .
      '<section class="widget"><h3 class="widget-title" data-i18n="news.categories">KATEGORIEN</h3><ul class="widget-list">' .
      ks_news_sidebar_categories($active_category) . '</ul></section>' .
      '<section class="widget"><h3 class="widget-title" data-i18n="news.tags">SCHLAGWÖRTER</h3><div class="tagcloud">' .
      ks_news_sidebar_tags($api, $active_tag) . '</div></section>' .
      ks_news_sidebar_ad() . '</aside>';
  }
}

// if (!function_exists('ks_news_hero_shortcode')) {
//   function ks_news_hero_shortcode($atts = []): string {
//     ks_news_enqueue_assets();

//     $atts = shortcode_atts([
//       'img' => get_stylesheet_directory_uri() . '/assets/img/news/mfs.png',
//       'title' => 'News',
//       'watermark' => 'NEWS',
//     ], $atts, 'ks_news_hero');

//     return ks_news_hero_markup(esc_url($atts['img']), esc_html($atts['title']), esc_attr($atts['watermark']));
//   }
// }

if (!function_exists('ks_news_hero_shortcode')) {
  function ks_news_hero_shortcode($atts = []): string {
    ks_news_enqueue_assets();

    $data = shortcode_atts([
      'title' => 'News',
      'subtitle' => 'Aktuelles aus der Dortmunder Fussball Schule',
      'watermark' => 'NEWS',
    ], $atts, 'ks_news_hero');

    return do_shortcode(ks_news_hero_shortcode_string($data));
  }
}

if (!function_exists('ks_news_hero_shortcode_string')) {
  function ks_news_hero_shortcode_string(array $data): string {
    $image = esc_url(get_stylesheet_directory_uri() . '/assets/img/hero/mfs.png');

    return '[ks_hero_page title="' . esc_attr($data['title']) .
      '" subtitle="' . esc_attr($data['subtitle']) .
      '" breadcrumb="Home" image="' . $image .
      '" variant="news" features="0" eyebrow="Aktuelles" primary_label="Beiträge lesen" primary_href="#news-archive" secondary_label="Newsletter" secondary_href="#ksNewsletter" title_i18n="news.hero.title" subtitle_i18n="news.hero.subtitle" eyebrow_i18n="news.hero.eyebrow" primary_i18n="news.hero.actions.read" secondary_i18n="news.hero.actions.newsletter"]';
  }
}

if (!function_exists('ks_news_archive_args')) {
  function ks_news_archive_args(int $limit, int $page, string $category, string $tag): array {
    $args = ['limit' => $limit, 'page' => $page, 'published' => 'true'];

    if ($category) {
      $args['category'] = $category;
    }

    if ($tag) {
      $args['tag'] = $tag;
    }

    return $args;
  }
}

if (!function_exists('ks_news_item_excerpt')) {
  function ks_news_item_excerpt(array $item, int $words): string {
    $excerpt = $item['excerpt'] ?? '';

    if (!$excerpt && !empty($item['content'])) {
      $excerpt = wp_trim_words(wp_strip_all_tags($item['content']), $words);
    }

    return esc_html($excerpt);
  }
}

if (!function_exists('ks_news_meta_markup')) {
  function ks_news_meta_markup(string $category, string $date): string {
    return '<div class="ks-news-item__meta">' .
      ($category ? esc_html($category) . ' · ' : '') .
      esc_html($date) . '</div>';
  }
}

if (!function_exists('ks_news_read_more_markup')) {
  function ks_news_read_more_markup(string $detail): string {
    return '<p class="ks-news-item__more"><a class="ks-link-more" href="' . esc_url($detail) . '">' .
      '<span class="ks-link-more__text" data-i18n="news.readMore">MEHR LESEN</span></a></p>';
  }
}

if (!function_exists('ks_news_archive_card')) {
  function ks_news_archive_card(array $item): string {
    $title = esc_html($item['title'] ?? '');
    $date = ks_news_format_date($item['date'] ?? null);
    $category = (string)($item['category'] ?? '');
    $excerpt = ks_news_item_excerpt($item, 48);
    $detail = ks_news_detail_url($item['slug'] ?? '');

    return '<article class="ks-news-item">' . ks_news_meta_markup($category, $date) .
      '<h2 class="ks-news-item__title"><a href="' . $detail . '">' . $title . '</a></h2>' .
      '<div class="ks-news-item__excerpt">' . $excerpt . '</div>' .
      ks_news_read_more_markup($detail) . '</article>';
  }
}

if (!function_exists('ks_news_archive_cards')) {
  function ks_news_archive_cards(array $items): string {
    if (!$items) {
      return '<p data-i18n="news.empty">Keine Beiträge gefunden.</p>';
    }

    return implode('', array_map('ks_news_archive_card', $items));
  }
}

if (!function_exists('ks_news_pagination_icons')) {
  function ks_news_pagination_icons(): array {
    $theme_uri = get_stylesheet_directory_uri();

    return [
      'left' => $theme_uri . '/assets/img/home/left.png',
      'right' => $theme_uri . '/assets/img/home/right.png',
    ];
  }
}

if (!function_exists('ks_news_pagination_markup')) {
  function ks_news_pagination_markup(array $data, int $page, string $category, string $tag): string {
    $pages = max(1, intval($data['pages'] ?? 1));

    if ($pages <= 1) {
      return '';
    }

    $icons = ks_news_pagination_icons();
    return '<nav class="ks-pagination" aria-label="News Pagination">' . paginate_links([
      'base' => esc_url(add_query_arg('pg', '%#%', get_permalink())),
      'format' => '',
      'total' => $pages,
      'current' => $page,
      'mid_size' => 2,
      'prev_text' => '<img class="ks-page-ic" src="' . esc_url($icons['left']) . '" alt="Zurück" />',
      'next_text' => '<img class="ks-page-ic" src="' . esc_url($icons['right']) . '" alt="Weiter" />',
      'add_args' => array_filter(['category' => $category, 'tag' => $tag]),
    ]) . '</nav>';
  }
}

if (!function_exists('ks_news_archive_shortcode')) {
  function ks_news_archive_shortcode($atts = []): string {
    ks_news_enqueue_assets();

    $atts = shortcode_atts(['per_page' => 5], $atts, 'ks_news_archive');
    $page = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
    $limit = min(5, max(1, (int)$atts['per_page']));
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $tag = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
    $api = ks_news_api_base();
    $data = ks_news_fetch(add_query_arg(ks_news_archive_args($limit, $page, $category, $tag), $api));

    return ks_news_archive_markup($api, $data ?: [], $page, $category, $tag);
  }
}

if (!function_exists('ks_news_archive_markup')) {
  function ks_news_archive_markup(string $api, array $data, int $page, string $category, string $tag): string {
    $items = ks_news_get_items($data);

    return '<section id="news-archive" class="ks-news-archive ks-sec ks-py-48"><div class="container">' .
      '<div class="ks-grid-12-8"><main class="ks-news-main">' .
      ks_news_archive_cards($items) .
      ks_news_pagination_markup($data, $page, $category, $tag) .
      '</main>' . ks_news_render_sidebar($api, $category, $tag) .
      '</div></div></section>';
  }
}

if (!function_exists('ks_news_has_html')) {
  function ks_news_has_html(string $raw): bool {
    return (bool)preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $raw);
  }
}

if (!function_exists('ks_news_md_inline')) {
  function ks_news_md_inline(string $text): string {
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

    return preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($match) {
      return '<a href="' . esc_url($match[2] ?? '') . '" target="_blank" rel="noopener">' . ($match[1] ?? '') . '</a>';
    }, $text);
  }
}

if (!function_exists('ks_news_md_close_lists')) {
  function ks_news_md_close_lists(array &$state): string {
    $html = '';

    foreach (['quote' => 'blockquote', 'ul' => 'ul', 'ol' => 'ol'] as $key => $tag) {
      if ($state[$key]) {
        $html .= "</{$tag}>\n";
        $state[$key] = false;
      }
    }

    return $html;
  }
}

if (!function_exists('ks_news_md_heading')) {
  function ks_news_md_heading(string $line, array &$state): string {
    if (!preg_match('/^(#{2,4})\s+(.*)$/', $line, $match)) {
      return '';
    }

    $level = min(4, max(2, strlen($match[1])));
    $text = ks_news_md_inline(esc_html($match[2]));

    return ks_news_md_close_lists($state) . "<h{$level}>{$text}</h{$level}>";
  }
}

if (!function_exists('ks_news_md_quote')) {
  function ks_news_md_quote(string $line, array &$state): string {
    if (!preg_match('/^\>\s?(.*)$/', $line, $match)) {
      return '';
    }

    $text = ks_news_md_inline(esc_html($match[1]));

    if (!$state['quote']) {
      $state['quote'] = true;
      return ks_news_md_close_lists($state) . "<blockquote><p>{$text}</p>";
    }

    return "<p>{$text}</p>";
  }
}

if (!function_exists('ks_news_md_ordered_item')) {
  function ks_news_md_ordered_item(string $line, array &$state): string {
    if (!preg_match('/^\d+\.\s+(.*)$/', $line, $match)) {
      return '';
    }

    $text = ks_news_md_inline(esc_html($match[1]));

    if (!$state['ol']) {
      $state['ol'] = true;
      return ks_news_md_close_lists($state) . "<ol><li>{$text}</li>";
    }

    return "<li>{$text}</li>";
  }
}

if (!function_exists('ks_news_md_unordered_item')) {
  function ks_news_md_unordered_item(string $line, array &$state): string {
    if (!preg_match('/^[-*]\s+(.*)$/', $line, $match)) {
      return '';
    }

    $text = ks_news_md_inline(esc_html($match[1]));

    if (!$state['ul']) {
      $state['ul'] = true;
      return ks_news_md_close_lists($state) . "<ul><li>{$text}</li>";
    }

    return "<li>{$text}</li>";
  }
}

if (!function_exists('ks_news_md_line_to_html')) {
  function ks_news_md_line_to_html(string $line, array &$state): string {
    $trimmed_line = rtrim($line);

    if ($trimmed_line === '') {
      return ks_news_md_close_lists($state);
    }

    foreach (['heading', 'quote', 'ordered_item', 'unordered_item'] as $type) {
      $html = call_user_func("ks_news_md_{$type}", $trimmed_line, $state);
      if ($html) {
        return $html;
      }
    }

    return ks_news_md_close_lists($state) . '<p>' . ks_news_md_inline(esc_html($trimmed_line)) . '</p>';
  }
}

if (!function_exists('ks_news_md_to_html')) {
  function ks_news_md_to_html(string $raw): string {
    $lines = preg_split("/\r\n|\n|\r/", $raw);
    $state = ['ul' => false, 'ol' => false, 'quote' => false];
    $html = [];

    foreach ($lines as $line) {
      $html[] = ks_news_md_line_to_html($line, $state);
    }

    $html[] = ks_news_md_close_lists($state);
    return implode("\n", array_filter($html));
  }
}

if (!function_exists('ks_news_render_article_content')) {
  function ks_news_render_article_content(string $raw): string {
    if ($raw === '') {
      return '';
    }

    if (ks_news_has_html($raw)) {
      return wp_kses_post($raw);
    }

    return wp_kses_post(ks_news_md_to_html($raw));
  }
}

if (!function_exists('ks_news_single_images')) {
  function ks_news_single_images(array $item): array {
    $images = [];

    if (!empty($item['coverImage'])) {
      $images[] = esc_url($item['coverImage']);
    }

    foreach (($item['media'] ?? []) as $media) {
      if (($media['type'] ?? '') === 'image' && !empty($media['url'])) {
        $images[] = esc_url($media['url']);
      }
    }

    return array_values(array_unique(array_filter($images)));
  }
}

if (!function_exists('ks_news_single_videos')) {
  function ks_news_single_videos(array $item): array {
    $videos = [];

    foreach (($item['media'] ?? []) as $media) {
      if (($media['type'] ?? '') === 'video' && !empty($media['url'])) {
        $videos[] = esc_url($media['url']);
      }
    }

    return array_values(array_unique(array_filter($videos)));
  }
}

if (!function_exists('ks_news_single_lead')) {
  function ks_news_single_lead(array $item, string $content_raw): string {
    $lead = (string)($item['excerpt'] ?? '');

    if (!$lead && $content_raw) {
      $lead = wp_trim_words(wp_strip_all_tags($content_raw), 28);
    }

    return esc_html($lead);
  }
}

if (!function_exists('ks_news_single_image_markup')) {
  function ks_news_single_image_markup(array $images, string $title): string {
    if (!$images) {
      return '';
    }

    if (count($images) === 1) {
      return '<figure class="ks-news-single__hero-media"><img src="' . $images[0] . '" alt="' . $title . '" loading="lazy"></figure>';
    }

    return '<div class="ks-news-single__gallery" aria-label="Bilder">' .
      implode('', array_map(fn($src) => '<figure class="ks-news-single__img"><img src="' . $src . '" alt="' . $title . '" loading="lazy"></figure>', array_slice($images, 0, 6))) .
      '</div>';
  }
}

if (!function_exists('ks_news_single_video_markup')) {
  function ks_news_single_video_markup(array $videos): string {
    if (!$videos) {
      return '';
    }

    $items = implode('', array_map(fn($video) => '<div class="ks-news-single__video"><video controls preload="metadata" src="' . $video . '"></video></div>', $videos));

    return '<section class="ks-news-single__videos" aria-label="Videos">' . $items . '</section>';
  }
}

if (!function_exists('ks_news_nav_button')) {
  function ks_news_nav_button($item, string $icon, string $direction, string $label): string {
    if (!$item || empty($item['slug'])) {
      return '<span class="ks-news-nav-btn ks-news-nav-btn--disabled" aria-hidden="true"><img src="' . esc_url($icon) . '" alt="" /></span>';
    }

    return '<a class="ks-news-nav-btn ks-news-nav-btn--' . esc_attr($direction) . '" href="' .
      ks_news_detail_url($item['slug']) . '"><img src="' . esc_url($icon) . '" alt="' . esc_attr($label) . '" /></a>';
  }
}

if (!function_exists('ks_news_more_card')) {
  function ks_news_more_card(array $item): string {
    $title = esc_html($item['title'] ?? '');
    $date = ks_news_format_date($item['date'] ?? null);
    $category = (string)($item['category'] ?? '');
    $excerpt = ks_news_item_excerpt($item, 18);
    $detail = ks_news_detail_url($item['slug'] ?? '');

    return '<article class="ks-news-more-card">' . ks_news_meta_markup($category, $date) .
      '<h3 class="ks-news-item__title"><a href="' . $detail . '">' . $title . '</a></h3>' .
      '<div class="ks-news-item__excerpt">' . $excerpt . '</div>' .
      ks_news_read_more_markup($detail) . '</article>';
  }
}

if (!function_exists('ks_news_more_grid')) {
  function ks_news_more_grid(array $items): string {
    if (!$items) {
      return '<p data-i18n="news.emptyMore">Keine weiteren Beiträge.</p>';
    }

    return '<div class="ks-news-more-grid">' . implode('', array_map('ks_news_more_card', $items)) . '</div>';
  }
}

if (!function_exists('ks_news_more_section')) {
  function ks_news_more_section(array $adjacent): string {
    $icons = ks_news_pagination_icons();

    return '<section class="ks-news-more-wrap ks-sec ks-py-32"><div class="ks-news-more-head">' .
      '<h2 class="ks-news-more-title" data-i18n="news.otherPosts">ANDERE BEITRÄGE</h2>' .
      '<div class="ks-news-more-nav" aria-label="Artikel Navigation">' .
      ks_news_nav_button($adjacent['prev'] ?? null, $icons['left'], 'prev', 'Zurück') .
      ks_news_nav_button($adjacent['next'] ?? null, $icons['right'], 'next', 'Weiter') .
      '</div></div>' . ks_news_more_grid($adjacent['more'] ?? []) . '</section>';
  }
}

if (!function_exists('ks_news_single_article')) {
  function ks_news_single_article(array $item): string {
    $title = esc_html($item['title'] ?? '');
    $date = ks_news_format_date($item['date'] ?? null);
    $category = (string)($item['category'] ?? '');
    $content_raw = (string)($item['content'] ?? '');
    $lead = ks_news_single_lead($item, $content_raw);

    return '<article class="ks-news-article">' . ks_news_meta_markup($category, $date) .
      '<h1 class="ks-news-single__title">' . $title . '</h1>' .
      ($lead ? '<p class="ks-news-single__lead"><strong>' . $lead . '</strong></p>' : '') .
      ks_news_single_image_markup(ks_news_single_images($item), $title) .
      '<div class="ks-news-single__content">' . ks_news_render_article_content($content_raw) . '</div>' .
      ks_news_single_video_markup(ks_news_single_videos($item)) . '</article>';
  }
}

if (!function_exists('ks_news_single_shortcode')) {
  function ks_news_single_shortcode(): string {
    ks_news_enqueue_assets();

    $slug = isset($_GET['slug']) ? sanitize_title($_GET['slug']) : '';
    if (!$slug) {
      return '<p data-i18n="news.noPostSelected">Kein Beitrag ausgewählt.</p>';
    }

    $api = ks_news_api_base();
    $data = ks_news_fetch(trailingslashit($api) . rawurlencode($slug));

    if (!$data || empty($data['item'])) {
      return '<p data-i18n="news.postNotFound">Beitrag nicht gefunden.</p>';
    }

    return ks_news_single_markup($api, $slug, $data['item']);
  }
}

if (!function_exists('ks_news_single_markup')) {
  function ks_news_single_markup(string $api, string $slug, array $item): string {
    $active_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : (string)($item['category'] ?? '');
    $active_tag = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
    $adjacent = ks_news_get_adjacent_and_more($api, $slug, 200);

    return '<section id="news-detail" class="ks-news-single ks-sec ks-py-48"><div class="container">' .
      '<div class="ks-grid-12-8"><main class="ks-news-main">' .
      ks_news_single_article($item) . ks_news_more_section($adjacent) .
      '</main>' . ks_news_render_sidebar($api, $active_category, $active_tag) .
     
      '</div><div id="ks-newsletter">' . do_shortcode('[ks_newsletter]') . '</div></div></section>';
  }
}

if (!function_exists('ks_register_news_shortcodes')) {
  function ks_register_news_shortcodes(): void {
    add_shortcode('ks_news_hero', 'ks_news_hero_shortcode');

    if (function_exists('ks_news_latest_shortcode')) {
      add_shortcode('ks_news_latest', 'ks_news_latest_shortcode');
    }

    add_shortcode('ks_news_archive', 'ks_news_archive_shortcode');
    add_shortcode('ks_news_single', 'ks_news_single_shortcode');
  }
}

add_action('init', 'ks_register_news_shortcodes');

