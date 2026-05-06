<?php

if (!function_exists('ks_about_t')) {
  function ks_about_t($key, $fallback) {
    return function_exists('ks_t') ? ks_t($key, $fallback, 'about') : $fallback;
  }
}

if (!function_exists('ks_register_about_shortcode')) {
  function ks_register_about_shortcode() {
    add_shortcode('ks_about', 'ks_render_about_shortcode');
  }

  add_action('init', 'ks_register_about_shortcode');
}

if (!function_exists('ks_render_about_shortcode')) {
  function ks_render_about_shortcode() {
    ks_enqueue_about_team_assets();
    $context = ks_get_about_context();

    ob_start();
    ks_print_about_page($context);

    return ob_get_clean();
  }
}

if (!function_exists('ks_enqueue_about_team_assets')) {
  function ks_enqueue_about_team_assets() {
    if (function_exists('ks_enqueue_team_assets')) {
      ks_enqueue_team_assets();
    }
  }
}

if (!function_exists('ks_get_about_context')) {
  function ks_get_about_context() {
    $theme_uri = get_stylesheet_directory_uri();

    return [
      'theme_uri' => $theme_uri,
      'coaches' => ks_get_coaches(48),
      'trainer_url' => ks_get_trainer_url(),
      'fallback_img' => $theme_uri . '/assets/img/avatar.png',
      'team_section' => locate_template('inc/partials/shared/team-section.php'),
    ];
  }
}

if (!function_exists('ks_print_about_page')) {
  function ks_print_about_page($context) {
    ks_print_about_hero();
    ks_print_about_intro_section();
    ks_print_about_team_section($context);
    ks_print_about_philosophy_section();
    ks_print_about_contact_section($context['theme_uri']);
    ks_print_about_goals_section();
    ks_print_about_locations_section();
  }
}

if (!function_exists('ks_print_about_hero')) {
  function ks_print_about_hero() {
    echo do_shortcode(ks_get_about_hero_shortcode());
  }
}

if (!function_exists('ks_get_about_hero_shortcode')) {
  function ks_get_about_hero_shortcode() {
    $image = esc_url(get_stylesheet_directory_uri() . '/assets/img/hero/mfs.png');

    return sprintf(
      '[ks_hero_page title="Über uns" subtitle="Wir fördern Kinder durch Fußball. Mit Leidenschaft, Kompetenz und Herz." breadcrumb="Home" watermark="ÜBER UNS" image="%s" variant="about" features="1" eyebrow="Mehr als Fussball" primary_label="Unsere Philosophie" primary_href="#philosophie" secondary_label="Trainerteam ansehen" secondary_href="#team" title_i18n="about.hero.title" subtitle_i18n="about.hero.subtitle" breadcrumb_i18n="common.home" watermark_i18n="about.hero.watermark" eyebrow_i18n="pageHero.eyebrow" primary_i18n="pageHero.actions.primary" secondary_i18n="pageHero.actions.team"]',
      $image
    );
  }
}

if (!function_exists('ks_print_about_intro_section')) {
  function ks_print_about_intro_section() {
    ks_print_about_text_list_section(
      'mfs',
      'about.intro.kicker',
      '25 Jahre Fußballerfahrung',
      'about.intro.title',
      'Die Dortmunder Fussball Schule',
      ks_get_about_intro_paragraphs(),
      ks_get_about_intro_items()
    );
  }
}

if (!function_exists('ks_get_about_intro_paragraphs')) {
  function ks_get_about_intro_paragraphs() {
    return [
      [
        'key' => 'about.intro.paragraphs.0',
        'text' => 'Die Dortmunder Fussball Schule wurde 2025 gegründet. Inzwischen ist unser Unternehmen gewachsen – mit über 10 Trainer*innen und Kooperationen mit mehr als 90 Vereinen im Großraum NRW & Dortmund. Unsere Angebote sind vereinsunabhängig und folgen einer ganzheitlichen Ausbildungsphilosophie.',
      ],
      [
        'key' => 'about.intro.paragraphs.1',
        'text' => 'Wir begleiten Kinder und Jugendliche sportlich und persönlich – Jahr für Jahr.',
      ],
    ];
  }
}

