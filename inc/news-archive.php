<?php
/**
 * News-Shortcodes: Hero, Latest, Archive (API), Single (API)
 *
 * Verwendung:
 *   Seite "News" (Slug z.B. new):
 *     [ks_news_hero]
 *     [ks_news_archive]
 *
 *   Home (z.B. in ks_home.php / Shortcode Output):
 *     <?php echo do_shortcode('[ks_news_latest limit="3" thumbs="0"]'); ?>
 *
 *   Seite "News Detail" (Slug exakt news-detail):
 *     [ks_news_single]
 */

/* =========================================================
   ASSETS (ks-utils + ks-home + ks-dir + ks-news.css)
   ========================================================= */
if (!function_exists('ks_news_enqueue_assets')) {
  function ks_news_enqueue_assets(): void {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    // ks-utils.css
    $utils_abs = $theme_dir . '/assets/css/ks-utils.css';
    if (file_exists($utils_abs) && !wp_style_is('ks-utils', 'enqueued')) {
      wp_enqueue_style(
        'ks-utils',
        $theme_uri . '/assets/css/ks-utils.css',
        ['kickstart-style'],
        filemtime($utils_abs)
      );
    }

    // ks-home.css
    $home_abs = $theme_dir . '/assets/css/ks-home.css';
    if (file_exists($home_abs) && !wp_style_is('ks-home', 'enqueued')) {
      wp_enqueue_style(
        'ks-home',
        $theme_uri . '/assets/css/ks-home.css',
        ['kickstart-style', 'ks-utils'],
        filemtime($home_abs)
      );
    }

    // ks-dir.css (Hero-Style wie Offers Directory)
    $dir_abs = $theme_dir . '/assets/css/ks-dir.css';
    if (file_exists($dir_abs) && !wp_style_is('ks-dir', 'enqueued')) {
      wp_enqueue_style(
        'ks-dir',
        $theme_uri . '/assets/css/ks-dir.css',
        ['ks-home'],
        filemtime($dir_abs)
      );
    }

    // ks-news.css
    if (wp_style_is('ks-news', 'registered') || wp_style_is('ks-news', 'enqueued')) {
      wp_enqueue_style('ks-news');
      return;
    }

    $news_abs = $theme_dir . '/assets/css/ks-news.css';
    if (file_exists($news_abs)) {
      wp_enqueue_style(
        'ks-news',
        $theme_uri . '/assets/css/ks-news.css',
        ['kickstart-style', 'ks-utils', 'ks-home', 'ks-dir'],
        filemtime($news_abs)
      );
    }
  }
}

/* =========================================================
   HELPERS (GLOBAL – sonst Fatal "Cannot redeclare")
   ========================================================= */
if (!function_exists('ks_news_api_base')) {
  function ks_news_api_base(): string {
    $opt = get_option('ks_news_api', '');
    if (is_string($opt) && $opt) return rtrim($opt, '/');
    return 'http://localhost:5000/api/news';
  }
}

if (!function_exists('ks_news_fetch')) {
  function ks_news_fetch(string $url) {
    $res = wp_remote_get($url, ['timeout' => 10]);
    if (is_wp_error($res)) return null;

    $code = wp_remote_retrieve_response_code($res);
    if ($code < 200 || $code >= 300) return null;

    $body = wp_remote_retrieve_body($res);
    $data = json_decode($body, true);
    return $data ?: null;
  }
}

if (!function_exists('ks_news_format_date')) {
  function ks_news_format_date($iso): string {
    $ts = strtotime($iso ?: '');
    if (!$ts) return '';
    return date_i18n(get_option('date_format'), $ts);
  }
}

if (!function_exists('ks_news_detail_url')) {
  function ks_news_detail_url(string $slug): string {
    $slug = urlencode($slug);
    $detail_page = get_page_by_path('news-detail');
    $detail_base = $detail_page ? get_permalink($detail_page->ID) : home_url('/index.php/news-detail/');
    return esc_url(add_query_arg('slug', $slug, $detail_base));
  }
}

