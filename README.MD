# Mafkoud

Mafkoud est une application web PHP/MySQL destinee a publier, rechercher et administrer des fiches de personnes disparues. Le projet est construit volontairement sans framework lourd: il repose sur PHP natif, PDO, des sessions serveur, une base MySQL/MariaDB et une couche front HTML/CSS/Sass legere.

L'application couvre les usages principaux suivants:

- consultation publique des fiches actives et non resolues;
- recherche par prenom, nom, ville et wilaya;
- inscription, authentification et deconnexion des utilisateurs;
- creation de signalements avec photo;
- fiche detaillee d'une personne disparue;
- gestion des signalements par le proprietaire du compte;
- marquage d'une personne comme retrouvee;
- administration des fiches: activation, modification et suppression.

---

## Sommaire

- [Pile technique](#pile-technique)
- [Architecture du projet](#architecture-du-projet)
- [Fonctionnement applicatif](#fonctionnement-applicatif)
- [Modele de donnees](#modele-de-donnees)
- [Prerequis](#prerequis)
- [Installation locale](#installation-locale)
- [Configuration de la base de donnees](#configuration-de-la-base-de-donnees)
- [Lancer le projet](#lancer-le-projet)
- [Compilation Sass et assets front](#compilation-sass-et-assets-front)
- [Creation d'un compte administrateur](#creation-dun-compte-administrateur)
- [Validation fonctionnelle](#validation-fonctionnelle)
- [Deploiement](#deploiement)
- [Securite et durcissement production](#securite-et-durcissement-production)
- [Maintenance](#maintenance)
- [Depannage](#depannage)

---

## Pile technique

### Backend

- **PHP natif**: application procedurale, chaque page PHP porte son propre controleur et son rendu HTML.
- **PDO**: connexion SQL centralisee dans `connectdb.php`, avec mode exception active.
- **Sessions PHP**: authentification basee sur `$_SESSION`, avec regeneration d'identifiant de session sur plusieurs parcours.
- **Mots de passe modernes pour les nouveaux comptes**: `password_hash(..., PASSWORD_BCRYPT)` a l'inscription et `password_verify()` a la connexion.
- **Protection CSRF partielle**: des tokens CSRF sont presents sur plusieurs actions sensibles cote profil et mise a jour.
- **Uploads controles**: les photos de signalement sont validees par extension, taille maximale et controle image via `getimagesize()`.

### Base de donnees

- **MySQL/MariaDB**
- **InnoDB**
- **Relation `user` -> `missing`** avec cle etrangere et cascade sur suppression/mise a jour.
- **Dump SQL fourni**: `missing.sql`.

### Frontend

- **HTML5**
- **CSS3**
- **Sass/SCSS**: source principale dans `sass/main.scss`, CSS compile dans `css/style.css`.
- **JavaScript vanilla**: `js/script.js` met a jour automatiquement l'annee du footer.
- **Assets statiques**: icones, favicons, webmanifest et police locale `fauna_oneregular`.

### Outillage

- **Node.js/npm** pour lancer les scripts Sass.
- **Aucune dependance Composer active**: `composer.json` est present mais vide dans l'etat actuel du projet.

---

## Architecture du projet

```text
Mafkoud/
|-- .env.exemple              # Exemple de configuration d'environnement
|-- .gitignore                # Ignore .env
|-- admin.php                 # Back-office administrateur
|-- connectdb.php             # Chargement .env + connexion PDO
|-- fiche.php                 # Fiche detaillee d'une personne disparue
|-- footer.php                # Footer commun + chargement JS
|-- index.php                 # Accueil, recherche et dernieres fiches actives
|-- login.php                 # Authentification
|-- logout.php                # Deconnexion
|-- missing.php               # Creation d'un signalement
|-- missing.sql               # Schema + donnees de demonstration
|-- package.json              # Scripts Sass
|-- profil.php                # Profil utilisateur + gestion des signalements
|-- register.php              # Inscription utilisateur
|-- updateMissing.php         # Modification d'un signalement
|-- updateProfil.php          # Modification du profil utilisateur
|-- assets/
|   |-- fonts/                # Police locale
|   |-- images/               # Icones UI
|   `-- favicon/webmanifest
|-- css/
|   |-- style.css             # CSS charge par les pages PHP
|   `-- style.css.map
|-- images/                   # Photos uploadees des signalements
|-- js/
|   `-- script.js
`-- sass/
    `-- main.scss             # Source Sass principale
```

---

## Fonctionnement applicatif

### Parcours public

`index.php` affiche les dernieres fiches de personnes disparues avec les conditions suivantes:

- `isActive = 1`: la fiche a ete activee par l'administration;
- `isFind = 0`: la personne n'est pas encore marquee comme retrouvee.

La recherche publique filtre les fiches actives et non retrouvees par:

- `firstName`;
- `lastName`;
- `city`;
- `wilaya`.

Une fiche peut etre consultee via `fiche.php?id=<idmissing>`.

### Parcours utilisateur

Un utilisateur peut:

- creer un compte depuis `register.php`;
- se connecter via `login.php`;
- creer un signalement depuis `missing.php`;
- consulter ses fiches depuis `profil.php?id=<iduser>`;
- modifier une fiche avec `updateMissing.php?id=<idmissing>`;
- supprimer une fiche;
- marquer une personne comme retrouvee (`isFind = 1`);
- modifier son profil depuis `updateProfil.php?id=<iduser>`;
- supprimer son compte.

Par defaut, une nouvelle fiche est creee avec `isActive = 0`. Elle ne sera donc visible publiquement qu'apres validation administrative.

### Parcours administrateur

Un utilisateur administrateur est identifie par `user.isAdmin = 1`.

Depuis `admin.php`, il peut:

- consulter les signalements;
- activer une fiche (`isActive = 1`);
- rediriger vers la modification d'une fiche;
- supprimer une fiche et son image associee;
- acceder aux informations de profil.

Important: l'interface admin actuelle active les fiches, mais ne propose pas de bascule inverse explicite vers `isActive = 0`.

---

## Modele de donnees

### Table `user`

La table `user` contient les comptes applicatifs.

Champs principaux:

- `iduser`: identifiant primaire auto-incremente;
- `pseudo`: pseudo utilisateur;
- `fullName`: nom complet;
- `phone`: telephone;
- `email`: email unique;
- `password`: mot de passe hashe;
- `isAdmin`: indicateur administrateur (`0` ou `1`);
- `create_time`: date de creation;
- `update_time`: date de mise a jour.

Contraintes:

- cle primaire sur `iduser`;
- unicite sur `email`;
- unicite sur `iduser`.

### Table `missing`

La table `missing` contient les fiches de personnes disparues.

Champs principaux:

- `idmissing`: identifiant primaire auto-incremente;
- `user_iduser`: proprietaire de la fiche;
- identite: `firstName`, `lastName`, `birthDate`, `BirthPlace`, `Sexe`;
- localisation: `currentAdress`, `previousAdress`, `city`, `wilaya`, `cityOfMissing`, `wilayaOfMissing`;
- disparition: `dateOfMissing`, `ageOfMissing`, `describing`;
- contact et caracteristiques: `phone`, `height`, `weight`, `hair`, `eyes`, `blood`;
- informations medicales/physiques: `bloodPressure`, `diabet`, `mentalIlness`, `tatoos`, `birthmarks`, `scars`;
- `photo`: chemin de l'image uploadee;
- `isActive`: validation administrative;
- `isFind`: statut retrouve;
- `create_time`, `update_time`.

Relation:

- `missing.user_iduser` reference `user.iduser`;
- suppression en cascade: supprimer un utilisateur supprime ses fiches en base.

---

## Prerequis

### Environnement recommande

- PHP 8.1 ou superieur;
- extension PHP `pdo_mysql` activee;
- MySQL 5.7+ ou MariaDB 10.4+;
- Apache, Nginx, Laragon, WAMP, XAMPP ou serveur PHP integre pour le developpement;
- Node.js 18+ si vous souhaitez recompiler le Sass;
- acces en ecriture au dossier `images/` pour les uploads.

### Configuration PHP utile

Verifier dans `php.ini`:

```ini
file_uploads = On
upload_max_filesize = 2M
post_max_size = 8M
max_file_uploads = 20
```

L'application limite deja les images a 2 Mo dans le code. `upload_max_filesize` doit donc etre au moins egal a cette valeur.

---

## Installation locale

### 1. Recuperer le projet

Dans un environnement Laragon, WAMP ou XAMPP, placer le dossier dans le repertoire web:

```powershell
D:\laragon\www\Mafkoud
```

ou cloner le depot:

```bash
git clone <url-du-depot> Mafkoud
cd Mafkoud
```

### 2. Creer le fichier d'environnement

Copier l'exemple:

```bash
cp .env.exemple .env
```

Sous PowerShell:

```powershell
Copy-Item .env.exemple .env
```

Renseigner les variables:

```env
DB_HOST=127.0.0.1
DB_NAME=missing
DB_USER=root
DB_PASS=
```

Adapter `DB_USER` et `DB_PASS` selon votre serveur local.

### 3. Installer la base de donnees

Creer la base:

```sql
CREATE DATABASE missing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importer le dump:

```bash
mysql -u root -p missing < missing.sql
```

Sous PowerShell, si la redirection `<` pose probleme:

```powershell
cmd /c "mysql -u root -p missing < missing.sql"
```

Alternative avec phpMyAdmin:

1. creer une base nommee `missing`;
2. ouvrir l'onglet **Importer**;
3. selectionner `missing.sql`;
4. lancer l'import.

### 4. Verifier les permissions d'upload

Le dossier `images/` doit etre accessible en ecriture par le serveur web.

Sur Linux:

```bash
chmod 755 images
```

Selon l'utilisateur du serveur web:

```bash
chown -R www-data:www-data images
```

Sur Windows/Laragon, verifier que le dossier n'est pas en lecture seule.

---

## Configuration de la base de donnees

La connexion est centralisee dans `connectdb.php`.

Le fichier:

1. lit `.env`;
2. extrait `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`;
3. ouvre une connexion PDO;
4. force l'encodage `utf8mb4`;
5. active `PDO::ERRMODE_EXCEPTION`.

Exemple de DSN genere:

```php
mysql:host=127.0.0.1;dbname=missing;charset=utf8mb4
```

En production, ne jamais versionner `.env`. Le fichier est deja ignore par `.gitignore`.

---

## Lancer le projet

### Avec Laragon

Si le projet est dans `D:\laragon\www\Mafkoud`, l'application est generalement accessible via:

```text
http://localhost/Mafkoud
```

ou via l'hote virtuel Laragon si active:

```text
http://mafkoud.test
```

### Avec le serveur PHP integre

Depuis la racine du projet:

```bash
php -S 127.0.0.1:8000
```

Puis ouvrir:

```text
http://127.0.0.1:8000
```

Ce mode est pratique pour le developpement, mais il n'est pas recommande pour la production.

---

## Compilation Sass et assets front

Le fichier source principal est:

```text
sass/main.scss
```

Le CSS utilise par les pages PHP est:

```text
css/style.css
```

### Installer Sass

Le `package.json` contient des scripts, mais aucune dependance npm n'est declaree actuellement. Installer Sass localement:

```bash
npm install --save-dev sass
```

### Mode watch

```bash
npm run sass
```

Ce script compile:

```text
sass/main.scss -> css/style.css
```

### Build production recommande

Le script `build:sass` actuel cible `./assets/css/style.css`, alors que les pages chargent `./css/style.css`.

Pour produire le CSS reellement utilise par l'application, executer:

```bash
npx sass ./sass/main.scss ./css/style.css --no-source-map --style=compressed
```

Si vous souhaitez conserver un script npm de production coherent, ajuster `package.json` pour compiler vers `./css/style.css`.

---

## Creation d'un compte administrateur

Le dump `missing.sql` contient des utilisateurs de demonstration historiques. Attention: leurs mots de passe semblent stockes sous un ancien format MD5, alors que le code actuel utilise `password_verify()` et attend des mots de passe generes avec `password_hash()`.

Consequence pratique: les comptes importes depuis le dump peuvent ne pas etre utilisables pour se connecter tant que leur mot de passe n'a pas ete reinitialise.

Procedure recommandee:

1. lancer l'application;
2. creer un nouvel utilisateur via `register.php`;
3. promouvoir cet utilisateur en administrateur:

```sql
UPDATE user
SET isAdmin = 1
WHERE email = 'admin@example.com';
```

Ensuite, se connecter avec ce compte et acceder a:

```text
admin.php?id=<iduser>
```

---

## Validation fonctionnelle

### Controle syntaxique PHP

Depuis la racine:

```bash
php -l index.php
php -l register.php
php -l login.php
php -l missing.php
php -l profil.php
php -l updateProfil.php
php -l updateMissing.php
php -l fiche.php
php -l admin.php
php -l logout.php
```

Sous PowerShell:

```powershell
Get-ChildItem -Filter *.php | ForEach-Object { php -l $_.FullName }
```

### Scenario de smoke test

1. Ouvrir `index.php`.
2. Verifier que la page d'accueil charge le CSS et les assets.
3. Creer un compte depuis `register.php`.
4. Se connecter depuis `login.php`.
5. Creer une fiche depuis `missing.php` avec une image `.jpg`, `.jpeg` ou `.png` de moins de 2 Mo.
6. Promouvoir le compte en admin si necessaire.
7. Aller dans `admin.php?id=<iduser>` et activer la fiche.
8. Revenir sur `index.php`: la fiche doit apparaitre dans les derniers signalements.
9. Ouvrir la fiche detail.
10. Depuis le profil, marquer la personne comme retrouvee.
11. Verifier qu'elle ne s'affiche plus dans la liste publique.

---

## Deploiement

### 1. Preparer l'environnement serveur

Installer:

- PHP avec `pdo_mysql`;
- MySQL/MariaDB;
- Apache ou Nginx;
- certificats TLS;
- Node.js uniquement si la compilation Sass est faite sur le serveur.

Recommandation: compiler les assets avant le deploiement, puis livrer directement `css/style.css`.

### 2. Copier les fichiers applicatifs

Deployer le projet dans un dossier dedie, par exemple:

```text
/var/www/mafkoud
```

Ne pas exposer inutilement les fichiers sensibles ou internes. Le projet actuel n'a pas de dossier `public/`; le document root pointe donc sur la racine applicative. Dans cette configuration, il faut imperativement proteger les fichiers comme `.env`, `missing.sql`, `sass/` et les fichiers de configuration serveur.

### 3. Configurer `.env`

Exemple production:

```env
DB_HOST=127.0.0.1
DB_NAME=missing
DB_USER=mafkoud_app
DB_PASS=mot_de_passe_long_et_unique
```

Creer un utilisateur SQL dedie:

```sql
CREATE USER 'mafkoud_app'@'localhost' IDENTIFIED BY 'mot_de_passe_long_et_unique';
GRANT SELECT, INSERT, UPDATE, DELETE ON missing.* TO 'mafkoud_app'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Importer la base

```bash
mysql -u root -p missing < missing.sql
```

Pour un serveur existant, prevoir une strategie de migration plutot qu'un import destructif.

### 5. Configurer Apache

Exemple minimal:

```apache
<VirtualHost *:80>
    ServerName mafkoud.example.com
    DocumentRoot /var/www/mafkoud

    <Directory /var/www/mafkoud>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Files ".env">
        Require all denied
    </Files>

    ErrorLog ${APACHE_LOG_DIR}/mafkoud_error.log
    CustomLog ${APACHE_LOG_DIR}/mafkoud_access.log combined
</VirtualHost>
```

Ajouter HTTPS via Let's Encrypt ou un certificat equivalent.

### 6. Configurer Nginx

Exemple minimal avec PHP-FPM:

```nginx
server {
    listen 80;
    server_name mafkoud.example.com;
    root /var/www/mafkoud;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ /\. {
        deny all;
    }

    location ~* \.(sql|scss|map)$ {
        deny all;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    location /images/ {
        try_files $uri =404;
    }
}
```

Ajouter HTTPS avant ouverture publique.

### 7. Permissions serveur

Permissions de base:

```bash
find /var/www/mafkoud -type f -exec chmod 644 {} \;
find /var/www/mafkoud -type d -exec chmod 755 {} \;
chmod 755 /var/www/mafkoud/images
chown -R www-data:www-data /var/www/mafkoud/images
```

Le dossier `images/` doit etre le seul dossier applicatif necessitant l'ecriture par le serveur web.

### 8. Mise en production

Checklist:

- `.env` present et non accessible publiquement;
- base importee;
- utilisateur SQL dedie configure;
- `images/` writable;
- CSS compile;
- HTTPS actif;
- `display_errors` desactive;
- logs serveur operationnels;
- compte administrateur cree;
- test complet d'inscription, connexion, creation, activation et consultation.

---

## Securite et durcissement production

Le projet contient deja plusieurs bonnes pratiques: PDO, hashing bcrypt pour les nouveaux comptes, validation de fichiers uploades, sessions et tokens CSRF sur plusieurs formulaires.

Avant une mise en production reelle, traiter les points suivants:

- **Admin SQL**: `admin.php` contient encore des requetes construites avec des valeurs GET/POST interpolees directement. Les remplacer par des requetes preparees et valider strictement tous les identifiants.
- **Affichage des erreurs**: `fiche.php` et `admin.php` activent encore `display_errors`. En production, desactiver l'affichage et journaliser les erreurs.
- **Protection `.env`**: bloquer l'acces HTTP aux fichiers caches et aux fichiers de configuration.
- **Protection du dossier `images/`**: interdire l'execution de PHP dans le dossier d'upload.
- **Cookies de session**: activer `session.cookie_secure`, `session.cookie_httponly` et `session.cookie_samesite`.
- **Headers HTTP**: ajouter au minimum `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` et une politique CSP adaptee.
- **Comptes de demonstration**: supprimer ou reinitialiser les comptes importes depuis `missing.sql`.
- **Backups**: planifier des sauvegardes de la base et du dossier `images/`.
- **Logs**: surveiller erreurs PHP, erreurs SQL, codes HTTP 4xx/5xx et echecs d'upload.

Exemple Apache pour empecher l'execution PHP dans `images/`:

```apache
<Directory /var/www/mafkoud/images>
    php_admin_flag engine off
    Options -ExecCGI
</Directory>
```

Exemple Nginx:

```nginx
location ~* ^/images/.*\.php$ {
    deny all;
}
```

---

## Maintenance

### Sauvegarde

Base de donnees:

```bash
mysqldump -u mafkoud_app -p missing > backup_missing_$(date +%F).sql
```

Images:

```bash
tar -czf backup_images_$(date +%F).tar.gz images/
```

### Restauration

```bash
mysql -u root -p missing < backup_missing_YYYY-MM-DD.sql
tar -xzf backup_images_YYYY-MM-DD.tar.gz
```

### Nettoyage

Surveiller:

- les images orphelines dans `images/`;
- les fiches non activees anciennes;
- les comptes de test;
- la taille du dossier d'upload;
- les erreurs SQL dans les logs.

---

## Depannage

### Page blanche

Causes probables:

- erreur PHP masquee par `display_errors = off`;
- extension `pdo_mysql` absente;
- `.env` manquant;
- connexion MySQL invalide.

Action:

```bash
php -m | grep pdo
php -l index.php
```

Consulter aussi les logs Apache/Nginx/PHP.

### Erreur de connexion a la base

Verifier:

- `DB_HOST`;
- `DB_NAME`;
- `DB_USER`;
- `DB_PASS`;
- existence de la base `missing`;
- droits SQL de l'utilisateur.

### Les images ne s'uploadent pas

Verifier:

- extension autorisee: `jpg`, `jpeg`, `png`;
- taille inferieure a 2 Mo;
- `file_uploads = On`;
- `upload_max_filesize`;
- droits d'ecriture sur `images/`;
- espace disque disponible.

### Le CSS ne se met pas a jour

Relancer:

```bash
npm run sass
```

ou compiler directement:

```bash
npx sass ./sass/main.scss ./css/style.css --no-source-map --style=compressed
```

### Impossible de se connecter avec les comptes du dump

Les anciens mots de passe du dump ne sont pas au format attendu par `password_verify()`. Creer un nouveau compte via l'application ou reinitialiser les mots de passe avec `password_hash()`.

---

## Notes de qualite

Ce projet a une base simple, lisible et directe. Sa force est sa faible complexite operationnelle: peu de dependances, une base SQL claire, des pages identifiables et un flux metier comprehensible. Pour aller vers un niveau production robuste, les prochaines evolutions prioritaires seraient:

- isoler un dossier `public/`;
- centraliser les controles d'authentification et d'autorisation;
- factoriser les acces SQL;
- finaliser la securisation de `admin.php`;
- ajouter des migrations SQL versionnees;
- ajouter des tests de parcours critiques;
- normaliser l'encodage UTF-8 dans tous les fichiers.

