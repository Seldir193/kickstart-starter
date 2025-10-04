<?php
/**
 * Shortcodes für News-Hero und News-Archiv (Liste + Sidebar)
 * Nutzung im Page-Editor:
 *   [ks_news_hero]
 *   [ks_news_archive]
 */

if (!function_exists('ks_register_news_shortcodes')) {
  function ks_register_news_shortcodes() {

    // =========================
    // [ks_news_hero img="..."]
    // =========================
    add_shortcode('ks_news_hero', function ($atts = []) {
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

    // =====================================
    // [ks_news_archive] – Liste + Sidebar
    // =====================================
    add_shortcode('ks_news_archive', function ($atts = []) {
      $atts = shortcode_atts([
        'per_page' => 5,
      ], $atts, 'ks_news_archive');

      // Paginierung auf einer Seite
      $paged = max(1, (int) get_query_var('paged'));
      $q = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $atts['per_page'],
        'paged'          => $paged,
        'ignore_sticky_posts' => true,
      ]);

      ob_start(); ?>
      <section class="ks-news-archive">
        <div class="container ks-news-grid">

          <main class="ks-news-main">
            <?php if ($q->have_posts()): ?>
              <?php while ($q->have_posts()): $q->the_post(); ?>
                <article <?php post_class('ks-news-item'); ?>>
                  <div class="ks-news-item__meta">
                    <?php
                      $cats = get_the_category();
                      if ($cats) {
                        $names = wp_list_pluck($cats, 'name');
                        echo esc_html(implode(', ', $names)) . ' · ';
                      }
                      echo esc_html(get_the_date('d. F Y'));
                    ?>
                  </div>

                  <h2 class="ks-news-item__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h2>

                  <div class="ks-news-item__excerpt">
                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 48)); ?>
                  </div>

                  <p class="ks-news-item__more">
                    <a class="ks-link-more" href="<?php the_permalink(); ?>">MEHR LESEN</a>
                  </p>
                </article>
              <?php endwhile; ?>

              <nav class="ks-pagination">
                <?php
                  echo paginate_links([
                    'total'   => $q->max_num_pages,
                    'current' => $paged,
                    'mid_size'=> 2,
                    'prev_text' => '«',
                    'next_text' => '»',
                  ]);
                ?>
              </nav>

            <?php else: ?>
              <p>Keine Beiträge gefunden.</p>
            <?php endif; wp_reset_postdata(); ?>
          </main>

   

       <aside class="ks-news-sidebar">
            <section class="widget">
              <h3 class="widget-title">LETZTE BEITRÄGE</h3>
              <ul class="widget-list">
                <?php
                  $rp = new WP_Query(['posts_per_page'=>4, 'no_found_rows'=>true]);
                  while ($rp->have_posts()): $rp->the_post(); ?>
                    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                <?php endwhile; wp_reset_postdata(); ?>
              </ul>
            </section>

            <section class="widget">
              <h3 class="widget-title">KATEGORIEN</h3>
              <ul class="widget-list">
                <?php wp_list_categories(['title_li'=>'']); ?>
              </ul>
            </section>

            <section class="widget">
              <h3 class="widget-title">INSTAGRAM</h3>
              <div class="insta-grid">
                <?php for ($i=0; $i<8; $i++): ?>
                  <span class="insta-ph" aria-hidden="true"></span>
                <?php endfor; ?>
              </div>
            </section>

             <!-- Schlagwörter -->
  <section class="widget">
    <h3 class="widget-title">SCHLAGWÖRTER</h3>
    <div class="tagcloud">
      <?php wp_tag_cloud([
        'smallest' => 12, 'largest' => 12, 'unit' => 'px', 'number' => 40
      ]); ?>
    </div>
  </section>

    <!-- Werbung (unter dem Newsletter) -->
  <section class="widget">
    <h3 class="widget-title">WERBUNG</h3>
    <a class="ad" href="#" aria-label="Werbung">
      <img src="<?php echo esc_url( get_stylesheet_directory_uri().'/assets/img/home/mfs.png' ); ?>"
           alt="" width="320" height="180" loading="lazy">
    </a>
  </section>
          </aside>





       


        </div>
      </section>
      <?php
      return ob_get_clean();
    });

  }
  add_action('init', 'ks_register_news_shortcodes');
}
