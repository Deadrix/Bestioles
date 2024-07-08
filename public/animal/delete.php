<?php

session_start();
if (!isset($_SESSION['token'])) {
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["id"])) {
    $id = $_POST["id"];
    $url = "http://localhost:8080/api/animal/" . $id;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    header('Content-Type: application/json');
    if ($response !== false && $httpStatusCode == 200) {
        http_response_code(200);
    } else if ($httpStatusCode == 400) {
        http_response_code(400);
        echo json_encode(json_decode($response, true)["message"]);
    } else {
        http_response_code(500);
    }
    exit();
}