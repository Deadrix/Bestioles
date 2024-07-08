<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/Specie.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $url = "http://localhost:8080/api/species";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpStatusCode == 200) {
        $allSpecies = json_decode($response, true);
        $_SESSION['allSpecies'] = json_encode($allSpecies);
    } else {
        $error_message = "Oups... Impossible de récupérer les espèces.";
        exit();
    }

} else if ($_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST['name'])
    && isset($_POST['color'])
    && isset($_POST['sex'])
    && isset($_POST['speciesId'])
) {
    $allSpecies = json_decode($_SESSION['allSpecies']);

    $name = $_POST['name'];
    $color = $_POST['color'];
    $sex = $_POST['sex'];
    $speciesId = $_POST['speciesId'];

    $error_message = "";

    if (strlen(trim($name)) === 0) {
        $error_message .= "Le nom commun ne peut pas être vide. <br>";
    }

    if (strlen(trim($name)) > 50) {
        $error_message .= "Le prénom commun doit contenir au maximum 50 caractères. <br>";
    }

    if (strlen(trim($color)) === 0) {
        $error_message .= "La couleur ne peut pas être vide. <br>";
    }

    if (strlen(trim($color)) > 50) {
        $error_message .= "La couleur doit contenir au maximum 50 caractères. <br>";
    }

    if ($sex !== "M" && $sex !== "F") {
        $error_message .= "Le sexe doit être exprimé avec M et F. <br>";
    }

    if (strlen($speciesId) === 0) {
        $error_message .= "L'espèce ne peut pas être vide. <br>";
    }

    if (empty($error_message)) {
        $url = "http://localhost:8080/api/animal";

        $specie = new Specie();
        $specie->fromDB($speciesId);

        $data = [
            'name' => $name,
            'color' => $color,
            'sex' => $sex,
            "species" => $specie
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $_SESSION['token'],
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            $response = json_decode($response, true);
            header('Location: /public/animal/visualisation.php?id=' . $response['id']);
            exit();
        } else {
            echo $response;
        }
    }
} else {
    $allSpecies = json_decode($_SESSION['allSpecies']);
    $error_message = "Tous les champs sont obligatoires.";
}


?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

    <div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
        <h1 class="text-3xl font-bold text-gray-900">Création d'un animal</h1>
        <form action="" method="post" class="w-full flex flex-col items-center justify-center gap-5">
            <div class="w-1/2 flex flex-col gap-3">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
                </div>
                <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="name" class="text-sm font-medium text-gray-500">Nom</label>
                        <input type="text" id="name" name="name"
                               value="<?php echo $_POST['name'] ?? '' ?>"
                               class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="color" class="text-sm font-medium text-gray-500">Couleur</label>
                        <input type="text" id="color" name="color"
                               value="<?php echo $_POST['color'] ?? '' ?>"
                               class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="sex" class="text-sm font-medium text-gray-500">Sexe</label>
                        <select name="sex" id="sex" class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                            <option value="M" <?php if (!empty($_POST['sex']) && $_POST['sex'] == 'M') echo 'selected' ?>>
                                Mâle
                            </option>
                            <option value="F" <?php if (!empty($_POST['sex']) && $_POST['sex'] == 'F') echo 'selected' ?> >
                                Femelle
                            </option>
                        </select>
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="speciesId" class="text-sm font-medium text-gray-500">Espèce</label>
                        <select name="speciesId" id="speciesId"
                                class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                            <?php
                            foreach ($allSpecies as $specie) {
                                $specieObject = new Specie();
                                $specieObject->fromJson(json_encode($specie));
                                ?>
                                <option value='<?php echo $specieObject->getId() ?>' <?php if (!empty($_POST['speciesId']) && $_POST['speciesId'] == $specieObject->getId()) echo 'selected' ?> >
                                    <?php echo $specieObject->getCommonName() ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <?php if (!empty($error_message)) { ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
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
                    <a href="/public/animal/listing.php"
                       class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Annuler
                    </a>
                </div>
            </div>
        </form>
    </div>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>