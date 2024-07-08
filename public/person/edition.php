<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

require_once __DIR__ . '/../models/Person.php';
require_once __DIR__ . '/../models/Animal.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['id'])) {

    $id = $_GET['id'];

    $person = new Person();
    if (!$person->fromDB($_GET['id'])) $error_message = "Oups... Impossible de créer la personne depuis son ID";

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
    && !empty($_POST['id'])
    && !empty($_POST['firstName'])
    && !empty($_POST['lastName'])
    && !empty($_POST['age'])
    && !empty($_POST['animalsId'])
) {

    $allAnimals = json_decode($_SESSION['allAnimals'], true);

    $id = $_POST['id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $age = $_POST['age'];
    $animalsId = $_POST['animalsId'];

    print_r($animalsId);

    $person = new Person($id, $firstName, $lastName, $age);
    foreach ($allAnimals as $animal) {
        $animalObject = new Animal();
        $animalObject->fromDB($animal['id']);
        if (in_array($animalObject->getId(), $animalsId)) {
            $person->addAnimal($animalObject);
        }
    }

    $error_message = "";

    if (strlen($firstName) > 50) {
        $error_message .= "Le prénom commun doit contenir au maximum 50 caractères. <br>";
    }

    if (strlen($lastName) > 50) {
        $error_message .= "Le nom doit contenir au maximum 50 caractères. <br>";
    }

    if ($age < 0 || $age > 120) {
        $error_message .= "L'âge doit être compris entre à et 120 ans. <br>";
    }

    if (empty($error_message)) {
        $url = "http://localhost:8080/api/person/" . $person->getId();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token'], 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $person->toJson());
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            header('Location: /public/person/visualisation.php?id=' . $person->getId());
            exit();
        } else {
            $error_message = "Oups... Une erreur est survenue.";
        }
    }
} else {
    header('Location: /public/person/listing.php');
    exit();
}


?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Edition d'une personne</h1>
    <form action="" method="post" class="w-full flex flex-col items-center justify-center gap-5">
        <input type="hidden" name="id" value="<?php echo $person->getId(); ?>">
        <div class="w-1/2 flex flex-col gap-3">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
            </div>
            <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="lastName" class="text-sm font-medium text-gray-500">Nom</label>
                    <input type="text" id="lastName" name="lastName"
                           value="<?php echo $person->getLastName(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="firstName" class="text-sm font-medium text-gray-500">Prénom</label>
                    <input type="text" id="firstName" name="firstName"
                           value="<?php echo $person->getFirstName(); ?>"
                           class="mt-1 text-sm text-gray-900 col-span-2 p-2 border rounded-lg">
                </div>
                <div class="px-4 grid grid-cols-3 gap-3 items-center">
                    <label for="age" class="text-sm font-medium text-gray-500">Age</label>
                    <input type="number" id="age" name="age"
                           value="<?php echo $person->getAge(); ?>"
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
                                       name="animalsId[]" value="<?php echo $animalObject->getId(); ?>"
                                    <?php if (in_array($animalObject->getId(), $person->getAnimalsIds())) echo "checked"; ?>>
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
                <a href="/public/person/visualisation.php?id=<?php echo $person->getId(); ?>"
                   class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>