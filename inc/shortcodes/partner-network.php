<?php

if (!function_exists('ks_register_partner_network_shortcode')) {
  function ks_register_partner_network_shortcode() {
    add_shortcode('ks_partner_network', 'ks_render_partner_network_shortcode');
  }

  add_action('init', 'ks_register_partner_network_shortcode');
}

if (!function_exists('ks_render_partner_network_shortcode')) {
  function ks_render_partner_network_shortcode(): string {
    $partners = ks_partner_network_items();

    if (empty($partners)) {
      return '';
    }

    return ks_partner_network_markup($partners);
  }
}

if (!function_exists('ks_partner_network_markup')) {
  function ks_partner_network_markup(array $partners): string {
    $arrow_icon = get_stylesheet_directory_uri() . '/assets/img/team/arrow_right_alt.svg';

    ob_start();
    ?>
    <section
      id="partner-network"
      class="ks-sec ks-partner-network"
      aria-label="Partner & Netzwerk"
      data-partner-network
    >
      <div class="container container--1400">
        <div class="ks-partner-network__inner">
          <div class="ks-partner-network__intro">
            <span class="ks-partner-network__eyebrow" data-i18n="partnerNetwork.eyebrow">Netzwerk</span>
            <strong class="ks-partner-network__title"  data-i18n="partnerNetwork.title">Partner &amp; Marken</strong>
          </div>

          <div class="ks-partner-network__slider" data-partner-slider>
            <?php echo ks_partner_network_nav_button($arrow_icon, 'prev'); ?>

            <div class="ks-partner-network__viewport">
              <ul class="ks-partner-network__list" role="list" data-partner-track>
                <?php foreach ($partners as $partner): ?>
                  <li class="ks-partner-network__item">
                    <?php echo ks_partner_network_item_markup($partner); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <?php echo ks_partner_network_nav_button($arrow_icon, 'next'); ?>
          </div>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}

if (!function_exists('ks_partner_network_nav_button')) {
  function ks_partner_network_nav_button(string $icon, string $direction): string {
    $modifier = $direction === 'prev' ? 'prev' : 'next';
    $label = $direction === 'prev' ? 'Vorherige Partner' : 'Nächste Partner';

    return sprintf(
      '<button type="button" class="ks-partner-network__nav ks-partner-network__nav--%s" data-partner-%s aria-label="%s"><img class="ks-partner-network__nav-icon" src="%s" alt="" aria-hidden="true"></button>',
      esc_attr($modifier),
      esc_attr($modifier),
      esc_attr($label),
      esc_url($icon)
    );
  }
}

if (!function_exists('ks_partner_network_items')) {
  function ks_partner_network_items(): array {
    $items = ks_fetch_partner_network_items();

    if (empty($items)) {
      return [];
    }

    usort($items, 'ks_sort_partner_network_items');

    return $items;
  }
}

if (!function_exists('ks_fetch_partner_network_items')) {
  function ks_fetch_partner_network_items(): array {
    $response = wp_remote_get(ks_partner_network_api_url(), ['timeout' => 8]);

    if (is_wp_error($response)) {
      return [];
    }

    return ks_parse_partner_network_response($response);
  }
}

if (!function_exists('ks_partner_network_api_url')) {
  function ks_partner_network_api_url(): string {
    $url = 'http://localhost:5000/api/public/partners';
    return apply_filters('ks_partner_network_api_url', $url);
  }
}

if (!function_exists('ks_parse_partner_network_response')) {
  function ks_parse_partner_network_response(array $response): array {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data) || empty($data['ok'])) {
      return [];
    }

    return ks_normalize_partner_network_items($data['items'] ?? []);
  }
}

if (!function_exists('ks_normalize_partner_network_items')) {
  function ks_normalize_partner_network_items($items): array {
    if (!is_array($items)) {
      return [];
    }

    $partners = array_map('ks_normalize_partner_network_item', $items);
    return array_values(array_filter($partners));
  }
}

if (!function_exists('ks_normalize_partner_network_item')) {
  function ks_normalize_partner_network_item($item): array {
    if (!is_array($item)) {
      return [];
    }

    $label = sanitize_text_field((string) ($item['label'] ?? $item['name'] ?? ''));

    if (!$label) {
      return [];
    }

    return [
      'logo' => esc_url_raw((string) ($item['logo'] ?? $item['logoUrl'] ?? $item['src'] ?? '')),
      'label' => $label,
      'url' => esc_url_raw((string) ($item['url'] ?? '')),
      'sortOrder' => (int) ($item['sortOrder'] ?? 100),
    ];
  }
}

if (!function_exists('ks_partner_network_item_markup')) {
  function ks_partner_network_item_markup(array $partner): string {
    $content = ks_partner_network_item_content($partner);

    if (empty($partner['url'])) {
      return $content;
    }

    return sprintf(
      '<a class="ks-partner-network__link" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
      esc_url($partner['url']),
      $content
    );
  }
}

if (!function_exists('ks_partner_network_item_content')) {
  function ks_partner_network_item_content(array $partner): string {
    $logo = ks_partner_network_logo_markup($partner);

    return sprintf(
      '%s<span class="ks-partner-network__label">%s</span>',
      $logo,
      esc_html($partner['label'])
    );
  }
}

if (!function_exists('ks_partner_network_logo_markup')) {
  function ks_partner_network_logo_markup(array $partner): string {
    if (empty($partner['logo'])) {
      return '<span class="ks-partner-network__logo-placeholder" aria-hidden="true"></span>';
    }

    return sprintf(
      '<span class="ks-partner-network__logo-wrap"><img src="%s" alt="" loading="lazy" decoding="async" aria-hidden="true"></span>',
      esc_url($partner['logo'])
    );
  }
}

if (!function_exists('ks_sort_partner_network_items')) {
  function ks_sort_partner_network_items(array $left, array $right): int {
    return (int) ($left['sortOrder'] ?? 100) <=> (int) ($right['sortOrder'] ?? 100);
  }
}