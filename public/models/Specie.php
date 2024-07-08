<?php

class Specie implements JsonSerializable
{
    private int $id;
    private string $commonName;
    private string $latinName;

    public function __construct($id = 0, $commonName = '', $latinName = '')
    {
        $this->id = $id;
        $this->commonName = $commonName;
        $this->latinName = $latinName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getCommonName(): string
    {
        return $this->commonName;
    }

    public function setCommonName($commonName): void
    {
        $this->commonName = $commonName;
    }

    public function getLatinName(): string
    {
        return $this->latinName;
    }

    public function setLatinName($latinName): void
    {
        $this->latinName = $latinName;
    }

    public function fromDB($id): bool
    {
        $url = "http://localhost:8080/api/species/" . $id;

        require_once __DIR__ . '/../models/Specie.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            $response = json_decode($response, true);
            $this->id = $id;
            $this->commonName = $response["commonName"];
            $this->latinName = $response["latinName"];
            return true;
        } else {
            return false;
        }
    }

    public function fromJson($json): void
    {
        $data = json_decode($json, true);
        $this->id = $data['id'];
        $this->commonName = $data['commonName'];
        $this->latinName = $data['latinName'];
    }

    public function toJson(): false|string
    {
        return json_encode([
            'id' => $this->id,
            'commonName' => $this->commonName,
            'latinName' => $this->latinName,
        ]);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'commonName' => $this->commonName,
            'latinName' => $this->latinName,
        ];
    }
}