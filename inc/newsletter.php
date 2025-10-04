




<?php
// Markup holen
function ks_newsletter_markup(): string {
  ob_start();
  $tpl = get_stylesheet_directory() . '/template-parts/components/newsletter.php';
  if (file_exists($tpl)) include $tpl;
  return ob_get_clean();
}


// Assets einreihen (dein JS bleibt 1:1)
function ks_newsletter_enqueue_assets(): void {
  $base = get_stylesheet_directory();
  $uri  = get_stylesheet_directory_uri();

  // optionales CSS
  if (file_exists("$base/assets/css/ks-newsletter.css")) {
    wp_enqueue_style('ks-newsletter', "$uri/assets/css/ks-newsletter.css", ['kickstart-style'], filemtime("$base/assets/css/ks-newsletter.css"));
  }

  // dein JS unverändert
  if (file_exists("$base/assets/js/ks-newsletter.js")) {
    wp_enqueue_script('ks-newsletter', "$uri/assets/js/ks-newsletter.js", [], filemtime("$base/assets/js/ks-newsletter.js"), true);

    // API optional zentral setzen (ändert deine Logik NICHT, nur window.KS_NEWS.api)
    $api = get_option('ks_newsletter_api', 'http://127.0.0.1:5000/api/public/newsletter');
    wp_add_inline_script('ks-newsletter', 'window.KS_NEWS = { api: ' . json_encode($api) . ' };', 'before');
  }
}

// Shortcode [ks_newsletter]
add_action('init', function () {
  add_shortcode('ks_newsletter', function ($atts = []) {
    ks_newsletter_enqueue_assets();
    return ks_newsletter_markup();
  });
});





