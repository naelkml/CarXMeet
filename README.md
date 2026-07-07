## À propos

CarXMeet est une application web conçue pour connecter les amateurs de voitures et créer une communauté dynamique. Les utilisateurs peuvent :
- Créer des profils personnalisés
- Partager leurs expériences avec des automobiles
- Organiser des rencontres et des événements
- Échanger des conseils et des recommandations

## Fonctionnalités

<!-- Les principales fonctionnalités du projet -->
-  Authentification utilisateur sécurisée
-  Gestion de profils utilisateurs
-  Système de recherche et filtrage
-  Création et gestion d'événements
-  Messagerie entre utilisateurs
-  Galerie photos
-  Avis et évaluations
-  Interface responsive et intuitive

## Technologies utilisées

Ce projet utilise un stack technologique moderne :

| Technologie | Utilisation 
|---|---|---|
| **PHP** | Backend - Logique serveur et traitement des données 
| **HTML** | Structuration des pages web 
| **Twig** | Moteur de templates pour le rendu dynamique 
| **CSS** | Styling et mise en page responsive 
| **JavaScript** | Interactivité client-side 

### Stack complète

- **Backend** : PHP (Framework recommandé : Symfony ou Laravel)
- **Templates** : Twig (moteur de templates puissant et sécurisé)
- **Frontend** : HTML5, CSS3, JavaScript
- **Base de données** : MySQL/MariaDB (recommandé)
- **Serveur** : Apache ou Nginx

## Installation


### Prérequis

Avant de commencer, assurez-vous d'avoir installé :
- PHP >= 7.4
- Composer (gestionnaire de dépendances PHP)
- MySQL/MariaDB
- Git

# Installation des prérequis (si nécessaire)

## Linux — Debian / Ubuntu


sudo apt update
sudo apt install -y php php-cli php-mysql php-mbstring php-curl php-xml unzip
php -v


sudo apt install -y composer
composer --version


sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
mysql --version


sudo apt install -y git
git --version


## macOS

> Homebrew doit être installé avant d’exécuter ces commandes.


brew install php
php -v

brew install composer
composer --version


brew install mariadb
brew services start mariadb
mysql --version


brew install git
git --version


## Windows — PowerShell

winget install --id PHP.PHP -e
php -v


winget install --id Composer.Composer -e
composer --version

winget install --id MariaDB.Server -e
mysql --version

winget install --id Git.Git -e
git --version


## Vérification finale

php -v
composer --version
mysql --version
git --version



### Étapes d'installation du projet

1. **Cloner le repository**
```bash
git clone https://github.com/naelkml/CarXMeet.git
cd CarXMeet
