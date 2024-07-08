<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Specie.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $url = "http://localhost:8080/api/species/" . $id;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpStatusCode == 200) {
        $specie = new Specie();
        $specie->fromJson($response);
    } else {
        $error_message = "Oups... Une erreur est survenue.";
        exit();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST"
    && !empty($_POST['id'])
    && !empty($_POST['commonName'])
    && !empty($_POST['latinName'])) {

    $id = $_POST['id'];
    $commonName = $_POST['commonName'];
    $latinName = $_POST['latinName'];

    $specie = new Specie($id, $commonName, $latinName);

    $error_message = "";

    if (strlen($commonName) > 50) {
        $error_message .= "Le nom commun doit contenir au maximum 50 caractères. <br>";
    }

    if (strlen($latinName) > 200) {
        $error_message .= "Le nom latin doit contenir au maximum 200 caractères. <br>";
    }

    if (empty($error_message)) {
        $url = "http://localhost:8080/api/species/" . $specie->getId();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token'], 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $specie->toJson());
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            header('Location: /public/species/visualisation.php?id=' . $specie->getId());
            exit();
        } else {
            $error_message = "Oups... Une erreur est survenue.";
        }
    }
} else {
    header('Location: /public/species/listing.php');
    exit();
}


?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Edition d'une espèce</h1>
    <form action="" method="post" class="w-full flex flex-col items-center justify-center gap-5">
        <input type="hidden" name="id" value="<?php echo $specie->getId(); ?>">
        <div class="w-1/2 flex flex-col gap-3">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
            </div>
            <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="commonName" class="text-sm font-medium text-gray-500">Nom commun</label>
                    <input type="text" id="commonName" name="commonName" value="<?php echo $specie->getCommonName(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="latinName" class="text-sm font-medium text-gray-500">Nom latin</label>
                    <input type="text" id="latinName" name="latinName" value="<?php echo $specie->getLatinName(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
            </div>

            <?php if (!empty($error_message)) { ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                     role="alert">
                    <p><strong class="font-bold">Erreur !</strong></p>
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php } ?>

            <div class="mt-5 flex gap-3">
                <button type="submit"
                        class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Enregistrer
                </button>
                <a href="/public/species/visualisation.php?id=<?php echo $specie->getId(); ?>"
                   class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>