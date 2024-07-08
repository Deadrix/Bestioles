<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = $_POST["user"];
    $password = $_POST["password"];

    $url = "http://localhost:8080/auth";
    $body = [
        "username" => $user,
        "password" => $password
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpStatusCode == 200) {
        $response = json_decode($response, true);
        session_start();
        $_SESSION["token"] = $response["id_token"];
        header("Location: /public/home.php");
    } else {
        $error_message = "Oups... Identifiants incorrects.";
    }
}
?>

<?php
echo file_get_contents(__DIR__ . "/header.php");
?>

    <div class="flex-1 flex flex-col items-center justify-center">
        <form class="max-w-sm mx-auto flex flex-col gap-5" method="post" action="login.php">
            <p id="helper-text-explanation"
               class="mt-2 text-sm text-red-500"><?php if (isset($error_message)) echo $error_message; ?></p>
            <div class="mb-5">
                <label for="user" class="block mb-2 text-sm font-medium text-gray-900">Utilisateur</label>
                <input type="text" id="user" name="user"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                       placeholder="admin" required/>
            </div>
            <div class="mb-5">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Mot de passe</label>
                <input type="password" id="password" name="password"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                       required/>
            </div>
            <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                Se connecter
            </button>
        </form>
    </div>

<?php
echo file_get_contents(__DIR__ . "/footer.php");
?>