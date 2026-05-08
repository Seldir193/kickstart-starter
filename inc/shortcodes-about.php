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
    if (function_exists('ks_enqueue_info_section_assets')) {
      ks_enqueue_info_section_assets();
    }

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
   
    ks_print_shared_contact_section('ks-py-32');
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
      ks_get_about_fact_item('about.intro.items.0', '25 Jahre Erfahrung', 'about.intro.itemTexts.0', 'Langjährige Erfahrung in Training, Förderung und Entwicklung.', 'trophy.svg'),
      ks_get_about_fact_item('about.intro.items.1', '10 Partner und > 10 Trainer', 'about.intro.itemTexts.1', 'Ein starkes Netzwerk aus Partnern und qualifizierten Trainern.', 'license.svg'),
      ks_get_about_fact_item('about.intro.items.2', '> 7000 Kinder & 10 Partnervereine', 'about.intro.itemTexts.2', 'Viele Kinder, Vereine und Familien vertrauen auf unsere Arbeit.', 'group.svg'),
      ks_get_about_fact_item('about.intro.items.3', 'Wöchentliche Trainerfortbildungen', 'about.intro.itemTexts.3', 'Regelmäßige Weiterentwicklung für moderne Trainingsqualität.', 'academy.svg'),
      ks_get_about_fact_item('about.intro.items.4', 'Streamingportal mit > 1000 Videos', 'about.intro.itemTexts.4', 'Digitale Inhalte für Training, Vorbereitung und Weiterentwicklung.', 'video.svg'),
    ];
  }
}

if (!function_exists('ks_get_about_fact_item')) {
  function ks_get_about_fact_item($key, $text, $desc_key, $description, $icon) {
    return [
      'key' => $key,
      'text' => $text,
      'desc_key' => $desc_key,
      'description' => $description,
      'icon' => $icon,
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
      ks_get_about_fact_item('about.philosophy.items.0', 'Spaß, Freude und Ausbildung vor Ergebnis', '', '', 'trophy.svg'),
      ks_get_about_fact_item('about.philosophy.items.1', '> 250 Tricks, Ballannahmen und Schusstechniken', '', '', 'video.svg'),
      ks_get_about_fact_item('about.philosophy.items.2', 'Komplexes altersgerechtes Athletiktraining', '', '', 'academy.svg'),
      ks_get_about_fact_item('about.philosophy.items.3', 'Hohe Trainingseffizienz durch kleine Gruppen', '', '', 'group.svg'),
      ks_get_about_fact_item('about.philosophy.items.4', 'Perfekte Trainingsstruktur', '', '', 'license.svg'),
      ks_get_about_fact_item('about.philosophy.items.5', 'Individual-, Gruppen- und Mannschaftstaktik im Detail', '', '', 'trophy.svg'),
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
      ks_get_about_fact_item('about.goals.items.0', 'Möglichst vielen Menschen bestmögliches Training ermöglichen', '', '', 'group.svg'),
      ks_get_about_fact_item('about.goals.items.1', 'Vereine inhaltlich & wirtschaftlich unterstützen', '', '', 'license.svg'),
      ks_get_about_fact_item('about.goals.items.2', 'Stetige Verbesserung und Weiterentwicklung unserer Philosophie', '', '', 'academy.svg'),
      ks_get_about_fact_item('about.goals.items.3', 'Unsere Philosophie in andere Städte & Länder bringen', '', '', 'trophy.svg'),
    ];
  }
}

if (!function_exists('ks_print_about_text_list_section')) {
  function ks_print_about_text_list_section($id, $kicker_key, $kicker, $title_key, $title, $paragraphs, $items) {
    ?>
    <section id="<?php echo esc_attr($id); ?>" class="ks-sec ks-py-48 ks-info-section">
      <div class="container container--1100">
        <div class="ks-info-section__grid">
          <div class="ks-info-section__content">
            <div class="ks-kicker" data-i18n="<?php echo esc_attr($kicker_key); ?>">
              <?php echo esc_html(ks_about_t($kicker_key, $kicker)); ?>
            </div>

            <h2 class="ks-dir__title ks-dir__title--split ks-mb-16">
              <span class="ks-dir__title-line" data-i18n="<?php echo esc_attr($title_key); ?>">
                <?php echo esc_html(ks_about_t($title_key, $title)); ?>
              </span>
            </h2>

            <?php ks_print_about_paragraphs($paragraphs); ?>
          </div>

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
    <div class="ks-info-section__copy">
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
    <ul class="ks-info-facts" role="list">
      <?php foreach ($items as $item): ?>
        <?php ks_print_about_list_item($item); ?>
      <?php endforeach; ?>
    </ul>
    <?php
  }
}

if (!function_exists('ks_print_about_list_item')) {
  function ks_print_about_list_item($item) {
    $icon = get_stylesheet_directory_uri() . '/assets/img/team/' . $item['icon'];
    ?>
    <li class="ks-info-fact">
      <span class="ks-info-fact__icon" aria-hidden="true">
        <img src="<?php echo esc_url($icon); ?>" alt="" loading="lazy" decoding="async">
      </span>

      <span class="ks-info-fact__body">
        <strong class="ks-info-fact__title" data-i18n="<?php echo esc_attr($item['key']); ?>">
          <?php echo esc_html(ks_about_t($item['key'], $item['text'])); ?>
        </strong>

        <?php if (!empty($item['desc_key']) || !empty($item['description'])): ?>
          <span class="ks-info-fact__text" data-i18n="<?php echo esc_attr($item['desc_key']); ?>">
            <?php echo esc_html(ks_about_t($item['desc_key'], $item['description'])); ?>
          </span>
        <?php endif; ?>
      </span>
    </li>
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
          <a href="<?php echo esc_url(home_url('/franchise-2/#fr-worldwide-map')); ?>" class="ks-btn" data-i18n="about.locations.button">
            <?php echo esc_html(ks_about_t('about.locations.button', 'Zu den Standorten')); ?>
          </a>
        </p>
      </div>
    </section>
    <?php
  }
}



