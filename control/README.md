# Sessions PHP

`session.php` fournit le socle de session de l’application. `view/index.php` démarre la session avant tout rendu HTML et conserve les paramètres de cookie sécurisés.

- `start_app_session()` démarre ou reprend la session.
- `set_authenticated_user($userId, $role)` enregistre uniquement l’identifiant et le rôle de l’utilisateur après une authentification réussie, puis renouvelle l’identifiant de session.
- `current_authenticated_user()` retourne ces deux informations ou `null`.
- `is_authenticated()` indique si un utilisateur est connecté.
- `logout_user()` efface les données et le cookie de session.

Ce socle ne vérifie pas les identifiants et n’ajoute pas de formulaire de connexion. Il faudra appeler `set_authenticated_user()` uniquement après avoir vérifié un mot de passe haché avec la base de données. Le mot de passe ne doit jamais être enregistré dans la session.