if (!function_exists('ks_get_about_intro_items')) {
  function ks_get_about_intro_items() {
    return [
      ['key' => 'about.intro.items.0', 'text' => '25 Jahre Erfahrung'],
      ['key' => 'about.intro.items.1', 'text' => '10 Partner und > 10 Trainer'],
      ['key' => 'about.intro.items.2', 'text' => '> 7000 Kinder & 10 Partnervereine'],
      ['key' => 'about.intro.items.3', 'text' => 'Wöchentliche Trainerfortbildungen'],
      ['key' => 'about.intro.items.4', 'text' => 'Streamingportal mit > 1000 Videos'],
    ];
  }
}

if (!function_exists('ks_print_about_team_section')) {
  function ks_print_about_team_section($context) {
    $theme_uri = $context['theme_uri'];
    $coaches = $context['coaches'];
    $trainer_url = $context['trainer_url'];
    $fallback_img = $context['fallback_img'];

    if ($context['team_section']) {
      include $context['team_section'];
    }
  }
}

if (!function_exists('ks_print_about_philosophy_section')) {
  function ks_print_about_philosophy_section() {
    ks_print_about_text_list_section(
      'philosophie',
      'about.philosophy.kicker',
      'Wofür wir stehen',
      'about.philosophy.title',
      'Unsere Philosophie',
      ks_get_about_philosophy_paragraphs(),
      ks_get_about_philosophy_items()
    );
  }
}

if (!function_exists('ks_get_about_philosophy_paragraphs')) {
  function ks_get_about_philosophy_paragraphs() {
    return [
      [
        'key' => 'about.philosophy.paragraphs.0',
        'text' => 'Wir lehren das Fußballspielen mit Fokus auf Freude, Entwicklung und Charakterbildung. Ausbildung geht bei uns vor Ergebnisdenken – wir fördern nachhaltig und altersgerecht.',
      ],
    ];
  }
}

if (!function_exists('ks_get_about_philosophy_items')) {
  function ks_get_about_philosophy_items() {
    return [
      ['key' => 'about.philosophy.items.0', 'text' => 'Spaß, Freude und Ausbildung vor Ergebnis'],
      ['key' => 'about.philosophy.items.1', 'text' => '> 250 Tricks, Ballannahmen und Schusstechniken'],
      ['key' => 'about.philosophy.items.2', 'text' => 'Komplexes altersgerechtes Athletiktraining'],
      ['key' => 'about.philosophy.items.3', 'text' => 'Hohe Trainingseffizienz durch kleine Gruppen'],
      ['key' => 'about.philosophy.items.4', 'text' => 'Perfekte Trainingsstruktur'],
      ['key' => 'about.philosophy.items.5', 'text' => 'Individual-, Gruppen- und Mannschaftstaktik im Detail'],
    ];
  }
}

if (!function_exists('ks_print_about_goals_section')) {
  function ks_print_about_goals_section() {
    ks_print_about_text_list_section(
      'ziele',
      'about.goals.kicker',
      'Unsere Philosophie',
      'about.goals.title',
      'Unsere Ziele',
      ks_get_about_goal_paragraphs(),
      ks_get_about_goal_items()
    );
  }
}

if (!function_exists('ks_get_about_goal_paragraphs')) {
  function ks_get_about_goal_paragraphs() {
    return [
      [
        'key' => 'about.goals.paragraphs.0',
        'text' => 'Im Mittelpunkt stehen Spaß und Freude am Fußball – das ist die Basis für Leistung und Erfolg. Wir fördern soziale Kompetenz, bieten qualitativ hochwertiges Training und achten auf sportwissenschaftliche Kriterien.',
      ],
    ];
  }
}