if (!function_exists('ks_news_archive_url')) {
  function ks_news_archive_url(): string {
    $p = get_page_by_path('new');
    if ($p) return get_permalink($p->ID);

    $p = get_page_by_path('news');
    if ($p) return get_permalink($p->ID);

    return home_url('/index.php/new/');
  }
}

if (!function_exists('ks_news_sidebar_url')) {
  function ks_news_sidebar_url(array $args): string {
    $base = ks_news_archive_url();
    return esc_url(add_query_arg($args, $base));
  }
}

/**
 * Holt eine größere Liste (published) und ermittelt:
 * - prev / next anhand der Reihenfolge
 * - "Andere Beiträge": max 3 (ohne aktuellen)
 */
if (!function_exists('ks_news_get_adjacent_and_more')) {
  function ks_news_get_adjacent_and_more(string $api, string $current_slug, int $pool_limit = 200): array {
    $list = ks_news_fetch(add_query_arg([
      'limit'     => $pool_limit,
      'page'      => 1,
      'published' => 'true',
    ], $api));

    $items = ($list && !empty($list['items']) && is_array($list['items'])) ? $list['items'] : [];
    if (!$items) return ['prev' => null, 'next' => null, 'more' => []];

    $idx = -1;
    foreach ($items as $i => $it) {
      if (!empty($it['slug']) && (string)$it['slug'] === (string)$current_slug) {
        $idx = $i;
        break;
      }
    }

    // Annahme: API liefert newest-first.
    // next = "neuerer" (Index - 1), prev = "älterer" (Index + 1)
    $next = ($idx > 0) ? ($items[$idx - 1] ?? null) : null;
    $prev = ($idx >= 0 && $idx < count($items) - 1) ? ($items[$idx + 1] ?? null) : null;

    $more = [];
    foreach ($items as $it) {
      $slug = (string)($it['slug'] ?? '');
      if (!$slug || $slug === $current_slug) continue;
      $more[] = $it;
      if (count($more) >= 3) break;
    }

    return ['prev' => $prev, 'next' => $next, 'more' => $more];
  }
}

