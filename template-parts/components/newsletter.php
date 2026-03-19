


<?php
/**
 * Template Part: Newsletter
 * Wird via Shortcode [ks_newsletter] gerendert.
 * JS bleibt unverändert (ks-newsletter.js).
 */
?>

<section id="ksNewsletter" class="ks-newsletter-wrap" aria-label="Newsletter">
  <div class="newsletter-box">
    <div class="ks-kicker">NEWSLETTER</div>

    <h3>Newsletter</h3>
    <p>
      Jetzt kostenlos Newsletter informieren. Siehe aktuelle News und Angebote der Münchner
      Fußballschule. Du kannst wenn du möchtest jederzeit nicht an News teilnehmen. Eine
      Abmeldung ist jederzeit möglich.
    </p>

    <form id="ksNewsletterForm" class="ks-news-form" novalidate>
      <div class="newsletter-row">
       

        <!-- Honeypot (Spam-Schutz) -->
<label class="hp" aria-hidden="true">
  Nicht ausfüllen
  <input type="text" name="website" tabindex="-1" autocomplete="off">
</label>


        <!-- Feld: gleiche Struktur/Klassen wie Directory-Controls -->
        <label class="ks-field">
          <span>E-Mail</span>
          <div class="ks-field__control ks-field__control--select">
            <input
              id="ksNewsletterEmail"
              type="email"
              name="email"
              inputmode="email"
              autocomplete="email"
              placeholder="Ihre E-Mail"
              required
            >
          </div>
        </label>

        <!-- Button: wie Home/Offers -->
        <button type="submit" class="ks-btn ks-btn--dark">
          Anmelden
        </button>
      </div>

      <span id="ksNewsletterMsg" class="ks-news-msg" role="status" aria-live="polite"></span>
    </form>
  </div>
</section>













