<?php
/**
 * KickStart Starter Theme – functions.php
 */

/* -------------------------------------------------------
 * Theme Setup
 * -----------------------------------------------------*/
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


/* -------------------------------------------------------
 * Styles & Scripts (zusammengeführt, inkl. Mega-Menu JS)
 * -----------------------------------------------------*/
add_action('wp_enqueue_scripts', function () {
    // Google Fonts
    wp_enqueue_style(
        'kickstart-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Roboto:wght@400;500;700&display=swap',
        [],
        null
    );

    // Theme CSS (mit Cache-Busting über filemtime)
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'kickstart-style',
        get_stylesheet_uri(),
        ['kickstart-fonts'],
        file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
    );

    // Mega-Menu Script (nur laden, wenn vorhanden)
    $mega_js = get_stylesheet_directory() . '/assets/js/mega-menu.js';
    if (file_exists($mega_js)) {
        wp_enqueue_script(
            'kickstart-mega-menu',
            get_stylesheet_directory_uri() . '/assets/js/mega-menu.js',
            [],
            filemtime($mega_js),
            true // Footer
        );
    }
});


/* -------------------------------------------------------
 * Kontaktformular (Admin Post Handler)
 * -----------------------------------------------------*/
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

    $ok = wp_mail($to, $subject, $body, $headers);
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


/* -------------------------------------------------------
 * Helpers: API-Basis, Next-Frontend-Basis, Offers-URL
 * -----------------------------------------------------*/

// Backend-API Basis (Express)
if (!function_exists('ks_api_base')) {
    function ks_api_base() {
        // ggf. per Filter überschreibbar machen:
        $base = 'http://localhost:5000';
        return rtrim(apply_filters('ks_api_base', $base), '/');
    }
}

// Next.js Frontend Basis
if (!function_exists('ks_next_base')) {
    function ks_next_base() {
        $base = 'http://localhost:3000';
        return rtrim(apply_filters('ks_next_base', $base), '/');
    }
}

// Liefert die echte URL der WP-Seite /angebote/ (robust, mit/ohne index.php)
if (!function_exists('ks_offers_url')) {
    function ks_offers_url() {
        $page = get_page_by_path('angebote'); // WP-Seite mit Slug "angebote" anlegen/veröffentlichen
        if ($page) return get_permalink($page->ID);
        // Fallback, falls Seite noch fehlt oder Permalinks aus sind
        return home_url('/index.php/angebote/');
    }
}


/* -------------------------------------------------------
 * Shortcodes
 * -----------------------------------------------------*/

// Test: [ks_ping]
add_action('init', function () {
    add_shortcode('ks_ping', function () {
        return '<p>Shortcode OK</p>';
    });
});

// Unified Offers Shortcode:
//   [ks_offers type="Camp" limit="6"]          (statisch)
//   [ks_offers limit="12"]  + URL ?type=Kindergarten (dynamisch)
add_action('init', function () {
    add_shortcode('ks_offers', 'ks_sc_offers');
});

function ks_sc_offers($atts) {
    $api_base  = ks_api_base();   // z.B. http://localhost:5000
    $book_base = ks_next_base();  // z.B. http://localhost:3000

    $a     = shortcode_atts(['type' => '', 'limit' => '12'], $atts, 'ks_offers');
    $limit = max(1, intval($a['limit']));

    // 1) type aus Attribut bevorzugen, sonst aus URL (?type=...)
    $typeAttr = trim((string)$a['type']);
    $typeUrl  = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : '';
    $type     = $typeAttr !== '' ? $typeAttr : $typeUrl;

    // Enum wie im Backend
    $valid_types = ['Camp','Foerdertraining','Kindergarten','PersonalTraining','AthleticTraining'];
    if ($type !== '' && !in_array($type, $valid_types, true)) {
        $type = ''; // ungültig -> alle
    }

    // API-URL bauen
    $url = $api_base . '/api/offers';
    if ($type !== '') $url = add_query_arg('type', rawurlencode($type), $url);

    // Daten holen
    $res = wp_remote_get($url, ['timeout' => 10, 'headers' => ['Accept' => 'application/json']]);
    if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
        return '<p>No offers found.</p>';
    }

    $data  = json_decode(wp_remote_retrieve_body($res), true);
    $items = [];
    if (isset($data['items']) && is_array($data['items'])) {
        $items = $data['items'];
    } elseif (is_array($data) && array_keys($data) === range(0, count($data)-1)) {
        $items = $data; // bare array
    }

    if (empty($items)) return '<p>No offers found.</p>';
    if (count($items) > $limit) $items = array_slice($items, 0, $limit);

    // Markup wie gewohnt, damit vorhandenes CSS greift
    ob_start();
    echo '<div class="grid">';
    foreach ($items as $o) {
        $id    = isset($o['_id']) ? (string)$o['_id'] : '';
        $to    = isset($o['type']) ? (string)$o['type'] : '';
        $loc   = isset($o['location']) ? (string)$o['location'] : '';
        $title = !empty($o['title']) ? (string)$o['title'] : trim($to . ' • ' . $loc);

        $priceStr = (isset($o['price']) && $o['price'] !== '') ? (intval($o['price']) . ' €') : '';
        $timeStr  = (!empty($o['timeFrom']) && !empty($o['timeTo'])) ? ($o['timeFrom'] . '–' . $o['timeTo']) : '';
        $ageStr   = (isset($o['ageFrom'],$o['ageTo']) && $o['ageFrom'] !== null && $o['ageTo'] !== null)
                    ? ('Ages ' . intval($o['ageFrom']) . '–' . intval($o['ageTo'])) : '';

        $book = $id ? $book_base . '/book?offerId=' . rawurlencode($id) : '';

        echo '<article class="card">';
            echo '<h3 class="card-title">' . esc_html($title ?: 'Offer') . '</h3>';
            if ($loc) {
                echo '<div class="offer-meta">' . esc_html($loc) . '</div>';
            }

            // Meta-Linie (Preis / Zeit / Alter)
            $metaParts = array_values(array_filter([$priceStr, $timeStr, $ageStr]));
            if ($metaParts) {
                echo '<div class="offer-price">' . esc_html(implode(' • ', $metaParts)) . '</div>';
            }

            if (!empty($o['info'])) {
                echo '<p class="offer-info">' . esc_html((string)$o['info']) . '</p>';
            }

            if ($book) {
                echo '<div class="card-actions">';
                echo '<a class="btn btn-primary" href="' . esc_url($book) . '" target="_blank" rel="noopener noreferrer">Book now</a>';
                echo '</div>';
            }
        echo '</article>';
    }
    echo '</div>';

    return ob_get_clean();
}