if (!function_exists('ks_news_render_sidebar')) {
  function ks_news_render_sidebar(string $api, string $active_category = '', string $active_tag = ''): string {
    ob_start(); ?>
      <aside class="ks-news-sidebar">
        <section class="widget">
          <h3 class="widget-title">LETZTE BEITRÄGE</h3>
          <ul class="widget-list">
            <?php
              $latest = ks_news_fetch(add_query_arg([
                'limit' => 4,
                'page' => 1,
                'published' => 'true'
              ], $api));

              if ($latest && !empty($latest['items'])):
                foreach ($latest['items'] as $n):
                  $t = esc_html($n['title'] ?? '');
                  $d = ks_news_detail_url($n['slug'] ?? '');
                  $date = ks_news_format_date($n['date'] ?? null);
                  $cat = esc_html($n['category'] ?? '');

                  echo '<li class="ks-latest-item">';
                  echo '  <div class="ks-latest-meta">';
                  echo      ($cat ? $cat . ' · ' : '') . esc_html($date);
                  echo '  </div>';
                  echo '  <a class="ks-latest-link" href="'. $d .'">'. $t .'</a>';
                  echo '</li>';
                endforeach;
              else:
                echo '<li>Keine Einträge.</li>';
              endif;
            ?>
          </ul>
        </section>

        <section class="widget">
          <h3 class="widget-title">KATEGORIEN</h3>
          <ul class="widget-list">
            <?php
              $cats = ['Allgemein','News','Partnerverein','Projekte'];
              foreach ($cats as $c):
                $urlC = ks_news_sidebar_url([
                  'category' => $c,
                  'pg' => 1,
                  'tag' => null
                ]);

                $cls  = ($active_category === $c) ? ' class="current"' : '';
                echo "<li><a{$cls} href=\"{$urlC}\">{$c}</a></li>";
              endforeach;
            ?>
          </ul>
        </section>

        <section class="widget">
          <h3 class="widget-title">SCHLAGWÖRTER</h3>

          <?php
            $tax = ks_news_fetch(trailingslashit($api) . 'taxonomy');
            $tagItems = ($tax && !empty($tax['tags']) && is_array($tax['tags'])) ? $tax['tags'] : [];

            $topTags  = array_slice($tagItems, 0, 12);
            $restTags = array_slice($tagItems, 12);
          ?>

          <div class="tagcloud">
            <?php if (!empty($topTags)): ?>
              <?php foreach ($topTags as $t):
                $raw = $t['name'] ?? '';
                if (!$raw) continue;
                $name = esc_html($raw);

                $u = ks_news_sidebar_url([
                  'tag'      => $raw,
                  'pg'       => 1,
                  'category' => null,
                ]);

                $cls = ($active_tag === $raw) ? ' class="current"' : '';
              ?>
                <a<?= $cls ?> href="<?= $u ?>"><?= $name ?></a>
              <?php endforeach; ?>

              <?php if (!empty($restTags)): ?>
                <details class="ks-tag-more">
                  <summary class="ks-tag-more__btn">
                    <span class="ks-tag-more__closed">ALLE SCHLAGWÖRTER ANZEIGEN</span>
                    <span class="ks-tag-more__open">WENIGER ANZEIGEN</span>
                  </summary>

                  <?php foreach ($restTags as $t):
                    $raw = $t['name'] ?? '';
                    if (!$raw) continue;
                    $name = esc_html($raw);

                    $u = ks_news_sidebar_url([
                      'tag'      => $raw,
                      'pg'       => 1,
                      'category' => null,
                    ]);

                    $cls = ($active_tag === $raw) ? ' class="current"' : '';
                  ?>
                    <a<?= $cls ?> href="<?= $u ?>"><?= $name ?></a>
                  <?php endforeach; ?>
                </details>
              <?php endif; ?>

            <?php else: ?>
              <span>Keine Schlagwörter.</span>
            <?php endif; ?>
          </div>
        </section>

        <section class="widget ks-widget-ad">
          <h3 class="widget-title">WERBUNG</h3>
          <a class="ad" href="https://selcuk-kocyigit.de" target="_blank" rel="noopener">
            <img
              src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/ads/mfs.png' ); ?>"
              alt="Werbung"
              loading="lazy"
              width="320"
              height="250"
            >
          </a>
        </section>
      </aside>
    <?php
    return ob_get_clean();
  }
}

/* =========================================================
   SHORTCODES
   ========================================================= */