if (!function_exists('ks_get_about_goal_items')) {
  function ks_get_about_goal_items() {
    return [
      ['key' => 'about.goals.items.0', 'text' => 'Möglichst vielen Menschen bestmögliches Training ermöglichen'],
      ['key' => 'about.goals.items.1', 'text' => 'Vereine inhaltlich & wirtschaftlich unterstützen'],
      ['key' => 'about.goals.items.2', 'text' => 'Stetige Verbesserung und Weiterentwicklung unserer Philosophie'],
      ['key' => 'about.goals.items.3', 'text' => 'Unsere Philosophie in andere Städte & Länder bringen'],
    ];
  }
}

if (!function_exists('ks_print_about_text_list_section')) {
  function ks_print_about_text_list_section($id, $kicker_key, $kicker, $title_key, $title, $paragraphs, $items) {
    ?>
    <section id="<?php echo esc_attr($id); ?>" class="ks-sec ks-py-48">
      <div class="container container--1100">
        <div class="ks-kicker" data-i18n="<?php echo esc_attr($kicker_key); ?>">
          <?php echo esc_html(ks_about_t($kicker_key, $kicker)); ?>
        </div>

        <h2 class="ks-dir__title ks-mb-16" data-i18n="<?php echo esc_attr($title_key); ?>">
          <?php echo esc_html(ks_about_t($title_key, $title)); ?>
        </h2>

        <div class="ks-grid-12-8">
          <?php ks_print_about_paragraphs($paragraphs); ?>
          <?php ks_print_about_list($items); ?>
        </div>
      </div>
    </section>
    <?php
  }
}

