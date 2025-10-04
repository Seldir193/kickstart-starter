

<?php
/** front-page.php (sehr schlank) */
get_header(); ?>

<main class="site-main">
  <?php
  if (have_posts()) {
    while (have_posts()) { the_post(); the_content(); }
  } else {
    // Fallback, falls die Seite leer ist:
    echo do_shortcode('[ks_home]');
  }
  ?>
</main>

<?php get_footer();




