<?php

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 80,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    register_nav_menus([
        'primary' => __('Primary Menu', 'kickstart-starter'),
    ]);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'kickstart-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Roboto:wght@400;500;700&display=swap',
        [],
        null
    );
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'kickstart-style',
        get_stylesheet_uri(),
        ['kickstart-fonts'],
        file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
    );
});

add_action('admin_post_send_contact_form', 'handle_contact_form');
add_action('admin_post_nopriv_send_contact_form', 'handle_contact_form');

function handle_contact_form()
{
    ks_verify_nonce_or_die();
    [$name, $email, $message] = ks_get_input();
    if (ks_invalid_input($name, $email, $message)) {
        ks_redirect(false);
    }
    $to      = 'fussballschule@selcuk-kocyigit.de';
    $subject = 'Neue Kontaktanfrage von ' . $name;
    $headers = ks_build_headers($name, $email);
    $body    = ks_build_body($name, $email, $message);
    $ok      = wp_mail($to, $subject, $body, $headers);
    ks_redirect($ok);
}

function ks_verify_nonce_or_die()
{
    if (
        ! isset($_POST['contact_form_nonce_field'])
        || ! wp_verify_nonce($_POST['contact_form_nonce_field'], 'contact_form_nonce')
    ) {
        wp_die('Ungültige Anfrage, bitte erneut versuchen.', 'Fehler', 403);
    }
}

function ks_get_input()
{
    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    return [$name, $email, $message];
}

function ks_invalid_input($name, $email, $message)
{
    return (empty($name) || ! is_email($email) || empty($message));
}

function ks_build_headers($name, $email)
{
    return [
        'From: Fußballschule <fussballschule@selcuk-kocyigit.de>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'Content-Type: text/html; charset=UTF-8',
    ];
}

function ks_build_body($name, $email, $message)
{
    $body  = '<h3>Neue Kontaktanfrage</h3>';
    $body .= '<p><strong>Name:</strong> ' . esc_html($name) . '</p>';
    $body .= '<p><strong>E-Mail:</strong> ' . esc_html($email) . '</p>';
    $body .= '<p><strong>Nachricht:</strong><br>' . nl2br(esc_html($message)) . '</p>';
    return $body;
}

function ks_redirect($ok)
{
    $url = add_query_arg('sent', $ok ? '1' : '0', wp_get_referer() ?: home_url('/'));
    wp_safe_redirect($url);
    exit;
}






















































