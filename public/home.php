<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}
?>

<?php echo file_get_contents(__DIR__ . "/header.php"); ?>

<div class="flex-1 grid grid-cols-3 gap-3 w-full">
    <a href="species/listing.php" class="rounded-2xl border border-black flex items-center justify-center">Espèces</a>
    <a href="animal/listing.php" class="rounded-2xl border border-black flex items-center justify-center">Animaux</a>
    <a href="person/listing.php" class="rounded-2xl border border-black flex items-center justify-center">Personnes</a>
</div>

<?php echo file_get_contents(__DIR__ . "/footer.php"); ?>