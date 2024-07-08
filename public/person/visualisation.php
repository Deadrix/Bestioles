<?php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: /public/login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET" || empty($_GET['id'])) {
    header('Location: /public/person/listing.php');
    exit();
}

$id = $_GET['id'];
require_once __DIR__ . '/../models/Person.php';

$person = new Person();
if (!$person->fromDB($_GET['id'])) $error_message = "Oups... Impossible de créer la personne depuis son ID";

?>

<?php echo file_get_contents(__DIR__ . "/../header.php"); ?>

<div class="flex-1 w-full px-4 flex flex-col items-center justify-center gap-5">
    <h1 class="text-3xl font-bold text-gray-900">Visualisation d'une personne</h1>
    <div class="w-1/2 flex flex-col gap-3">
        <div class="px-4 py-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Informations</h3>
        </div>
        <div class="border-t border-gray-200 flex flex-col gap-5">
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Nom</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $person->getLastName(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Prénom</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $person->getFirstName(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="text-sm font-medium text-gray-500">Age</p>
                <p class="mt-1 text-sm text-gray-900 col-span-2 p-2"><?php echo $person->getAge(); ?></p>
            </div>
            <div class="px-4 grid grid-cols-3 gap-3 items-center">
                <p class="self-start text-sm font-medium text-gray-500">Animaux</p>
                <div>
                    <?php
                    $animals = $person->getAnimals();
                    if (empty($animals)) {
                        echo("<p class='mt-1 text-sm text-gray-900'>Aucun animal</p>");
                    } else {
                        foreach ($animals as $animal) {
                            echo("<p class='mt-1 text-sm text-gray-900'>" . $animal->getName() . "</p>");
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

        <div class="mt-5 flex gap-3">
            <a href="/public/person/listing.php"
               class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Retour
            </a>

            <a href="/public/person/edition.php?id=<?php echo $person->getId(); ?>"
               class="flex-1 text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Modifier
            </a>

            <button id="deleteButton" data-id="<?php echo $person->getId(); ?>"
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
            const confirmed = confirm("Êtes-vous sûr de vouloir supprimer cette personne ?");
            if (confirmed) {
                const id = this.getAttribute('data-id');

                fetch('/public/person/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`,
                }).then(response => {
                    if (response.status === 200) {
                        alert("La personne a été supprimée avec succès.");
                        window.location.href = '/public/person/listing.php';
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
