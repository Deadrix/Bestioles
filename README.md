# Bestioles

## Prérequis

Ce projet nécessite le projet github disponible à l'adresse suivante :
https://github.com/jomage/Bestioles.git

Le projet Bestioles ne dit pas etre cloner dans le repertoire Laragon/www installé plus bas.

Lancer le projet Bestioles avec la commande suivante :
```
./mvnw spring-boot:run
```

## Installation

Installer un serveur web Apache et PHP.
Le plus simple est d'installer laragon, disponible a l'adresse suivante :
https://github.com/leokhoa/laragon/releases/download/6.0.0/laragon-wamp.exe

Par défaut laragon s'installe dans le répertoire C:\laragon

Dans le dossier C:\laragon\www, cloner le projet git :
```bash
cd C:\laragon\www
git clone https://github.com/Deadrix/Bestioles.git
```

Lancer laragon et cliquer sur démarrer en bas à gauche. 

Une popup admin devrait apparaitre, cliquer sur oui, cela sert à créer un VirtualHost.

Dans le navigateur, taper l'adresse suivante :
http://bestioles.test

A la première visite, se connecter avec les identifiants :
```
admin
admin
```
Le token ne se refresh pas automatiquement, il faut donc se déconnecter et se reconnecter pour obtenir un nouveau token.