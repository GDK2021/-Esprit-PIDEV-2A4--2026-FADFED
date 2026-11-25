# Sessions PHP

`session.php` fournit le socle de session de l’application. `view/frontOffice/index.php` démarre la session avant tout rendu HTML et conserve les paramètres de cookie sécurisés.

- `start_app_session()` démarre ou reprend la session.
- `set_authenticated_user($userId, $role)` enregistre uniquement l’identifiant et le rôle de l’utilisateur après une authentification réussie, puis renouvelle l’identifiant de session.
- `current_authenticated_user()` retourne ces deux informations ou `null`.
- `is_authenticated()` indique si un utilisateur est connecté.
- `logout_user()` efface les données et le cookie de session.

Ce socle ne vérifie pas les identifiants et n’ajoute pas de formulaire de connexion. Il faudra appeler `set_authenticated_user()` uniquement après avoir vérifié un mot de passe haché avec la base de données. Le mot de passe ne doit jamais être enregistré dans la session.

## Compte administrateur de test

La page de connexion du back-office est `view/BackOffice/login.php`. Démarrez MySQL dans XAMPP, puis créez le compte de test depuis la racine du projet :

```powershell
C:\xampp\php\php.exe .\control\create_admin.php admin
```

Saisissez `admin1234` à l’invite du mot de passe. Le mot de passe est haché avant d’être enregistré. Le script est exécutable uniquement en ligne de commande et refuse de modifier un compte existant. Après les tests, supprimez le compte de test dans la table `user` (`DELETE FROM user WHERE login = 'admin' AND role = 'admin';`).

## Profils

La page `view/frontOffice/profile.php` permet à tout utilisateur connecté de modifier ses informations et sa photo. Pour une base créée avant cette fonctionnalité, appliquez une fois la migration :

```powershell
C:\xampp\mysql\bin\mysql.exe -u root web_app < .\control\migrations\add_photo_profil.sql
```

Les photos acceptées sont JPEG, PNG et WebP, jusqu’à 2 Mo.