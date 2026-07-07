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

```bash
sudo apt update
sudo apt install -y php php-cli php-mysql php-mbstring php-curl php-xml unzip
php -v
```

```bash
sudo apt install -y composer
composer --version
```

```bash
sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
mysql --version
```

```bash
sudo apt install -y git
git --version
```

## macOS

> Homebrew doit être installé avant d’exécuter ces commandes.

```bash
brew install php
php -v
```

```bash
brew install composer
composer --version
```

```bash
brew install mariadb
brew services start mariadb
mysql --version
```

```bash
brew install git
git --version
```

## Windows — PowerShell

```powershell
winget install --id PHP.PHP -e
php -v
```

```powershell
winget install --id Composer.Composer -e
composer --version
```

```powershell
winget install --id MariaDB.Server -e
mysql --version
```

```powershell
winget install --id Git.Git -e
git --version
```

## Vérification finale

```bash
php -v
composer --version
mysql --version
git --version
```



### Étapes d'installation du projet

1. **Cloner le repository**
```bash
git clone https://github.com/naelkml/CarXMeet.git
cd CarXMeet
