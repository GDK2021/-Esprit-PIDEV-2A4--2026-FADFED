# FADFED

FADFED est une application web dédiée à la santé mentale : un espace bienveillant où chacun peut trouver des repères et des ressources, à son rythme et sans jugement.

Projet réalisé dans le cadre du PIDEV 2A4 (Esprit, 2026).

## Fonctionnalités

**Front-office (utilisateurs)**
- Page d'accueil avec présentation et ressources
- Inscription d'un compte *patient* ou *professionnel*
- Connexion et déconnexion
- Page profil : modification des informations personnelles et de la photo de profil (JPEG, PNG ou WebP, 2 Mo maximum)

**Back-office (administrateurs)**
- Connexion réservée aux comptes `admin`
- Liste des utilisateurs
- Ajout et suppression d'utilisateurs (un admin ne peut pas supprimer son propre compte)

## Technologies

- PHP 8.1 ou plus récent (testé avec PHP 8.2 fourni par XAMPP)
- MySQL / MariaDB via PDO
- HTML, CSS et JavaScript (appels AJAX vers les contrôleurs, réponses JSON)

## Architecture (MVC)

```
FADFED/
├── control/                 Contrôleur et logique serveur
│   ├── userControl.php      Contrôleur unique : session, connexion PDO, actions HTTP
│   ├── shema.sql            Schéma de la base de données
│   ├── migrations/          Migrations SQL
│   └── README.md            Détail du contrôleur et des sessions
├── model/
│   └── user.php             Classes user et admin
└── view/
    ├── layout.php           Gabarit commun des pages
    ├── includes/            En-tête, barre de navigation, pied de page
    ├── frontOffice/         Accueil, connexion, inscription, profil
    ├── BackOffice/          Connexion admin et gestion des utilisateurs
    └── assets/              CSS, JavaScript, images et photos de profil
```

### Actions du contrôleur

Les pages appellent `control/userControl.php` en AJAX, avec le paramètre `action` :

| Action | Méthode | Rôle |
|--------|---------|------|
| `login` | POST | Connexion (front-office ou back-office) |
| `logout` | GET | Déconnexion |
| `register` | POST | Création de compte |
| `profile` | GET / POST | Consulter / modifier son profil |
| `list` | GET | Liste des utilisateurs (admin) |
| `add` | POST | Ajout d'un utilisateur (admin) |
| `delete` | POST | Suppression d'un utilisateur (admin) |

Les vues incluent aussi `userControl.php` pour utiliser les fonctions de session (`start_app_session()`, `current_authenticated_user()`…).

## Installation

1. Installer [XAMPP](https://www.apachefriends.org/) et démarrer **Apache** et **MySQL**.
2. Placer le projet dans `C:\xampp\htdocs\FADFED`.
3. Créer la base de données et la table `user` :

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root < .\control\shema.sql
   ```

4. Si besoin, adapter les identifiants de connexion dans `control/userControl.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Par défaut : base `web_app`, utilisateur `root`, sans mot de passe.
5. Si la base a été créée avant l'ajout des photos de profil, appliquer une fois la migration :

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root web_app < .\control\migrations\add_photo_profil.sql
   ```

6. Créer un compte administrateur (le mot de passe est demandé puis haché) :

   ```powershell
   C:\xampp\php\php.exe .\control\userControl.php create_admin admin
   ```

## Utilisation

| Page | Adresse |
|------|---------|
| Accueil | http://localhost/FADFED/view/frontOffice/index.php |
| Inscription | http://localhost/FADFED/view/frontOffice/register.php |
| Connexion | http://localhost/FADFED/view/frontOffice/login.php |
| Profil | http://localhost/FADFED/view/frontOffice/profile.php |
| Connexion admin | http://localhost/FADFED/view/BackOffice/login.php |
| Gestion des utilisateurs | http://localhost/FADFED/view/BackOffice/liste.php |

Les administrateurs se connectent uniquement par le back-office, les autres utilisateurs par le front-office.

## Sécurité

- Mots de passe hachés avec `password_hash()` et jamais renvoyés par l'API
- Requêtes préparées PDO contre les injections SQL
- Validation des données côté serveur (et pas seulement en JavaScript)
- Cookie de session `HttpOnly` et `SameSite=Lax`, identifiant de session renouvelé à la connexion
- Jeton CSRF pour la mise à jour du profil
- Opérations de gestion des utilisateurs réservées aux administrateurs

Plus de détails sur le contrôleur, les sessions et le compte de test : [control/README.md](control/README.md).
