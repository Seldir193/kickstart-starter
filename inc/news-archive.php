<?php
/**
 * News-Shortcodes: Hero, Latest, Archive (API), Single (API)
 * Verwendung:
 *   Seite "News" (Slug z.B. new):
 *     [ks_news_hero]
 *     [ks_news_archive]
 *     [ks_newsletter]    (optional)
 *
 *   Seite "News Detail" (Slug exakt news-detail):
 *     [ks_news_single]
 */

/* ========== CSS-Helfer: lädt /assets/css/ks-news.css nur wenn ein Shortcode verwendet wird ========== */
if (!function_exists('ks_news_enqueue_assets')) {
  function ks_news_enqueue_assets() {
    // Bevorzugt ein bereits registriertes Stylesheet
    if (wp_style_is('ks-news', 'registered') || wp_style_is('ks-news', 'enqueued')) {
      wp_enqueue_style('ks-news');
      return;
    }
    // Fallback: direkt aus dem Theme-Verzeichnis laden
    $abs = get_stylesheet_directory() . '/assets/css/ks-news.css';
    if (file_exists($abs)) {
      wp_enqueue_style(
        'ks-news',
        get_stylesheet_directory_uri() . '/assets/css/ks-news.css',
        ['kickstart-style'], // nach deiner Basis-CSS
        filemtime($abs)
      );
    }
  }
}

