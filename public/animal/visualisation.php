<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET" || empty($_GET['id'])) {
    header('Location: /public/animal/listing.php');
    exit();
}

$id = $_GET['id'];
require_once __DIR__ . '/../models/Animal.php';

$animal = new Animal();
if (!$animal->fromDB($_GET['id'])) $error_message = "Oups... Impossible de créer l'animal depuis son ID";

?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Visualisation d'un animal</h1>
    <div class="w-1/2 flex flex-col gap-3">
        <div class="px-4 py-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
        </div>
        <div class="border-t pt-5 border-gray-200 flex flex-col gap-5">
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Nom</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $animal->getName(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Couleur</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $animal->getColor(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Sexe</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2">
                    <?php
                    if ($animal->getSex() === "M") {
                        echo "Mâle";
                    } else if ($animal->getSex() === "F") {
                        echo "Femelle";
                    } else {
                        echo "Inconnu";
                    }
                    ?>
                </p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Espèce</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $animal->getSpecie()->getCommonName(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Maîtres</p>
                <div>
                    <?php
                    $persons = $animal->getPersons();
                    if (empty($persons)) {
                        echo("<p class='mt-1 text-sm text-gray-900 col-span-2 p-2'>Aucun maître</p>");
                    } else {
                        foreach ($persons as $person) {
                            echo("<p class='mt-1 text-sm text-gray-900 col-span-2 p-2'>" . $person->getFirstName() . "</p>");
                        }
                    }
                    ?>
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

        <div class="flex gap-3">
            <a href="/public/animal/listing.php"
               class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Retour
            </a>

            <a href="/public/animal/edition.php?id=<?php echo $animal->getId(); ?>"
               class="flex-1 text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Modifier
            </a>

            <button id="deleteButton" data-id="<?php echo $animal->getId(); ?>"
                    class="flex-1 text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Supprimer
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const deleteButton = document.querySelector('#deleteButton');

        deleteButton.addEventListener('click', function () {
            const confirmed = confirm("Êtes-vous sûr de vouloir supprimer cet animal ?");
            if (confirmed) {
                const id = this.getAttribute('data-id');

                fetch('/public/animal/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`,
                }).then(response => {
                    if (response.status === 200) {
                        alert("L'animal a été supprimée avec succès.");
                        window.location.href = '/public/animal/listing.php';
                    } else if (response.status === 400) {
                        response.json().then(data => alert(data));
                    } else {
                        alert('Une erreur inconnue est survenue.');
                    }
                });
            }
        });
    });
</script>

<?php echo file_get_contents(__DIR__ . "/../footer.php"); ?>
