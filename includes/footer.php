<footer class="main-footer">
    <p>© <span id="year"></span> Fly With Us. Të gjitha të drejtat e rezervuara.</p>
    <p class="sub-footer">
    <a href="<?= $GLOBALS['base_url'] ?>/pages/contact.php">Kontakti</a> |
    <a href="<?= $GLOBALS['base_url'] ?>/pages/faq.php">FAQ</a>
</p>
</footer>

<script>
    const prices = <?= json_encode($flightMatrix ?? []) ?>;
</script>
<script src="<?= $GLOBALS['base_url'] ?>/assets/js/script.js"></script>

</body>
</html>