<footer class="main-footer">
    <p>&copy; <span id="year"></span> Fly With Us. Te gjitha te drejtat e rezervuara.</p>
    <p class="sub-footer">
        <a href="<?= $GLOBALS['base_url'] ?>/pages/contact.php">Kontakti</a> |
        <a href="<?= $GLOBALS['base_url'] ?>/pages/faq.php">FAQ</a>
    </p>
</footer>

<script>
    window.appBaseUrl = <?= json_encode($GLOBALS['base_url'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    window.bookingPrices = <?= json_encode($bookingPrices ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    window.bookingDestinations = <?= json_encode($bookingDestinations ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="<?= $GLOBALS['base_url'] ?>/assets/js/script.js"></script>

</body>
</html>