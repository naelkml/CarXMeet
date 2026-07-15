# CarXMeet

## Présentation

CarXMeet est une application web destinée aux passionnés d'automobile. Elle permet aux utilisateurs de créer un profil, d'échanger avec la communauté, de partager du contenu et d'organiser des événements automobiles.

## Fonctionnalités

* Authentification sécurisée
* Gestion des profils utilisateurs
* Recherche et filtrage
* Création et gestion d'événements
* Messagerie entre utilisateurs
* Galerie photos
* Avis et évaluations
* Interface responsive

## Technologies

| Composant        | Technologie                    |
| ---------------- | ------------------------------ |
| Backend          | PHP 8.4 - Symfony 7            |
| Frontend         | React, JavaScript, HTML5, CSS3 |
| Templates        | Twig                           |
| Base de données  | MySQL                          |
| Serveur Web      | Nginx                          |
| Conteneurisation | Docker & Docker Compose        |

## Prérequis

* Git
* Docker
* Docker Compose

Vérification de l'installation :

```bash
git --version
docker --version
docker compose version
```

## Installation

Cloner le dépôt :

```bash
git clone https://github.com/naelkml/CarXMeet.git
cd CarXMeet
git checkout developpement
```

Construire et démarrer les conteneurs :

```bash
docker compose up --build -d
```

Installer les dépendances du projet :

```bash
docker compose exec php composer install
docker compose exec php npm install
```

Créer la base de données et appliquer les migrations :

```bash
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate
```

Compiler les ressources front-end :

```bash
docker compose exec php npm run build
```

## Lancement

Une fois les conteneurs démarrés, l'application est accessible à l'adresse :

```
http://localhost
```

## Commandes utiles

Arrêter les conteneurs :

```bash
docker compose down
```

Afficher les journaux :

```bash
docker compose logs -f
```

Accéder au conteneur PHP :

```bash
docker compose exec php bash
```

Vider le cache Symfony :

```bash
docker compose exec php php bin/console cache:clear
```

## Auteur

Naël Khamallah

Dépôt GitHub : https://github.com/naelkml/CarXMeet
