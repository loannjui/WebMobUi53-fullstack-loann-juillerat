# HEIG-VD WebMobUI Course - Projet sondage fullstack

Ce dépôt contient le mini-projet à réaliser dans le cadre du cours
_"[WebMobUI](https://github.com/Chabloz/WebMobUi52/blob/main/ex/Fullstack_Project.md)"_
enseigné à la
[Haute Ecole d'Ingénierie et de Gestion du Canton de Vaud (HEIG-VD)](https://heig-vd.ch),
Suisse.

## Objectif du mini-projet

L'objectif est de concevoir une interface permettant de créer, configurer, consulter et utiliser des sondages à travers une API JSON consommée par le frontend.

Dans cette application, un sondage est un objet créé par une personne authentifiée, contenant une question, plusieurs options de réponse et un ensemble de paramètres définissant son comportement (brouillon ou lancé, choix simple ou multiple, visibilité des résultats et éventuelle durée de disponibilité).

## Choix techniques

- Le frontend utilise Vue 3 avec la Composition API (<script setup>).
- Les appels à l'API sont gérés avec useFetchApi qui centralise la gestion des headers, du token XSRF et des erreurs.
- L'état des sondages est centralisé dans un composable usePollStore réutilisable entre les composants.
- Un composable usePolling permet de mettre à jour les données périodiquement via un intervalle.
- Au lieu d'une SPA globale, les vues Laravel des sondages monte une instance Vue indépendante sur un élément dédié. Cela permet de les intégrer dans les routes Laravel de base.
- Pour la CSS, le projet continue à utiliser Tailwind pour garder une unité entre les nouvelles pages implémentées (les sondages) et les existantes (posts, pages profil, etc.).

## Pré-requis

Afin de lancer ce projet, une stack compatible avec Laravel, est requise.

Voici les pré-requis nécessaires :

- PHP >= 8.2.
- Composer.
- Node.js et npm.
- Une base de données (MySQL, PostgreSQL, SQLite, etc.).
- Un serveur web (Apache, Nginx, etc.).

[Laravel Herd](https://herd.laravel.com) est recommandé pour une installation facile de Laravel et de ses dépendances.

## Développement local

Pour développer et tester le mini-projet en local, voici les étapes à suivre :

1. Forker ce dépôt

2. Installer les dépendances avec npm et Composer :

    ```bash
    npm install && npm run build

    composer install
    ```

3. Copier le fichier `.env.example` en `.env`.
4. Modifier les variables d'environnement si nécessaire (optionnel).
5. Générer la clé d'application Laravel :

    ```bash
    php artisan key:generate
    ```

6. Créer le lien symbolique pour les fichiers téléversés :

    ```bash
    php artisan storage:link
    ```

7. Créer la base de données et exécuter les migrations :

    ```bash
    php artisan migrate
    ```

    S'il est nécessaire de réinitialiser la base de données, utiliser la commande `php artisan migrate:reset` puis `php artisan migrate` à nouveau.

8. Optionnel : en mode développement, il est possible de peupler la base de données avec des données fictives :

    ```bash
    php artisan db:seed
    ```

9. Démarrer le serveur de développement Laravel :

    ```bash
    composer run dev
    ```

**Raccourci**
Les étapes 2, 3, 5 et 7 peuvent être effectuées en une seule commande :

    ```bash
    composer setup
    ```

L'application sera accessible à l'adresse <http://localhost:8000>.
