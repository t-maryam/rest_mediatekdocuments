# rest_mediatekdocuments – Atelier 2

> Ce dépôt présente les fonctionnalités **ajoutées** dans le cadre de l'Atelier 2.
> Pour la présentation de l'API d'origine, de sa structure et de son exploitation, consulter le dépôt d'origine :
> https://github.com/CNED-SLAM/rest_mediatekdocuments

## Présentation

Cette API REST, écrite en PHP 8.3, permet d'exécuter des requêtes SQL sur la base de données MySQL `mediatek86` de la médiathèque MediaTek86.
Elle est protégée par une authentification basique et répond aux demandes de l'application de bureau C# MediatekDocuments, disponible ici :
https://github.com/t-maryam/mediatekdocuments

## Fonctionnalités ajoutées

Les ajouts ont été réalisés uniquement dans le fichier `src/MyAccessBDD.php`, en ajoutant de nouvelles méthodes privées et leurs cases dans les switch correspondants.

### Gestion des commandes de livres/DVD

- `selectCommandesLivre` : récupère toutes les commandes de livres/DVD avec le libellé de l'étape de suivi. Peut être filtrée par id de document.
- `insertCommandeLivre` : insère une nouvelle commande dans `commande` puis dans `commandedocument` avec l'étape de suivi "en cours" (id `00001`).
- `updateSuiviCommande` : modifie l'étape de suivi d'une commande dans `commandedocument`.
- `deleteCommandeLivre` : supprime une commande dans `commande` (le trigger `before_delete_commande` assure la suppression en cascade dans `commandedocument`).

### Gestion des commandes de revues (abonnements)

- `selectCommandesRevue` : récupère toutes les commandes de revues (abonnements) avec leur date de fin. Peut être filtrée par id de revue.
- `insertCommandeRevue` : insère un nouvel abonnement dans `commande` puis dans `abonnement`.
- `updateAbonnement` : modifie la date de fin d'un abonnement (renouvellement).
- `deleteCommandeRevue` : supprime un abonnement dans `abonnement` puis dans `commande`.

### Authentification des utilisateurs

- `selectUtilisateur` : vérifie les identifiants d'un utilisateur (login + mot de passe haché en SHA256) et retourne son service d'appartenance.

### Sécurité (Mission 5)

- Le fichier `.htaccess` a été modifié pour retourner une erreur **400** si l'API est appelée sans paramètres (URL racine), évitant l'affichage de la liste des fichiers du serveur.
- Le fichier `.env` contient les identifiants de connexion à la BDD et de l'authentification de l'API — il n'est jamais versionné sur GitHub (présent dans `.gitignore`).
- Une directive `SetEnvIf Authorization` a été ajoutée dans `.htaccess` pour assurer la transmission de l'en-tête d'authentification sur les hébergeurs mutualisés.

## Installation en local

### Prérequis

- WampServer (avec PHP 8.3 et MySQL 9.x)
- NetBeans (ou tout autre éditeur PHP)
- Composer
- Postman (pour les tests)

### Étapes

1. Cloner ou télécharger ce dépôt et placer le dossier dans `C:\wamp64\www\` en le nommant `rest_mediatekdocuments`.
2. Ouvrir une invite de commandes en mode administrateur, se placer dans ce dossier et taper :
   ```
   composer install
   ```
   Cela recrée le dossier `vendor/` nécessaire au bon fonctionnement.
3. Dans phpMyAdmin (`http://localhost/phpmyadmin`), créer une base de données nommée `mediatek86` puis importer le script `mediatek86.sql` situé à la racine du projet.
4. Copier le fichier `.env.example` en `.env` dans le dossier `src/` et le remplir avec les informations locales :
   ```
   AUTHENTIFICATION=basic
   AUTH_USER=admin
   AUTH_PW=adminpwd
   BDD_LOGIN=root
   BDD_PWD=
   BDD_BD=mediatek86
   BDD_SERVER=localhost
   BDD_PORT=3306
   ```
5. Vérifier que WampServer est démarré (icône verte) et tester l'API dans Postman.

### Test avec Postman

Configurer l'authentification dans Postman :
- Onglet **Authorization** → Type : **Basic Auth**
- Username : `admin`
- Password : `adminpwd`

Exemple de requête GET pour récupérer les livres :
```
GET http://localhost/rest_mediatekdocuments/livre
```

## API en ligne

L'API est déployée en ligne sur AwardSpace à l'adresse :
```
http://mediatek86.atwebpages.com/
```
La documentation technique est disponible à l'adresse :
```
[http://mediatek86.atwebpages.com/docs/index.html](http://mediatek86.atwebpages.com/docs/index.html)
```
Exemple de requête Postman pour tester l'API en ligne :
```
GET http://mediatek86.atwebpages.com/livre
```
Avec les identifiants Basic Auth configurés dans l'application (voir `App.config` de MediatekDocuments).

```
