<?php
// Helper: About-URL finden
if (!function_exists('ks_about_url')) {
  function ks_about_url() {
    $candidates = ['ueber-uns','uber-uns','über-uns','about','ueberuns'];
    foreach ($candidates as $slug) {
      if ($p = get_page_by_path($slug)) return get_permalink($p->ID);
    }
    if ($p = get_page_by_title('Über uns')) return get_permalink($p->ID);
    return '';
  }
}

// Menü-Panel anhängen wie bei Programs
add_filter('walker_nav_menu_start_el', function ($item_output, $item, $depth, $args) {
  if (($args->theme_location ?? '') !== 'primary' || $depth !== 0) return $item_output;
  $about = ks_about_url();
  if (!$about) return $item_output;
  if (trailingslashit($item->url) !== trailingslashit($about)) return $item_output;

  ob_start();
  get_template_part('template-parts/mega', 'about'); // lädt template-parts/mega-about.php
  $panel = ob_get_clean();

  return preg_replace('~</a>~', '</a>' . $panel, $item_output, 1);
}, 10, 4);
