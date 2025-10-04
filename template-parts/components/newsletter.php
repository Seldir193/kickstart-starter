<?php
/**
 * Template Part: Newsletter (nur Markup)
 * JS:  assets/js/ks-newsletter.js  (unverändert)
 * CSS: assets/css/ks-newsletter.css
 */
?>
<section class="ks-newsletter-wrap">
  <div class="container">
    <div id="newsletter" class="newsletter-box" role="region" aria-labelledby="newsletter-title">
      <h3 id="newsletter-title">NEWSLETTER</h3>

      <p>
        Unser kostenloser Newsletter informiert Sie über aktuelle Kurse und Angebote der
        Münchner Fußballschule. Die Daten werden selbstverständlich nicht an Dritte
        weitergegeben. Eine Abmeldung ist jederzeit möglich.
      </p>

      <form id="ksNewsletterForm" class="newsletter-form" novalidate>
        <label for="nlEmail" class="screen-reader-text">Ihre E-Mail</label>

        <div class="newsletter-row">
          <input
            id="nlEmail"
            name="email"
            type="email"
            required
            autocomplete="email"
            placeholder="Ihre E-Mail"
          />
          <!-- Honeypot (muss leer bleiben) -->
          <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" />
          <button type="submit" class="newsletter-btn">Anmelden</button>
        </div>

        <small
          id="ksNewsletterMsg"
          class="ks-news-msg"
          aria-live="polite"
          aria-atomic="true"></small>
      </form>
    </div>
  </div>
</section>


