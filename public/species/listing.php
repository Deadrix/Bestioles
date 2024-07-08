<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Specie.php';

if (!empty($_POST['contains'])) {
    $contains = $_POST['contains'];
    $url = "http://localhost:8080/api/species?contains=" . urlencode($contains);
} else {
    $url = "http://localhost:8080/api/species";
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
$response = curl_exec($ch);
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $httpStatusCode == 200) {
    $species = json_decode($response, true);
} else {
    $error_message = "Oups... Impossible de récupérer les espèces.";
}

?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="w-full flex flex-col gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Liste des espèces</h1>
    <div id="tableHeader" class="flex items-center gap-5 w-full">
        <form action="" method="post" class="flex-1">
            <div class="flex gap-5">
                <label for="filterInput" class="flex-1 flex">
                    <input type="text" id="filterInput" name="contains" placeholder="Filtrer par nom..."
                           class="border rounded-lg flex-1 px-2">
                </label>
                <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Filtrer
                </button>
            </div>
        </form>
        <div class="flex gap-5">
            <a href="/public/species/listing.php"
               class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Supprimer le filtre
            </a>
            <a href="/public/species/creation.php"
               class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Ajouter une espèce
            </a>
        </div>
    </div>
    <div class="mt-5">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom commun
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nom latin
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php if (isset($species)) {
                foreach ($species as $specie) {
                    $specieObject = new Specie();
                    $specieObject->fromJson(json_encode($specie));
                    ?>
                    <tr data-id="<?php echo $specieObject->getId() ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $specieObject->getCommonName(); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $specieObject->getLatinName(); ?></div>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($error_message)) { ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
             role="alert">
            <p><strong class="font-bold">Erreur !</strong></p>
            <p><?php echo $error_message; ?></p>
        </div>
    <?php } ?>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            row.addEventListener('mouseover', function () {
                this.style.cursor = 'pointer';
            });
            row.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                window.location.href = `/public/species/visualisation.php?id=${id}`;
            });
        });
    });
</script>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>