<!-- </main> -->

<?php
$custom_logo_id = get_theme_mod('custom_logo');
$footer_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';

$theme_uri = get_stylesheet_directory_uri();
$offers_img = $theme_uri . '/assets/img/offers/';
$footer_img = $theme_uri . '/assets/img/footer/';

$footer_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'footer') : $fallback;
};

$common_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'common') : $fallback;
};

$fr_page = get_page_by_path('franchise');
$fr_url = $fr_page ? get_permalink($fr_page->ID) : site_url('/franchise/');
$fr_url .= '#fr-worldwide-map';

$dfsmanager_url = 'https://selcuk-kocyigit.de/';
$provider_login_url = 'http://localhost:3000/admin/login';
?>

<footer
  class="site-footer"
  role="contentinfo"
  aria-label="<?php echo esc_attr($footer_t('footer.ariaLabel', 'Fußzeile')); ?>"
  data-i18n="footer.ariaLabel"
  data-i18n-attr="aria-label"
  style="--footer-link-icon:url('<?php echo esc_url($theme_uri . '/assets/img/team/arrow_right_alt.svg'); ?>');"
>
  <div class="footer-top">
    <div class="container footer-grid">
      <section class="footer-column footer-contact">
        <div class="footer-kicker">
          <img
            src="<?php echo esc_url($footer_img . 'footer-contact.svg'); ?>"
            alt=""
            loading="lazy"
          >
          <span data-i18n="footer.contact.kicker">
            <?php echo esc_html($footer_t('footer.contact.kicker', 'Wir sind für dich da')); ?>
          </span>
        </div>

        <h4 class="footer-head" data-i18n="footer.contact.title">
          <?php echo esc_html($footer_t('footer.contact.title', 'Kontakt')); ?>
        </h4>

        <ul class="footer-list">
          <li class="footer-contact-item">
            <span
              class="footer-contact-icon"
              style="--icon:url('<?php echo esc_url($offers_img . 'location.png'); ?>')"
            ></span>
            <a href="<?php echo esc_url($fr_url); ?>" data-i18n="footer.contact.region">
              <?php echo esc_html($footer_t('footer.contact.region', 'Dortmund & Umgebung')); ?>
            </a>
          </li>

          <li class="footer-contact-item">
            <span
              class="footer-contact-icon"
              style="--icon:url('<?php echo esc_url($offers_img . 'mail.png'); ?>')"
            ></span>
            <a href="mailto:info@dortmunder-fussballschule.de">
              info@dortmunder-fussballschule.de
            </a>
          </li>

          <li class="footer-contact-item">
            <span
              class="footer-contact-icon"
              style="--icon:url('<?php echo esc_url($offers_img . 'phone.png'); ?>')"
            ></span>
            <a href="tel:+4917643203362">+49 176 4320 3362</a>
          </li>
        </ul>
      </section>

      <nav
        class="footer-column footer-links"
        aria-label="<?php echo esc_attr($footer_t('footer.service.ariaLabel', 'Quicklinks')); ?>"
      >
        <div class="footer-kicker">
          <img
            src="<?php echo esc_url($footer_img . 'footer-links.svg'); ?>"
            alt=""
            loading="lazy"
          >
          <span data-i18n="footer.service.kicker">
            <?php echo esc_html($footer_t('footer.service.kicker', 'Wichtige Links')); ?>
          </span>
        </div>

        <h4 class="footer-head" data-i18n="footer.service.title">
          <?php echo esc_html($footer_t('footer.service.title', 'Service')); ?>
        </h4>

        <?php if (has_nav_menu('footer')): ?>
          <?php
          wp_nav_menu([
            'theme_location' => 'footer',
            'container' => false,
            'menu_class' => 'footer-menu',
            'fallback_cb' => false,
            'depth' => 1,
          ]);
          ?>
        <?php else: ?>
          <ul class="footer-menu">
            <li>
              <a
                href="<?php echo esc_url($dfsmanager_url); ?>"
                target="_blank"
                rel="noopener"
                data-i18n="footer.service.dfsManager"
              >
                <?php echo esc_html($footer_t('footer.service.dfsManager', 'DFS Manager')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/abo-kuendigen/')); ?>"
                data-i18n="footer.service.cancelSubscription"
              >
                <?php echo esc_html($footer_t('footer.service.cancelSubscription', 'Abo kündigen')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/widerrufen/')); ?>"
                data-i18n="footer.service.withdrawContract"
              >
                <?php echo esc_html($footer_t('footer.service.withdrawContract', 'Vertrag widerrufen')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/?page_id=143')); ?>"
                data-i18n="footer.service.helpContact"
              >
                <?php echo esc_html($footer_t('footer.service.helpContact', 'Hilfe & Kontakt')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/impressum/')); ?>"
                data-i18n="footer.service.legalNotice"
              >
                <?php echo esc_html($footer_t('footer.service.legalNotice', 'Impressum')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/datenschutz/')); ?>"
                data-i18n="footer.service.privacy"
              >
                <?php echo esc_html($footer_t('footer.service.privacy', 'Datenschutz')); ?>
              </a>
            </li>

            <li>
              <a
                href="<?php echo esc_url(site_url('/agb/')); ?>"
                data-i18n="footer.service.terms"
              >
                <?php echo esc_html($footer_t('footer.service.terms', 'AGB')); ?>
              </a>
            </li>
          </ul>
        <?php endif; ?>
      </nav>

      <section class="footer-column footer-social">
        <div class="footer-kicker">
          <img
            src="<?php echo esc_url($footer_img . 'footer-social.svg'); ?>"
            alt=""
            loading="lazy"
          >
          <span data-i18n="footer.social.kicker">
            <?php echo esc_html($footer_t('footer.social.kicker', 'Bleib verbunden')); ?>
          </span>
        </div>

        <h4 class="footer-head" data-i18n="footer.social.title">
          <?php echo esc_html($footer_t('footer.social.title', 'DFS Online')); ?>
        </h4>

        <p class="footer-text" data-i18n="footer.social.text">
          <?php echo esc_html($footer_t('footer.social.text', 'Bleib mit uns verbunden.')); ?>
        </p>

        <ul class="social-list">
          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="<?php echo esc_attr($footer_t('footer.social.instagram', 'Instagram')); ?>"
              data-i18n="footer.social.instagram"
              data-i18n-attr="aria-label"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'instagram.svg'); ?>')"
            ></a>
          </li>

          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="<?php echo esc_attr($footer_t('footer.social.facebook', 'Facebook')); ?>"
              data-i18n="footer.social.facebook"
              data-i18n-attr="aria-label"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'facebook.svg'); ?>')"
            ></a>
          </li>

          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="<?php echo esc_attr($footer_t('footer.social.youtube', 'YouTube')); ?>"
              data-i18n="footer.social.youtube"
              data-i18n-attr="aria-label"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'youtube.svg'); ?>')"
            ></a>
          </li>

          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="<?php echo esc_attr($footer_t('footer.social.tiktok', 'TikTok')); ?>"
              data-i18n="footer.social.tiktok"
              data-i18n-attr="aria-label"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'tiktok.svg'); ?>')"
            ></a>
          </li>
        </ul>
      </section>

      <section class="footer-column footer-locations">
        <div class="footer-kicker">
          <img
            src="<?php echo esc_url($footer_img . 'footer-region.svg'); ?>"
            alt=""
            loading="lazy"
          >
          <span data-i18n="footer.region.kicker">
            <?php echo esc_html($footer_t('footer.region.kicker', 'Aktiv in der Region')); ?>
          </span>
        </div>

        <h4 class="footer-head">
          <a href="<?php echo esc_url($fr_url); ?>" data-i18n="footer.region.title">
            <?php echo esc_html($footer_t('footer.region.title', 'Standorte & Einsatzgebiet')); ?>
          </a>
        </h4>

        <p class="footer-text" data-i18n="footer.region.text">
          <?php echo esc_html($footer_t('footer.region.text', 'Dortmund · NRW · Partnervereine')); ?>
        </p>

        <a
          class="footer-region-card"
          href="<?php echo esc_url($fr_url); ?>"
          aria-label="<?php echo esc_attr($footer_t('footer.region.cardAria', 'Standorte ansehen')); ?>"
          data-i18n="footer.region.cardAria"
          data-i18n-attr="aria-label"
        >
          <img
            src="<?php echo esc_url($offers_img . 'world-map.png'); ?>"
            alt="<?php echo esc_attr($footer_t('footer.region.mapAlt', 'Standorte & Einsatzgebiet')); ?>"
            data-i18n="footer.region.mapAlt"
            data-i18n-attr="alt"
            loading="lazy"
          >
        </a>
      </section>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <div class="copyright-wrap">
        <small>
          © <?php echo esc_html(date('Y')); ?>
          <span data-i18n="footer.bottom.brand">
            <?php echo esc_html($footer_t('footer.bottom.brand', 'Dortmunder Fussballschule')); ?>
          </span>
        </small>

        <?php if ($footer_logo_url): ?>
          <img
            class="footer-mini-logo"
            src="<?php echo esc_url($footer_logo_url); ?>"
            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
            loading="lazy"
          >
        <?php endif; ?>
      </div>

      <a
        class="anbieter-login"
        href="<?php echo esc_url($provider_login_url); ?>"
        target="_blank"
        rel="noopener"
      >
        <img
          src="<?php echo esc_url($footer_img . 'footer-login.svg'); ?>"
          alt=""
          loading="lazy"
        >
        <span data-i18n="footer.bottom.providerLogin">
          <?php echo esc_html($footer_t('footer.bottom.providerLogin', 'Anbieter-Login')); ?>
        </span>
      </a>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>

<button
  class="ks-back-top"
  type="button"
  aria-label="<?php echo esc_attr($common_t('common.backToTop', 'Nach oben scrollen')); ?>"
  data-i18n="common.backToTop"
  data-i18n-attr="aria-label"
>
  <span class="ks-back-top__icon" aria-hidden="true">
    <img
      src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/home/back-to-top.svg'); ?>"
      alt=""
      loading="lazy"
      decoding="async"
    >
  </span>
</button>
</body>
</html>






