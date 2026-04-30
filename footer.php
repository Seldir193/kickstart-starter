</main>

<?php
$custom_logo_id = get_theme_mod('custom_logo');
$footer_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';

$theme_uri = get_stylesheet_directory_uri();
$offers_img = $theme_uri . '/assets/img/offers/';
$footer_img = $theme_uri . '/assets/img/footer/';

$fr_page = get_page_by_path('franchise');
$fr_url = $fr_page ? get_permalink($fr_page->ID) : site_url('/franchise/');
$fr_url .= '#fr-worldwide-map';

$dfsmanager_url = 'https://selcuk-kocyigit.de/';
$provider_login_url = 'http://localhost:3000/admin/login';
?>

<footer
  class="site-footer"
  role="contentinfo"
  style="--footer-link-icon:url('<?php echo esc_url($theme_uri . '/assets/img/team/arrow_right_alt.svg'); ?>')"
>
  <div class="footer-top">
    <div class="container footer-grid">
      <section class="footer-column footer-contact">
        <div class="footer-kicker">
          <img src="<?php echo esc_url($footer_img . 'footer-contact.svg'); ?>" alt="" loading="lazy">
          <span>Wir sind für dich da</span>
        </div>

        <h4 class="footer-head">Kontakt</h4>

        <ul class="footer-list">
          <li class="footer-contact-item">
            <span class="footer-contact-icon" style="--icon:url('<?php echo esc_url($offers_img . 'location.png'); ?>')"></span>
            
            <a href="<?php echo esc_url($fr_url); ?>">Dortmund &amp; Umgebung</a>
          </li>

          <li class="footer-contact-item">
            <span class="footer-contact-icon" style="--icon:url('<?php echo esc_url($offers_img . 'mail.png'); ?>')"></span>
            <a href="mailto:info@dortmunder-fussballschule.de">info@dortmunder-fussballschule.de</a>
          </li>

          <li class="footer-contact-item">
            <span class="footer-contact-icon" style="--icon:url('<?php echo esc_url($offers_img . 'phone.png'); ?>')"></span>
            <a href="tel:+4917643203362">+49 176 4320 3362</a>
          </li>
        </ul>
      </section>

      <nav class="footer-column footer-links" aria-label="Quicklinks">
        <div class="footer-kicker">
          <img src="<?php echo esc_url($footer_img . 'footer-links.svg'); ?>" alt="" loading="lazy">
          <span>Wichtige Links</span>
        </div>

        <h4 class="footer-head">Service</h4>

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
              <a href="<?php echo esc_url($dfsmanager_url); ?>" target="_blank" rel="noopener">
                DFS Manager
              </a>
            </li>
            <li><a href="<?php echo esc_url(site_url('/abo-kuendigen/')); ?>">Abo kündigen</a></li>
            <li><a href="<?php echo esc_url(site_url('/widerrufen/')); ?>">Vertrag widerrufen</a></li>
            <li>
  <a href="<?php echo esc_url(site_url('/?page_id=143')); ?>">Hilfe &amp; Kontakt</a>
</li>
            <li><a href="<?php echo esc_url(site_url('/impressum/')); ?>">Impressum</a></li>
            <li><a href="<?php echo esc_url(site_url('/datenschutz/')); ?>">Datenschutz</a></li>
            <li><a href="<?php echo esc_url(site_url('/agb/')); ?>">AGB</a></li>
          </ul>
        <?php endif; ?>
      </nav>

      <section class="footer-column footer-social">
        <div class="footer-kicker">
          <img src="<?php echo esc_url($footer_img . 'footer-social.svg'); ?>" alt="" loading="lazy">
          <span>Bleib verbunden</span>
        </div>

        <h4 class="footer-head">DFS Online</h4>

        <p class="footer-text">Bleib mit uns verbunden.</p>

        <ul class="social-list">
          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="Instagram"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'instagram.svg'); ?>')"
            ></a>
          </li>
          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="Facebook"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'facebook.svg'); ?>')"
            ></a>
          </li>
          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="YouTube"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'youtube.svg'); ?>')"
            ></a>
          </li>
          <li>
            <a
              class="social-icon"
              href="#"
              aria-label="TikTok"
              target="_blank"
              rel="noopener"
              style="--icon:url('<?php echo esc_url($offers_img . 'tiktok.svg'); ?>')"
            ></a>
          </li>
        </ul>
      </section>

      <section class="footer-column footer-locations">
        <div class="footer-kicker">
          <img src="<?php echo esc_url($footer_img . 'footer-region.svg'); ?>" alt="" loading="lazy">
          <span>Aktiv in der Region</span>
        </div>

        <h4 class="footer-head">
          <a href="<?php echo esc_url($fr_url); ?>">Standorte &amp; Einsatzgebiet</a>
        </h4>

        <p class="footer-text">Dortmund · NRW · Partnervereine</p>

        <a class="footer-region-card" href="<?php echo esc_url($fr_url); ?>" aria-label="Standorte ansehen">
          <img src="<?php echo esc_url($offers_img . 'world-map.png'); ?>" alt="Standorte & Einsatzgebiet" loading="lazy">
        </a>
      </section>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <div class="copyright-wrap">
        <small>© <?php echo esc_html(date('Y')); ?> Dortmunder Fussballschule</small>

        <?php if ($footer_logo_url): ?>
          <img
            class="footer-mini-logo"
            src="<?php echo esc_url($footer_logo_url); ?>"
            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
            loading="lazy"
          >
        <?php endif; ?>
      </div>

      <a class="anbieter-login" href="<?php echo esc_url($provider_login_url); ?>" target="_blank" rel="noopener">
        <img src="<?php echo esc_url($footer_img . 'footer-login.svg'); ?>" alt="" loading="lazy">
        <span>Anbieter-Login</span>
      </a>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
















