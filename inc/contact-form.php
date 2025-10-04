<?php
/* -------------------------------------------------------
 * Kontaktformular (Hooks + Handler)
 * -----------------------------------------------------*/
add_action('admin_post_nopriv_send_contact_form', 'ks_handle_contact_form');
add_action('admin_post_send_contact_form',        'ks_handle_contact_form');

function ks_handle_contact_form() {
  // Nonce prüfen
  if (
    !isset($_POST['contact_form_nonce_field']) ||
    !wp_verify_nonce($_POST['contact_form_nonce_field'], 'contact_form_nonce')
  ) {
    return ks_contact_redirect('0');
  }

  // Felder
  $name    = sanitize_text_field($_POST['name']    ?? '');
  $email   = sanitize_email($_POST['email']        ?? '');
  $message = sanitize_textarea_field($_POST['message'] ?? '');

  if (!$name || !is_email($email) || !$message) {
    return ks_contact_redirect('0');
  }

  // Senden
  $to      = 'fussballschule@selcuk-kocyigit.de';
  $subject = 'Kontaktformular';
  $body    = "Name: {$name}\nE-Mail: {$email}\n\nNachricht:\n{$message}\n";
  $headers = [
    'Content-Type: text/plain; charset=UTF-8',
    'Reply-To: ' . sanitize_email($email),
  ];

  $ok = wp_mail($to, $subject, $body, $headers);
  return ks_contact_redirect($ok ? '1' : '0');
}

function ks_contact_redirect($sent = '0') {
  $back = wp_get_referer();
  if (!$back) {
    // Fallback: Kontaktseite
    $back = home_url('/?page_id=143');
  }
  wp_safe_redirect( add_query_arg('sent', $sent, $back) );
  exit;
}