if (!function_exists('ks_register_news_shortcodes')) {
  function ks_register_news_shortcodes(): void {

    /* ---------- [ks_news_hero] ---------- */
    add_shortcode('ks_news_hero', function ($atts = []) {
      ks_news_enqueue_assets();

      $atts = shortcode_atts([
        'img' => get_stylesheet_directory_uri() . '/assets/img/news/mfs.png',
        'title' => 'News',
        'watermark' => 'NEWS',
      ], $atts, 'ks_news_hero');

      $hero_img = esc_url($atts['img']);
      $title = esc_html($atts['title']);
      $wm = esc_attr($atts['watermark']);

      ob_start(); ?>
        <div class="ks-dir__hero"
             data-watermark="<?= $wm ?>"
             style="--hero-img:url('<?= $hero_img ?>')">
          <div class="ks-dir__hero-inner">
            <div class="ks-dir__crumb">
              <a class="ks-dir__crumb-home" href="<?= esc_url(home_url('/')) ?>">Home</a>
              <span class="sep">/</span>
              <?= $title ?>
            </div>
            <h1 class="ks-dir__hero-title"><?= $title ?></h1>
          </div>
        </div>
      <?php
      return ob_get_clean();
    });

    /* ---------- [ks_news_latest limit="3" thumbs="0"] ---------- */
    add_shortcode('ks_news_latest', function ($atts = []) {
      ks_news_enqueue_assets();

      $atts = shortcode_atts([
        'limit'  => 3,
        'thumbs' => 0,
      ], $atts, 'ks_news_latest');

      $limit  = max(1, min(12, (int) $atts['limit']));
      $thumbs = ((string)$atts['thumbs'] === '1');

      $api  = ks_news_api_base();
      $url  = add_query_arg([
        'limit'     => $limit,
        'page'      => 1,
        'published' => 'true',
      ], $api);

      $data = ks_news_fetch($url);
      if (!$data || empty($data['items'])) {
        return '<p>Keine Beiträge gefunden.</p>';
      }

      ob_start(); ?>
        <section class="ks-news-latest ks-sec ks-py-56">
          <div class="container">
            <div class="ks-grid-3">
              <?php foreach ($data['items'] as $n):
                $title   = esc_html($n['title'] ?? '');
                $date    = ks_news_format_date($n['date'] ?? null);
                $catName = esc_html($n['category'] ?? '');
                $detail  = ks_news_detail_url($n['slug'] ?? '');

                $excerpt = $n['excerpt'] ?? '';
                if (!$excerpt && !empty($n['content'])) {
                  $excerpt = wp_trim_words(wp_strip_all_tags($n['content']), 22);
                }
                $excerpt = esc_html($excerpt);

                $cover = !empty($n['coverImage']) ? esc_url($n['coverImage']) : '';
              ?>
                <article class="ks-news-item">
                  <div class="ks-news-item__meta">
                    <?= $catName ? $catName . ' · ' : '' ?><?= esc_html($date) ?>
                  </div>

                  <?php if ($thumbs && $cover): ?>
                    <a class="ks-news-item__thumb" href="<?= $detail ?>">
                      <img src="<?= $cover ?>" alt="<?= $title ?>" loading="lazy">
                    </a>
                  <?php endif; ?>

                  <h3 class="ks-news-item__title">
                    <a href="<?= $detail ?>"><?= $title ?></a>
                  </h3>

                  <div class="ks-news-item__excerpt"><?= $excerpt ?></div>

                 

     <p class="ks-news-item__more">
  <a class="ks-link-more" href="<?= $detail ?>">
    <span class="ks-link-more__text">MEHR LESEN</span>
  </a>
</p>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php
      return ob_get_clean();
    });

    /* ---------- [ks_news_archive per_page="5"] ---------- */
    add_shortcode('ks_news_archive', function ($atts = []) {
      ks_news_enqueue_assets();

      $atts = shortcode_atts(['per_page' => 5], $atts, 'ks_news_archive');

      $api = ks_news_api_base();

      // Wichtig: pg statt page
      $page = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

      $limit_raw = (int)$atts['per_page'];
      $limit = min(5, max(1, $limit_raw));

      $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
      $tag      = isset($_GET['tag'])      ? sanitize_text_field($_GET['tag'])      : '';

      $args = [
        'limit'     => $limit,
        'page'      => $page,
        'published' => 'true',
      ];
      if ($category) $args['category'] = $category;
      if ($tag)      $args['tag']      = $tag;

      $url  = add_query_arg($args, $api);
      $data = ks_news_fetch($url);

      $left_icon  = get_stylesheet_directory_uri() . '/assets/img/home/left.png';
      $right_icon = get_stylesheet_directory_uri() . '/assets/img/home/right.png';

      ob_start(); ?>

      <!-- WICHTIG: NICHT id="news" (kollidiert mit ks-home.css) -->
      <section id="news-archive" class="ks-news-archive ks-sec ks-py-48">
        <div class="container">

          <div class="ks-grid-12-8">
            <main class="ks-news-main">
              <?php if (!$data || empty($data['items'])): ?>
                <p>Keine Beiträge gefunden.</p>
              <?php else: ?>

                <?php foreach ($data['items'] as $n):
                  $title   = esc_html($n['title'] ?? '');
                  $date    = ks_news_format_date($n['date'] ?? null);
                  $catName = esc_html($n['category'] ?? '');
                  $excerpt = $n['excerpt'] ?? '';

                  if (!$excerpt && !empty($n['content'])) {
                    $excerpt = wp_trim_words(wp_strip_all_tags($n['content']), 48);
                  }
                  $excerpt = esc_html($excerpt);

                  $detail  = ks_news_detail_url($n['slug'] ?? '');
                ?>
                  <article class="ks-news-item">
                    <div class="ks-news-item__meta">
                      <?= $catName ? $catName . ' · ' : '' ?><?= esc_html($date) ?>
                    </div>

                    <h2 class="ks-news-item__title">
                      <a href="<?= $detail ?>"><?= $title ?></a>
                    </h2>

                    <div class="ks-news-item__excerpt"><?= $excerpt ?></div>

                    <p class="ks-news-item__more">
                      <a class="ks-link-more" href="<?= $detail ?>">MEHR LESEN</a>
                    </p>
                  </article>
                <?php endforeach; ?>

                <?php
                  $pages = max(1, intval($data['pages'] ?? 1));
                  if ($pages > 1):
                    $base = add_query_arg('pg', '%#%', get_permalink());

                    $add_args = [];
                    if ($category) $add_args['category'] = $category;
                    if ($tag)      $add_args['tag']      = $tag;

                    echo '<nav class="ks-pagination" aria-label="News Pagination">';
                    echo paginate_links([
                      'base'      => esc_url($base),
                      'format'    => '',
                      'total'     => $pages,
                      'current'   => $page,
                      'mid_size'  => 2,
                      'prev_text' => '<img class="ks-page-ic" src="'. esc_url($left_icon) .'" alt="Zurück" />',
                      'next_text' => '<img class="ks-page-ic" src="'. esc_url($right_icon) .'" alt="Weiter" />',
                      'add_args'  => $add_args,
                    ]);
                    echo '</nav>';
                  endif;
                ?>

              <?php endif; ?>
            </main>

            <?= ks_news_render_sidebar($api, $category, $tag) ?>
          </div>
        </div>
      </section>

      <?php
      return ob_get_clean();
    });

    /* ---------- Markdown/HTML Rendering Helpers ---------- */
    if (!function_exists('ks_news_has_html')) {
      function ks_news_has_html(string $raw): bool {
        return (bool) preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $raw);
      }
    }

    if (!function_exists('ks_news_md_inline')) {
      function ks_news_md_inline(string $text): string {
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $text);
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($m) {
          $label = $m[1] ?? '';
          $url = esc_url($m[2] ?? '');
          return '<a href="'.$url.'" target="_blank" rel="noopener">'.$label.'</a>';
        }, $text);
        return $text;
      }
    }

    if (!function_exists('ks_news_md_close_lists')) {
      function ks_news_md_close_lists(array &$state): string {
        $html = '';
        if ($state['quote']) { $html .= "</blockquote>\n"; $state['quote'] = false; }
        if ($state['ul']) { $html .= "</ul>\n"; $state['ul'] = false; }
        if ($state['ol']) { $html .= "</ol>\n"; $state['ol'] = false; }
        return $html;
      }
    }

    if (!function_exists('ks_news_md_line_to_html')) {
      function ks_news_md_line_to_html(string $line, array &$state): string {
        $trim = rtrim($line);
        if ($trim === '') return ks_news_md_close_lists($state);

        if (preg_match('/^(#{2,4})\s+(.*)$/', $trim, $m)) {
          $lvl = min(4, max(2, strlen($m[1])));
          $txt = ks_news_md_inline(esc_html($m[2]));
          return ks_news_md_close_lists($state) . "<h{$lvl}>{$txt}</h{$lvl}>";
        }

        if (preg_match('/^\>\s?(.*)$/', $trim, $m)) {
          $txt = ks_news_md_inline(esc_html($m[1]));
          if (!$state['quote']) { $state['quote'] = true; return ks_news_md_close_lists($state) . "<blockquote><p>{$txt}</p>"; }
          return "<p>{$txt}</p>";
        }

        if (preg_match('/^\d+\.\s+(.*)$/', $trim, $m)) {
          $txt = ks_news_md_inline(esc_html($m[1]));
          if (!$state['ol']) { $state['ol'] = true; return ks_news_md_close_lists($state) . "<ol><li>{$txt}</li>"; }
          return "<li>{$txt}</li>";
        }

        if (preg_match('/^[-*]\s+(.*)$/', $trim, $m)) {
          $txt = ks_news_md_inline(esc_html($m[1]));
          if (!$state['ul']) { $state['ul'] = true; return ks_news_md_close_lists($state) . "<ul><li>{$txt}</li>"; }
          return "<li>{$txt}</li>";
        }

        $txt = ks_news_md_inline(esc_html($trim));
        return ks_news_md_close_lists($state) . "<p>{$txt}</p>";
      }
    }

    if (!function_exists('ks_news_md_to_html')) {
      function ks_news_md_to_html(string $raw): string {
        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $out = [];
        $state = ['ul' => false, 'ol' => false, 'quote' => false];
        foreach ($lines as $line) $out[] = ks_news_md_line_to_html($line, $state);
        $out[] = ks_news_md_close_lists($state);
        return implode("\n", array_filter($out));
      }
    }

    if (!function_exists('ks_news_render_article_content')) {
      function ks_news_render_article_content(string $raw): string {
        if ($raw === '') return '';
        if (ks_news_has_html($raw)) return wp_kses_post($raw);
        return wp_kses_post(ks_news_md_to_html($raw));
      }
    }

    /* ---------- [ks_news_single] ---------- */
    add_shortcode('ks_news_single', function () {
      ks_news_enqueue_assets();

      $slug = isset($_GET['slug']) ? sanitize_title($_GET['slug']) : '';
      if (!$slug) return '<p>Kein Beitrag ausgewählt.</p>';

      $api = ks_news_api_base();
      $url = trailingslashit($api) . rawurlencode($slug);
      $data = ks_news_fetch($url);
      if (!$data || empty($data['item'])) return '<p>Beitrag nicht gefunden.</p>';

      $n      = $data['item'];
      $title  = esc_html($n['title'] ?? '');
      $date   = ks_news_format_date($n['date'] ?? null);
      $cat    = (string)($n['category'] ?? '');
      $catEsc = esc_html($cat);

      $content_raw = (string)($n['content'] ?? '');
      $content = ks_news_render_article_content($content_raw);

      // Lead: excerpt oder aus Content generieren (MFS-like: fett)
      $lead = (string)($n['excerpt'] ?? '');
      if (!$lead && $content_raw) {
        $lead = wp_trim_words(wp_strip_all_tags($content_raw), 28);
      }
      $lead = esc_html($lead);

      // Gallery: cover + media images (für 3er-Grid)
      $imgs = [];
      if (!empty($n['coverImage'])) $imgs[] = esc_url($n['coverImage']);
      if (!empty($n['media']) && is_array($n['media'])) {
        foreach ($n['media'] as $m) {
          if (($m['type'] ?? '') === 'image' && !empty($m['url'])) {
            $imgs[] = esc_url($m['url']);
          }
        }
      }
      $imgs = array_values(array_unique(array_filter($imgs)));

      // Videos
      $videos = [];
      if (!empty($n['media']) && is_array($n['media'])) {
        foreach ($n['media'] as $m) {
          if (($m['type'] ?? '') === 'video' && !empty($m['url'])) {
            $videos[] = esc_url($m['url']);
          }
        }
      }
      $videos = array_values(array_unique(array_filter($videos)));

      // Sidebar active state
      $active_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
      if (!$active_category) $active_category = $cat;
      $active_tag = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';

      // Adjacent + 3 other posts
      $adj = ks_news_get_adjacent_and_more($api, $slug, 200);
      $prev = $adj['prev'] ?? null;
      $next = $adj['next'] ?? null;
      $more = $adj['more'] ?? [];

      $left_icon  = get_stylesheet_directory_uri() . '/assets/img/home/left.png';
      $right_icon = get_stylesheet_directory_uri() . '/assets/img/home/right.png';

      ob_start(); ?>
        <section id="news-detail" class="ks-news-single ks-sec ks-py-48">
          <div class="container">
            <div class="ks-grid-12-8">

              <main class="ks-news-main">
                <article class="ks-news-article">
                  <div class="ks-news-item__meta">
                    <?= $catEsc ? $catEsc . ' · ' : '' ?><?= esc_html($date) ?>
                  </div>

                  <h1 class="ks-news-single__title"><?= $title ?></h1>

                  <?php if ($lead): ?>
                    <p class="ks-news-single__lead"><strong><?= $lead ?></strong></p>
                  <?php endif; ?>

                  <?php if (!empty($imgs)): ?>
                    <?php if (count($imgs) === 1): ?>
                      <figure class="ks-news-single__hero-media">
                        <img src="<?= $imgs[0] ?>" alt="<?= $title ?>" loading="lazy">
                      </figure>
                    <?php else: ?>
                      <div class="ks-news-single__gallery" aria-label="Bilder">
                        <?php foreach (array_slice($imgs, 0, 6) as $src): ?>
                          <figure class="ks-news-single__img">
                            <img src="<?= $src ?>" alt="<?= $title ?>" loading="lazy">
                          </figure>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>

                  <div class="ks-news-single__content">
                    <?= $content ?>
                  </div>

                  <?php if (!empty($videos)): ?>
                    <section class="ks-news-single__videos" aria-label="Videos">
                      <?php foreach ($videos as $v): ?>
                        <div class="ks-news-single__video">
                          <video controls preload="metadata" src="<?= $v ?>"></video>
                        </div>
                      <?php endforeach; ?>
                    </section>
                  <?php endif; ?>
                </article>

                <!-- NAV + ANDERE BEITRÄGE (klar getrennt) -->
                <section class="ks-news-more-wrap ks-sec ks-py-32">
                  <div class="ks-news-more-head">
                    <h2 class="ks-news-more-title">ANDERE BEITRÄGE</h2>

                    <div class="ks-news-more-nav" aria-label="Artikel Navigation">
                      <?php if ($prev && !empty($prev['slug'])): ?>
                        <a class="ks-news-nav-btn ks-news-nav-btn--prev" href="<?= ks_news_detail_url($prev['slug']) ?>">
                          <img src="<?= esc_url($left_icon) ?>" alt="Zurück" />
                        </a>
                      <?php else: ?>
                        <span class="ks-news-nav-btn ks-news-nav-btn--disabled" aria-hidden="true">
                          <img src="<?= esc_url($left_icon) ?>" alt="" />
                        </span>
                      <?php endif; ?>

                      <?php if ($next && !empty($next['slug'])): ?>
                        <a class="ks-news-nav-btn ks-news-nav-btn--next" href="<?= ks_news_detail_url($next['slug']) ?>">
                          <img src="<?= esc_url($right_icon) ?>" alt="Weiter" />
                        </a>
                      <?php else: ?>
                        <span class="ks-news-nav-btn ks-news-nav-btn--disabled" aria-hidden="true">
                          <img src="<?= esc_url($right_icon) ?>" alt="" />
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if (!empty($more)): ?>
                    <div class="ks-news-more-grid">
                      <?php foreach ($more as $it):
                        $t = esc_html($it['title'] ?? '');
                        $d = ks_news_detail_url($it['slug'] ?? '');
                        $dt = ks_news_format_date($it['date'] ?? null);
                        $cc = esc_html($it['category'] ?? '');
                        $ex = $it['excerpt'] ?? '';
                        if (!$ex && !empty($it['content'])) $ex = wp_trim_words(wp_strip_all_tags($it['content']), 18);
                        $ex = esc_html($ex);
                      ?>
                        <article class="ks-news-more-card">
                          <div class="ks-news-item__meta">
                            <?= $cc ? $cc . ' · ' : '' ?><?= esc_html($dt) ?>
                          </div>
                          <h3 class="ks-news-item__title">
                            <a href="<?= $d ?>"><?= $t ?></a>
                          </h3>
                          <div class="ks-news-item__excerpt"><?= $ex ?></div>
                          <p class="ks-news-item__more">
                            <a class="ks-link-more" href="<?= $d ?>">MEHR LESEN</a>
                          </p>
                        </article>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <p>Keine weiteren Beiträge.</p>
                  <?php endif; ?>
                </section>

              </main>

              <?= ks_news_render_sidebar($api, $active_category, $active_tag) ?>

            </div>

            <?= do_shortcode('[ks_newsletter]') ?>
          </div>
        </section>
      <?php
      return ob_get_clean();
    });
  }
}

add_action('init', 'ks_register_news_shortcodes');










