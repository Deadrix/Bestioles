<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/Specie.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['id'])) {

    $id = $_GET['id'];

    $animal = new Animal();
    if (!$animal->fromDB($_GET['id'])) $error_message = "Oups... Impossible de créer l'animal depuis son ID";

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
    && !empty($_POST['id'])
    && !empty($_POST['name'])
    && !empty($_POST['color'])
    && !empty($_POST['sex'])
    && !empty($_POST['speciesId'])
) {

    $allSpecies = json_decode($_SESSION['allSpecies']);

    $id = $_POST['id'];
    $name = $_POST['name'];
    $color = $_POST['color'];
    $sex = $_POST['sex'];

    $animal = new Animal($id, $name, $color, $sex);
    foreach ($allSpecies as $specie) {
        $specieObject = new Specie();
        $specieObject->fromJson(json_encode($specie));
        if ($specieObject->getId() == $_POST['speciesId']) {
            $animal->setSpecie($specieObject);
            break;
        }
    }

    $error_message = "";

    if (strlen($name) > 50) {
        $error_message .= "Le prénom commun doit contenir au maximum 50 caractères. <br>";
    }

    if (strlen($color) > 50) {
        $error_message .= "La couleur doit contenir au maximum 50 caractères. <br>";
    }

    if ($sex !== "M" && $sex !== "F") {
        $error_message .= "Le sexe doit être exprimé avec M et F. <br>";
    }

    if (empty($error_message)) {
        $url = "http://localhost:8080/api/animal/" . $animal->getId();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token'], 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $animal->toJson());
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            header('Location: /public/animal/visualisation.php?id=' . $id);
            exit();
        } else {
            $error_message = "Oups... Impossible de sauvegarder l'animal.";
        }
    }
} else {
    header('Location: /public/animal/listing.php');
    exit();
}


?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Edition d'un animal</h1>
    <form action="" method="post" class="w-full flex flex-col items-center justify-center gap-5">
        <input type="hidden" name="id" value="<?php echo $animal->getId(); ?>">
        <div class="w-1/2 flex flex-col gap-3">
            <div class="px-4 py-5">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
            </div>
            <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="name" class="text-sm font-medium text-gray-500">Nom</label>
                    <input type="text" id="name" name="name"
                           value="<?php echo $animal->getName(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="color" class="text-sm font-medium text-gray-500">Couleur</label>
                    <input type="text" id="color" name="color"
                           value="<?php echo $animal->getColor(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="sex" class="text-sm font-medium text-gray-500">Sexe</label>
                    <select name="sex" id="sex" class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                        <option value="M" <?php if ($animal->getSex() === "M") echo "selected"; ?>>Mâle</option>
                        <option value="F" <?php if ($animal->getSex() === "F") echo "selected"; ?>>Femelle</option>
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
                            <option value='<?php echo $specieObject->getId() ?>'
                                <?php if ($animal->getSpecie()->getId() === $specieObject->getId()) echo "selected"; ?>>
                                <?php echo $specieObject->getCommonName(); ?>
                            </option>
                        <?php } ?>
                    </select>
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
                <a href="/public/animal/visualisation.php?id=<?php echo $animal->getId(); ?>"
                   class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>