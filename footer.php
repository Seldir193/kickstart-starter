<?php // schließt den Hauptinhalt ?>
</main>

<?php
  // Logo-URL aus dem Customizer für das kleine Logo unten
  $custom_logo_id  = get_theme_mod('custom_logo');
  $footer_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';
  // Basis-Pfad für Icons/Bilder (Weltkarte etc.)
  $asset_base = get_stylesheet_directory_uri() . '/assets/img/offers/';
?>

<footer class="site-footer" role="contentinfo"
        style="--plus-icon:url('<?php echo esc_url( $asset_base . 'plus.png' ); ?>')">

  <!-- OBERER BEREICH – 4 Spalten -->
  <div class="footer-top">
    <div class="container footer-grid">

      <!-- 1) Kontakt -->
      <section class="footer-contact">
        <h4 class="footer-head">Kontakt</h4>
        <ul class="footer-list">
          <li class="with-icon">
            <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($asset_base . 'location.png'); ?>')"></span>
            <span class="text">Dortmund &amp; Umgebung</span>
          </li>
          <li class="with-icon">
            <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($asset_base . 'mail.png'); ?>')"></span>
            <a class="text link" href="mailto:info@dortmunder-fussballschule.de">info@dortmunder-fussballschule.de</a>
          </li>
          <li class="with-icon">
            <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($asset_base . 'phone.png'); ?>')"></span>
            <a class="text link" href="tel:+4917643203362">+49 176 4320 3362</a>
          </li>
        </ul>
      </section>

      <!-- 2) Quicklinks -->
      <nav class="footer-links" aria-label="Quicklinks">
        <h4 class="footer-head">Quicklinks</h4>
        <?php
          if (has_nav_menu('footer')) {
            wp_nav_menu([
              'theme_location' => 'footer',
              'container'      => false,
              'menu_class'     => 'footer-menu with-plus',
              'fallback_cb'    => false,
              'depth'          => 1,
            ]);
          } else {
            echo '<ul class="footer-menu with-plus">
                    <li><a href="/dfsmanager">DFSMAMAGER</a></li>
                    <li><a href="/impressum">Impressum</a></li>
                    <li><a href="/datenschutz">Datenschutz</a></li>
                    <li><a href="/agb">AGB</a></li>
                    <li><a href="/faq">FAQ</a></li>
                  </ul>';
          }
        ?>
      </nav>

  


      <!-- 3) Folge uns -->
<section class="footer-social">
  <h4 class="footer-head">Folge uns</h4>
  <ul class="social-list">
    <li>
      <a class="social-icon"
         href="#"
         aria-label="Instagram"
         rel="noopener"
         target="_blank"
         style="--icon:url('<?php echo esc_url( $asset_base . 'instagram.svg' ); ?>')">
      </a>
    </li>
    <li>
      <a class="social-icon"
         href="#"
         aria-label="Facebook"
         rel="noopener"
         target="_blank"
         style="--icon:url('<?php echo esc_url( $asset_base . 'facebook.svg' ); ?>')">
      </a>
    </li>
    <li>
      <a class="social-icon"
         href="#"
         aria-label="YouTube"
         rel="noopener"
         target="_blank"
         style="--icon:url('<?php echo esc_url( $asset_base . 'youtube.svg' ); ?>')">
      </a>
    </li>
    <li>
      <a class="social-icon"
         href="#"
         aria-label="TikTok"
         rel="noopener"
         target="_blank"
         style="--icon:url('<?php echo esc_url( $asset_base . 'tiktok.svg' ); ?>')">
      </a>
    </li>
  </ul>
</section>


      <!-- 4) Unsere Standorte (Weltkarte klickbar) -->
      <section class="footer-locations">
        <h4 class="footer-head">
          <a class="world-title" href="<?php echo esc_url( site_url('/standorte') ); ?>">Unsere Standorte</a>
        </h4>
        <a class="world-link" href="<?php echo esc_url( site_url('/standorte') ); ?>" aria-label="Standorte ansehen">
          <img class="world-img" src="<?php echo esc_url( $asset_base . 'world-map.png' ); ?>" alt="Weltkarte – Standorte" loading="lazy">
        </a>
      </section>

    </div><!-- /.container.footer-grid -->

      <!-- UNTERER BEREICH – zentriert; Logo direkt hinter dem Text -->
  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <div class="copyright-wrap">
        <small>© <?php echo date('Y'); ?> Dortmunder Fussballschule</small>
        <?php if ($footer_logo_url): ?>
          <img class="footer-mini-logo" src="<?php echo esc_url($footer_logo_url); ?>"
               alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php endif; ?>
      </div>

      <small class="anbieter-login">
        <a href="http://localhost:3000/admin/login" target="_blank" rel="noopener">Anbieter-Login</a>
      </small>
    </div>
  </div>
  </div><!-- /.footer-top -->





</footer>

<?php wp_footer(); ?>
</body>
</html>













