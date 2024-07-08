<?php

require_once __DIR__ . '/../models/Specie.php';
require_once __DIR__ . '/../models/Person.php';

class Animal implements JsonSerializable
{
    private int $id;
    private string $name;
    private string $color;
    private string $sex;
    private Specie $specie;
    private array $persons;

    public function __construct($id = 0, $name = "", $color = "", $sex = "", $species = new Specie(), $persons = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->sex = $sex;
        $this->setSpecie($species);
        $this->setPersons($persons);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor($color): void
    {
        $this->color = $color;
    }

    public function getSex(): string
    {
        return $this->sex;
    }

    public function setSex($sex): void
    {
        $this->sex = $sex;
    }

    public function getSpecie(): Specie
    {
        return $this->specie;
    }

    public function setSpecie($specie): void
    {
        $this->specie = $specie;
    }

    public function getPersons(): array
    {
        return $this->persons;
    }

    public function setPersons($persons): void
    {
        $this->persons = $persons;
    }

    public function addPerson($person): void
    {
        $this->persons[] = $person;
    }

    public function fromDB($id): bool
    {
        $url = "http://localhost:8080/api/animal/" . $id;

        require_once __DIR__ . '/../models/Specie.php';
        require_once __DIR__ . '/../models/Person.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {
            $response = json_decode($response, true);
            $this->id = $id;
            $this->name = $response["name"];
            $this->color = $response["color"];
            $this->sex = $response["sex"];

            $specie = new Specie();
            $specie->fromJson(json_encode($response["species"]));
            $this->specie = $specie;

            $persons = [];
            if (isset($response['persons'])) {
                foreach ($response['persons'] as $personArray) {
                    $person = new Person();
                    $person->fromJson(json_encode($personArray));
                    $persons[] = $person;
                }
            }
            $this->setPersons($persons);

            return true;
        } else {
            return false;
        }
    }

    public function fromJson($json): void
    {
        $data = json_decode($json, true);
        $this->id = $data['id'];
        $this->name = $data['name'];
        if (isset($data['color'])) $this->color = $data['color'];
        if (isset($data['sex'])) $this->sex = $data['sex'];

        if (isset($data['species'])) {
            $species = new Specie();
            $species->fromJson(json_encode($data['species']));
            $this->setSpecie($species);
        }

        $persons = [];
        if (isset($data['persons'])) {
            foreach ($data['persons'] as $personArray) {
                $person = new Person();
                $person->fromJson(json_encode($personArray));
                $persons[] = $person;
            }
        }
        $this->setPersons($persons);
    }

    public function toJson(): false|string
    {
        return json_encode([
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'sex' => $this->sex,
            'species' => $this->specie
        ]);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'specie' => $this->specie->jsonSerialize()
        ];
    }
}