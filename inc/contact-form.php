<?php
/* -------------------------------------------------------
 * Kontaktformular (Hooks + Handler)
 * Datei: inc/contact-form.php
 * -----------------------------------------------------*/

// admin-post (Fallback ohne JS)
add_action('admin_post_nopriv_send_contact_form', 'ks_handle_contact_form');
add_action('admin_post_send_contact_form',        'ks_handle_contact_form');

// admin-ajax (AJAX ohne Reload -> kein Scroll)
add_action('wp_ajax_nopriv_ks_contact_send', 'ks_contact_ajax_send');
add_action('wp_ajax_ks_contact_send',        'ks_contact_ajax_send');

/**
 * Styles + JS nur dort laden, wo es gebraucht wird:
 * - Kontakt-Seite (ID 143)
 * - Franchise-Seite (ID 307) -> dort nutzt du das Partial ebenfalls
 */
if (!function_exists('ks_enqueue_contact_assets')) {
  add_action('wp_enqueue_scripts', 'ks_enqueue_contact_assets');

  function ks_enqueue_contact_assets() {
    if (is_admin()) return;

    // ✅ nur auf den Seiten, wo das Contact-Partial wirklich genutzt wird
    if (!is_page(143) && !is_page(307)) return;

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    ks_enqueue_style_if_exists('ks-utils',   $theme_dir, $theme_uri, '/assets/css/ks-utils.css',   ['kickstart-style']);
    ks_enqueue_style_if_exists('ks-home',    $theme_dir, $theme_uri, '/assets/css/ks-home.css',    ['kickstart-style', 'ks-utils']);
    ks_enqueue_style_if_exists('ks-contact', $theme_dir, $theme_uri, '/assets/css/ks-contact.css', ['kickstart-style', 'ks-utils', 'ks-home']);

    // JS (Validation + AJAX submit)
    $js_rel = '/assets/js/ks-contact-form.js';
    $js_abs = $theme_dir . $js_rel;
    if (file_exists($js_abs) && !wp_script_is('ks-contact-form', 'enqueued')) {
      wp_enqueue_script('ks-contact-form', $theme_uri . $js_rel, [], filemtime($js_abs), true);
    }
  }

  function ks_enqueue_style_if_exists($handle, $theme_dir, $theme_uri, $rel_path, $deps = []) {
    $abs = $theme_dir . $rel_path;
    if (!file_exists($abs)) return;
    if (wp_style_is($handle, 'enqueued')) return;

    wp_enqueue_style($handle, $theme_uri . $rel_path, $deps, filemtime($abs));
  }
}

/* -------------------------
   Gemeinsame Validierung
-------------------------- */
function ks_contact_sanitize_payload($src) {
  $name    = sanitize_text_field($src['name'] ?? '');
  $email   = sanitize_email($src['email'] ?? '');
  $message = sanitize_textarea_field($src['message'] ?? '');

  return [
    'name'    => $name,
    'email'   => $email,
    'message' => $message,
  ];
}

function ks_contact_is_valid($payload) {
  if (empty($payload['name'])) return false;
  if (empty($payload['email']) || !is_email($payload['email'])) return false;
  if (empty($payload['message'])) return false;
  return true;
}

function ks_contact_send_mail($payload) {
  $to      = 'fussballschule@selcuk-kocyigit.de';
  $subject = 'Kontaktformular';

  $body = "Name: {$payload['name']}\n";
  $body .= "E-Mail: {$payload['email']}\n\n";
  $body .= "Nachricht:\n{$payload['message']}\n";

  $headers = [
    'Content-Type: text/plain; charset=UTF-8',
    'Reply-To: ' . sanitize_email($payload['email']),
  ];

  return wp_mail($to, $subject, $body, $headers);
}

/* -------------------------
   AJAX Handler (kein Reload)
-------------------------- */
function ks_contact_ajax_send() {
  // Nonce prüfen
  $nonce = $_POST['contact_form_nonce_field'] ?? '';
  if (!$nonce || !wp_verify_nonce($nonce, 'contact_form_nonce')) {
    wp_send_json(['ok' => false, 'error' => 'Sicherheitsprüfung fehlgeschlagen. Bitte Seite neu laden.'], 400);
  }

  $payload = ks_contact_sanitize_payload($_POST);
  if (!ks_contact_is_valid($payload)) {
    wp_send_json(['ok' => false, 'error' => 'Bitte alle Pflichtfelder korrekt ausfüllen.'], 400);
  }

  $ok = ks_contact_send_mail($payload);
  if (!$ok) {
    wp_send_json(['ok' => false, 'error' => 'Senden fehlgeschlagen. Bitte erneut versuchen.'], 500);
  }

  wp_send_json(['ok' => true, 'message' => 'Vielen Dank! Ihre Nachricht wurde gesendet.'], 200);
}

/* -------------------------
   admin-post Fallback (No-JS)
-------------------------- */
function ks_handle_contact_form() {
  if (
    !isset($_POST['contact_form_nonce_field']) ||
    !wp_verify_nonce($_POST['contact_form_nonce_field'], 'contact_form_nonce')
  ) {
    return ks_contact_redirect('0');
  }

  $payload = ks_contact_sanitize_payload($_POST);
  if (!ks_contact_is_valid($payload)) {
    return ks_contact_redirect('0');
  }

  $ok = ks_contact_send_mail($payload);
  return ks_contact_redirect($ok ? '1' : '0');
}

function ks_contact_redirect($sent = '0') {
  $back = wp_get_referer();
  if (!$back) {
    $back = home_url('/?page_id=143');
  }

  // ✅ Fallback möglichst “unten bleiben”: an Kontakt-Sektion ankern
  $url = add_query_arg('sent', $sent, $back);
  $url .= '#kontakt';

  wp_safe_redirect($url);
  exit;
}






