<?php

require_once __DIR__ . '/../models/Animal.php';

class Person
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private ?int $age;
    private array $animals;

    public function __construct($id = 0, $firstName = "", $lastName = "", $age = 0, $animals = [])
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
        $this->setAnimals($animals);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge($age): void
    {
        $this->age = $age;
    }

    public function getAnimals(): array
    {
        return $this->animals;
    }

    public function setAnimals($animals): void
    {
        $this->animals = $animals;
    }

    public function addAnimal($animal): void
    {
        $this->animals[] = $animal;
    }

    public function getAnimalsIds(): array
    {
        return array_map(fn($animal) => $animal->getId(), $this->animals);
    }

    public function fromDB($id): bool
    {
        $url = "http://localhost:8080/api/person/" . $id;

        require_once __DIR__ . '/../models/Animal.php';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $_SESSION['token']));
        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatusCode == 200) {

            $this->fromJson($response);

            $fullAnimals = [];
            foreach ($this->animals as $animal) {
                $fullAnimal = new Animal();
                $fullAnimal->fromDB($animal->getId());
                $fullAnimals[] = $fullAnimal;
            }
            $this->animals = $fullAnimals;

            return true;
        } else {
            return false;
        }
    }

    public function fromJson($json): void
    {
        $data = json_decode($json, true);

        $this->id = $data['id'];
        $this->firstName = $data['firstName'];
        $this->lastName = $data['lastName'];
        $this->age = $data['age'] ?? null;

        $animals = [];
        if (isset($data['animals'])) {
            foreach ($data['animals'] as $animal) {
                $animalObject = new Animal();
                $animalObject->fromJson(json_encode($animal));
                $animals[] = $animalObject;
            }
        }
        $this->setAnimals($animals);
    }

    public function toJson(): false|string
    {
        return json_encode([
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'age' => $this->age,
            'animals' => array_map(fn($animal) => json_decode($animal->toJson()), $this->animals),
        ]);
    }
}