/* ========== Shortcodes registrieren ========== */
if (!function_exists('ks_register_news_shortcodes')) {
  function ks_register_news_shortcodes() {

    /* ---------- Helpers ---------- */
    function ks_news_api_base(): string {
      $opt = get_option('ks_news_api', '');
      if (is_string($opt) && $opt) return rtrim($opt, '/');
      return 'http://localhost:5000/api/news';
    }

    function ks_news_fetch(string $url) {
      $res = wp_remote_get($url, ['timeout' => 7]);
      if (is_wp_error($res)) return null;
      $body = wp_remote_retrieve_body($res);
      $data = json_decode($body, true);
      return $data ?: null;
    }

    function ks_news_format_date($iso): string {
      $ts = strtotime($iso ?: '');
      if (!$ts) return '';
      return date_i18n(get_option('date_format'), $ts);
    }

    // Robuster Basislink für Detailseite (funktioniert mit/ohne index.php)
    function ks_news_detail_url(string $slug): string {
      $slug = urlencode($slug);
      $detail_page = get_page_by_path('news-detail');
      $detail_base = $detail_page ? get_permalink($detail_page->ID) : home_url('/index.php/news-detail/');
      return esc_url(add_query_arg('slug', $slug, $detail_base));
    }

    /* ---------- [ks_news_hero] ---------- */
    add_shortcode('ks_news_hero', function ($atts = []) {
      ks_news_enqueue_assets();

      $atts = shortcode_atts([
        'img' => get_stylesheet_directory_uri() . '/assets/img/news/mfs.png',
      ], $atts, 'ks_news_hero');

      ob_start(); ?>
      <section class="ks-news-hero full-bleed" data-watermark="NEWS"
               style="--news-hero-img:url('<?= esc_url($atts['img']) ?>');">
        <div class="ks-news-hero__inner container">
          <div class="ks-news-hero__left">
            <div class="ks-crumbs">HOME · NEWS</div>
            <h1 class="ks-news-hero__title">News</h1>
          </div>
          <div class="ks-news-hero__art" aria-hidden="true"></div>
        </div>
      </section>
      <?php
      return ob_get_clean();
    });

    /* ---------- [ks_news_latest limit="3"] ---------- */
    add_shortcode('ks_news_latest', function ($atts = []) {
      ks_news_enqueue_assets();

      /*$atts = shortcode_atts(['limit' => 3], $atts, 'ks_news_latest');*/

      $atts = shortcode_atts([
  'limit'  => 3,
  'thumbs' => '1', // 1 = mit Bild, 0 = ohne Bild
], $atts, 'ks_news_latest');

$show_thumbs = ($atts['thumbs'] !== '0');
      $api  = ks_news_api_base();
      $url  = add_query_arg([
        'limit'     => (int)$atts['limit'],
        'page'      => 1,
        'published' => 'true',
      ], $api);

      $data = ks_news_fetch($url);
      if (!$data || empty($data['items'])) return '<p>Keine Neuigkeiten.</p>';

      ob_start(); ?>
      <section class="ks-news-latest">
        <div class="ks-news-grid container">
          <main class="ks-news-main">
            <?php foreach ($data['items'] as $n):
              $title   = esc_html($n['title'] ?? '');
              $date    = ks_news_format_date($n['date'] ?? null);
              $excerpt = $n['excerpt'] ?? '';
              if (!$excerpt && !empty($n['content'])) {
                $excerpt = wp_trim_words(wp_strip_all_tags($n['content']), 36);
              }
              $excerpt = esc_html($excerpt);
              $detail  = ks_news_detail_url($n['slug'] ?? '');
              $cover   = !empty($n['coverImage']) ? esc_url($n['coverImage']) : '';
            ?>
              <article class="ks-news-item">
                <div class="ks-news-item__meta"><?= esc_html($date) ?></div>
                <?php if ($cover): ?>
                  <a href="<?= $detail ?>" class="ks-news-item__thumb">
                    <img src="<?= $cover ?>" alt="<?= $title ?>" loading="lazy" />
                  </a>
                <?php endif; ?>
                <h2 class="ks-news-item__title"><a href="<?= $detail ?>"><?= $title ?></a></h2>
                <div class="ks-news-item__excerpt"><?= $excerpt ?></div>
                <p class="ks-news-item__more"><a class="ks-link-more" href="<?= $detail ?>">MEHR LESEN</a></p>
              </article>
            <?php endforeach; ?>
          </main>
        </div>
      </section>
      <?php
      return ob_get_clean();
    });

    /* ---------- [ks_news_archive per_page="10"] ---------- */
    add_shortcode('ks_news_archive', function ($atts = []) {
      ks_news_enqueue_assets();

      $atts = shortcode_atts(['per_page' => 10], $atts, 'ks_news_archive');

      $api   = ks_news_api_base();

      // Filter & Pagination aus URL
      $page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
      $limit    = max(1, (int)$atts['per_page']);
      $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
      $tag      = isset($_GET['tag'])      ? sanitize_text_field($_GET['tag'])      : '';

      // API-URL mit Filtern
      $args = [
        'limit'     => $limit,
        'page'      => $page,
        'published' => 'true',
      ];
      if ($category) $args['category'] = $category;
      if ($tag)      $args['tag']      = $tag;

      $url  = add_query_arg($args, $api);
      $data = ks_news_fetch($url);

      ob_start(); ?>


      <section class="ks-news-archive">
        <div class="container ks-news-grid">

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
                $cover   = !empty($n['coverImage']) ? esc_url($n['coverImage']) : '';
              ?>
                <article class="ks-news-item">
                  <div class="ks-news-item__meta">
                    <?= $catName ? $catName . ' · ' : '' ?><?= esc_html($date) ?>
                  </div>
                 







<?php if ($cover): ?>
  <a href="<?= $detail ?>" class="ks-news-item__thumb">
    <img src="<?= $cover ?>" alt="<?= $title ?>" loading="lazy" />
  </a>
<?php endif; ?>


                  <h2 class="ks-news-item__title"><a href="<?= $detail ?>"><?= $title ?></a></h2>
                  <div class="ks-news-item__excerpt"><?= $excerpt ?></div>
                  <p class="ks-news-item__more"><a class="ks-link-more" href="<?= $detail ?>">MEHR LESEN</a></p>
                </article>
              <?php endforeach; ?>

              <?php
                $pages = max(1, intval($data['pages'] ?? 1));
                if ($pages > 1):
                  $base = add_query_arg('page', '%#%', get_permalink());
                  $add_args = [];
                  if ($category) $add_args['category'] = $category;
                  if ($tag)      $add_args['tag']      = $tag;
                  echo '<nav class="ks-pagination">';
                  echo paginate_links([
                    'base'      => esc_url($base),
                    'format'    => '',
                    'total'     => $pages,
                    'current'   => $page,
                    'mid_size'  => 2,
                    'prev_text' => '«',
                    'next_text' => '»',
                    'add_args'  => $add_args,
                  ]);
                  echo '</nav>';
                endif;
              ?>
            <?php endif; ?>
          </main>

          <aside class="ks-news-sidebar">
            <!-- Letzte Beiträge -->
            <section class="widget">
              <h3 class="widget-title">LETZTE BEITRÄGE</h3>
              <ul class="widget-list">
                <?php
                  $latest = ks_news_fetch(add_query_arg(['limit'=>4, 'page'=>1, 'published'=>'true'], $api));
                  if ($latest && !empty($latest['items'])):
                    foreach ($latest['items'] as $n):
                      $t = esc_html($n['title'] ?? '');
                      $detail = ks_news_detail_url($n['slug'] ?? '');
                      echo '<li><a href="'. $detail .'">'. $t .'</a></li>';
                    endforeach;
                  else:
                    echo '<li>Keine Einträge.</li>';
                  endif;
                ?>
              </ul>
            </section>

            <!-- Kategorien -->
            <section class="widget">
              <h3 class="widget-title">KATEGORIEN</h3>
              <ul class="widget-list">
                <?php
                  $cats = ['Allgemein','News','Partnerverein','Projekte'];
                  foreach ($cats as $c):
                    $urlC = esc_url(add_query_arg(['category'=>$c, 'page'=>1, 'tag'=>null], get_permalink()));
                    $cls  = $category === $c ? ' class="current"' : '';
                    echo "<li><a{$cls} href=\"{$urlC}\">{$c}</a></li>";
                  endforeach;
                ?>
              </ul>
            </section>

            <!-- Schlagwörter -->
            <section class="widget">
              <h3 class="widget-title">SCHLAGWÖRTER</h3>
              <div class="tagcloud">
                <?php
                  $tax = ks_news_fetch(trailingslashit($api) . 'taxonomy');
                  $tagItems = $tax && !empty($tax['tags']) ? $tax['tags'] : [];
                  if ($tagItems):
                    foreach ($tagItems as $t):
                      $name = esc_html($t['name']);
                      $u = esc_url(add_query_arg(['tag'=>$t['name'], 'page'=>1, 'category'=>null], get_permalink()));
                      echo "<a href=\"{$u}\">{$name}</a> ";
                    endforeach;
                  else:
                    echo '<span>Keine Schlagwörter.</span>';
                  endif;
                ?>
              </div>
            </section>

             <!-- WERBUNG (direkt unter Schlagwörtern) -->
  <section class="widget ks-widget-ad">
    <h3 class="widget-title">WERBUNG</h3>
    <a class="ad" href="https://dein-link.ziel/" target="_blank" rel="noopener">
      <img
        src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/ads/mfs.png' ); ?>"
        alt="Werbung"
        loading="lazy"
        width="320"
        height="250"
        style="max-width:100%;height:auto;display:block;border-radius:8px"
      >
    </a>
  </section>
          </aside>

        </div>
      </section>
      <?php
      return ob_get_clean();
    });

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
      $cover  = !empty($n['coverImage']) ? esc_url($n['coverImage']) : '';
      $content_raw = $n['content'] ?? '';
      $content = wp_kses_post($content_raw);

      ob_start(); ?>
      <article class="ks-news-single container">
        <header>
          <h1><?= $title ?></h1>
          <?php if ($date): ?><time class="ks-news-date"><?= esc_html($date) ?></time><?php endif; ?>
          <?php if ($cover): ?><img src="<?= $cover ?>" alt="<?= $title ?>" loading="lazy"/><?php endif; ?>
        </header>

        <div class="ks-news-content"><?= $content ?></div>

        <?php if (!empty($n['media']) && is_array($n['media'])): ?>
          <section class="ks-news-media">
            <h2>Medien</h2>
            <?php foreach ($n['media'] as $m):
              $type = $m['type'] ?? '';
              $url  = esc_url($m['url'] ?? '');
              $alt  = esc_attr($m['alt'] ?? '');
              if ($type === 'image' && $url): ?>
                <figure><img loading="lazy" src="<?= $url ?>" alt="<?= $alt ?>"></figure>
              <?php elseif ($type === 'video' && $url): ?>
                <div class="ratio ratio-16x9">
                  <video controls preload="metadata" src="<?= $url ?>"></video>
                </div>
              <?php endif; endforeach; ?>
          </section>
        <?php endif; ?>
      </article>
      <?php
      return ob_get_clean();
    });

  }
  add_action('init', 'ks_register_news_shortcodes');
}