if (!function_exists('ks_print_about_paragraphs')) {
  function ks_print_about_paragraphs($paragraphs) {
    ?>
    <div>
      <?php foreach ($paragraphs as $paragraph): ?>
        <p data-i18n="<?php echo esc_attr($paragraph['key']); ?>">
          <?php echo esc_html(ks_about_t($paragraph['key'], $paragraph['text'])); ?>
        </p>
      <?php endforeach; ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_about_list')) {
  function ks_print_about_list($items) {
    ?>
    <ul class="ks-list-plus">
      <?php foreach ($items as $item): ?>
        <?php ks_print_about_list_item($item); ?>
      <?php endforeach; ?>
    </ul>
    <?php
  }
}

if (!function_exists('ks_print_about_list_item')) {
  function ks_print_about_list_item($item) {
    ?>
    <li>
      <span class="ks-list-plus__icon" aria-hidden="true"></span>
      <span data-i18n="<?php echo esc_attr($item['key']); ?>">
        <?php echo esc_html(ks_about_t($item['key'], $item['text'])); ?>
      </span>
    </li>
    <?php
  }
}

if (!function_exists('ks_print_about_contact_section')) {
  function ks_print_about_contact_section($theme_uri) {
    ?>
    <section id="kontakt" class="ks-sec ks-py-56 ks-bg-dark ks-text-light">
      <div class="container container--1100 ks-text-center">
        <div class="ks-kicker ks-text-accent" data-i18n="about.contact.kicker">
          <?php echo esc_html(ks_about_t('about.contact.kicker', 'Kontakt')); ?>
        </div>

        <h2 class="ks-dir__title ks-text-light" data-i18n="about.contact.title">
          <?php echo esc_html(ks_about_t('about.contact.title', 'Hast du Fragen?')); ?>
        </h2>

        <p data-i18n="about.contact.text">
          <?php echo esc_html(ks_about_t('about.contact.text', 'Bei Interesse kannst du uns folgendermaßen erreichen:')); ?>
        </p>

        <?php ks_print_about_contact_cards($theme_uri); ?>
      </div>
    </section>
    <?php
  }
}

if (!function_exists('ks_print_about_contact_cards')) {
  function ks_print_about_contact_cards($theme_uri) {
    ?>
    <div class="ks-grid-3 ks-mt-28 ks-contact-cards">
      <?php foreach (ks_get_about_contact_cards($theme_uri) as $card): ?>
        <?php ks_print_about_contact_card($card); ?>
      <?php endforeach; ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_get_about_contact_cards')) {
  function ks_get_about_contact_cards($theme_uri) {
    return [
      ks_get_about_contact_card('tel:+4917643203362', 'about.contact.phoneAria', 'Anrufen', 'phone.png', 'about.contact.phoneTitle', 'Ruf uns an:', '+49 (176) 43 20 33 62', $theme_uri),
      ks_get_about_contact_card('mailto:fussballschule@selcuk-kocyigit.de', 'about.contact.mailAria', 'E-Mail schreiben', 'mail.png', 'about.contact.mailTitle', 'Schreib uns:', 'fussballschule@selcuk-kocyigit.de', $theme_uri),
      ks_get_about_contact_card('#top', 'about.contact.hoursAria', 'Nach oben scrollen', 'clock.png', 'about.contact.hoursTitle', 'Telefonzeiten:', 'Mo.–Fr. 09:00–20:00 Uhr', $theme_uri),
    ];
  }
}

if (!function_exists('ks_get_about_contact_card')) {
  function ks_get_about_contact_card($url, $label_key, $label, $icon, $title_key, $title, $text, $theme_uri) {
    return [
      'url' => $url,
      'label_key' => $label_key,
      'label' => $label,
      'icon' => $theme_uri . '/assets/img/offers/' . $icon,
      'title_key' => $title_key,
      'title' => $title,
      'text' => $text,
    ];
  }
}

if (!function_exists('ks_print_about_contact_card')) {
  function ks_print_about_contact_card($card) {
    ?>
    <div class="ks-text-center">
      <a
        class="ks-contact-iconwrap"
        href="<?php echo esc_url($card['url']); ?>"
        aria-label="<?php echo esc_attr(ks_about_t($card['label_key'], $card['label'])); ?>"
        data-i18n="<?php echo esc_attr($card['label_key']); ?>"
        data-i18n-attr="aria-label"
      >
        <img
          class="ks-contact-icon-img"
          src="<?php echo esc_url($card['icon']); ?>"
          alt=""
          aria-hidden="true"
          loading="lazy"
          decoding="async"
        >
      </a>

      <div class="ks-fw-700 ks-mb-16" data-i18n="<?php echo esc_attr($card['title_key']); ?>">
        <?php echo esc_html(ks_about_t($card['title_key'], $card['title'])); ?>
      </div>

      <div>
        <a class="ks-link-light" href="<?php echo esc_url($card['url']); ?>">
          <?php echo esc_html($card['text']); ?>
        </a>
      </div>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_about_locations_section')) {
  function ks_print_about_locations_section() {
    ?>
    <section id="standorte" class="ks-sec ks-py-32 ks-bg-deep ks-text-light ks-standorte">
      <div class="container container--1200">
        <div
          class="ks-title-wrap"
          data-bgword="<?php echo esc_attr(ks_about_t('about.locations.watermark', 'STANDORTE')); ?>"
          data-i18n="about.locations.watermark"
          data-i18n-attr="data-bgword"
        >
          <h2 class="ks-dir__title ks-text-dark" data-i18n="about.locations.title">
            <?php echo esc_html(ks_about_t('about.locations.title', 'Unsere Standorte')); ?>
          </h2>
        </div>

        <p class="ks-mt">
          <a
            href="<?php echo esc_url(home_url('/franchise-2/#fr-worldwide-map')); ?>"
            class="ks-btn"
            data-i18n="about.locations.button"
          >
            <?php echo esc_html(ks_about_t('about.locations.button', 'Zu den Standorten')); ?>
          </a>
        </p>
      </div>
    </section>
    <?php
  }
}












