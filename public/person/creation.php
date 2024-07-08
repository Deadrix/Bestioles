<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/Animal.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $url = "http://localhost:8080/api/animal";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpStatusCode == 200) {
        $allAnimals = json_decode($response, true);
        $_SESSION['allAnimals'] = json_encode($allAnimals);
    } else {
        $error_message = "Oups... Impossible de récupérer les animaux.";
        exit();
    }

} else if ($_SERVER["REQUEST_METHOD"] == "POST"
    && !empty($_POST['firstName'])
    && !empty($_POST['lastName'])
    && !empty($_POST['age'])
    && !empty($_POST['animalsId'])
) {

    $allAnimals = json_decode($_SESSION['allAnimals']);

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $age = $_POST['age'];
    $animalsId = $_POST['animalsId'];

    $error_message = "";

    if (strlen($firstName) === 0) {
        $error_message .= "Le prénom ne peut pas être vide. <br>";
    }

    if (strlen($firstName) > 50) {
        $error_message .= "Le prénom doit contenir au maximum 50 caractères. <br>";
    }

    if (strlen($lastName) === 0) {
        $error_message .= "Le nom ne peut pas être vide. <br>";
    }

    if (strlen($lastName) > 50) {
        $error_message .= "Le nom doit contenir au maximum 50 caractères. <br>";
    }

    if ($age < 0 || $age > 120) {
        $error_message .= "L'âge doit être compris entre 0 et 120 ans. <br>";
    }

    if (empty($error_message)) {
        $url = "http://localhost:8080/api/person";

        $animals = [];
        foreach ($animalsId as $animalId) {
            $animal = new Animal();
            $animal->fromDb($animalId);
            $animals[] = $animal;
        }

        $data = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'age' => $age,
            'animals' => $animals
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

        if ($response !== false && $httpStatusCode == 201) {
            $response = json_decode($response, true);
            header('Location: /public/person/visualisation.php?id=' . $response['id']);
            exit();
        } else {
            $error_message = "Oups... Une erreur est survenue.";
        }
    }
} else {
    $allAnimals = json_decode($_SESSION['allAnimals']);
    $error_message = "Tous les champs sont obligatoires.";
}


?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

    <div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
        <h1 class="text-3xl font-bold text-gray-900">Création d'une personne</h1>
        <form action="" method="post" class="w-full flex flex-col items-center justify-center gap-5">
            <div class="w-1/2 flex flex-col gap-3">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
                </div>
                <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="lastName" class="text-sm font-medium text-gray-500">Nom</label>
                        <input type="text" id="lastName" name="lastName"
                               value="<?php echo $_POST['lastName'] ?? '' ?>"
                               class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="firstName" class="text-sm font-medium text-gray-500">Prénom</label>
                        <input type="text" id="firstName" name="firstName"
                               value="<?php echo $_POST['firstName'] ?? '' ?>"
                               class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label for="age" class="text-sm font-medium text-gray-500">Age</label>
                        <input type="number" id="age" name="age"
                               value="<?php echo $_POST['age'] ?? '' ?>"
                               min="0" max="120"
                               class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                    </div>
                    <div class="px-4 grid grid-cols-3 gap-3 items-center">
                        <label class="text-sm font-medium text-gray-500">Espèce</label>
                        <div class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                            <?php foreach ($allAnimals as $animal) {
                                $animalObject = new Animal();
                                $animalObject->fromJson(json_encode($animal)); ?>
                                <div>
                                    <input type="checkbox" id="animal<?php echo $animalObject->getId(); ?>"
                                           name="animalsId[]" value="<?php echo $animalObject->getId(); ?>">
                                    <label for="animal<?php echo $animalObject->getId(); ?>">
                                        <?php echo $animalObject->getName() . " " . $animalObject->getSpecie()->getCommonName() . " " . $animalObject->getColor(); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
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
                    <a href="/public/person/listing.php"
                       class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Annuler
                    </a>
                </div>
            </div>
        </form>
    </div>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>