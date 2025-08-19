<?php
/* 
Template Name: Kontakt (KickStart)
*/
get_header(); 
?>

<div class="container">
  <div class="contact-form">
    <h2>Kontaktformular</h2>
    <p>Schreiben Sie uns – wir melden uns schnellstmöglich zurück.</p>

<?php if ( isset($_GET['sent']) && $_GET['sent'] === '1' ) : ?>
  <div class="ok" >Vielen Dank! Ihre Nachricht wurde gesendet.</div>
  <script>
    (function () {
      var box = document.querySelector('.ok');
      if (!box) return;

      // Meldung nach 5 Sekunden ausblenden/entfernen
      setTimeout(function () {
        if (box && box.parentNode) box.parentNode.removeChild(box);
      }, 5000); // -> für 4 Sekunden: 4000

      // "sent" aus der URL entfernen, damit sie beim Reload nicht wieder auftaucht
      try {
        var url = new URL(window.location.href);
        url.searchParams.delete('sent');
        window.history.replaceState({}, '', url);
      } catch (e) {}
    })();
  </script>
<?php endif; ?>

    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="form">
      <input type="hidden" name="action" value="send_contact_form">
      <?php wp_nonce_field('contact_form_nonce','contact_form_nonce_field'); ?>

      <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required>
      </div>

      <div class="field">
        <label for="email">E-Mail *</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="field">
        <label for="message">Nachricht *</label>
        <textarea id="message" name="message" rows="5" required></textarea>
      </div>

      <button type="submit" class="btn-primary">Nachricht senden</button>
    </form>
  </div>
</div>

<?php get_footer(); ?>











