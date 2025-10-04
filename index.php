<?php get_header(); ?>



<div class="container content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class('entry'); ?>>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <div class="entry-content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; else: ?>
    <p><?php _e('No content found.', 'kickstart-starter'); ?></p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>



