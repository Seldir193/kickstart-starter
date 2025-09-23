<?php
/**
 * Standard Page Template
 * Theme: KickStart Starter
 */

get_header(); ?>

<main id="primary" class="container content">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article <?php post_class('entry'); ?>>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; else : ?>
    <p><?php esc_html_e( 'No content found.', 'kickstart-starter' ); ?></p>
  <?php endif; ?>
</main>

<?php get_footer();
