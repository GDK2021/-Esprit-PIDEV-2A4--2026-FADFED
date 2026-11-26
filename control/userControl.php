<?php
declare(strict_types=1);

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent($file, $line)) {
        throw new RuntimeException("Impossible de démarrer la session : les en-têtes ont déjà été envoyés dans {$file}:{$line}.");
    }

    if (ini_set('session.use_strict_mode', '1') === false
        || ini_set('session.use_only_cookies', '1') === false) {
        throw new RuntimeException('Impossible d’activer les paramètres de sécurité de session PHP.');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

    if (!session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ])) {
        throw new RuntimeException('Impossible de configurer le cookie de session.');
    }

    if (!session_start()) {
        throw new RuntimeException('Impossible de démarrer la session PHP.');
    }
}

function set_authenticated_user(int $userId, string $role): void
{
    if ($userId < 1 || trim($role) === '') {
        throw new InvalidArgumentException('Un identifiant utilisateur et un rôle valides sont requis.');
    }

    start_app_session();
    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Impossible de renouveler l’identifiant de session.');
    }

    $_SESSION['auth_user'] = [
        'id' => $userId,
        'role' => trim($role),
    ];
}

/**
 * @return array{id: int, role: string}|null
 */
function current_authenticated_user(): ?array
{
    start_app_session();

    if (!array_key_exists('auth_user', $_SESSION)) {
        return null;
    }

    $user = $_SESSION['auth_user'];
    if (!is_array($user)
        || !isset($user['id'], $user['role'])
        || !is_int($user['id'])
        || $user['id'] < 1
        || !is_string($user['role'])
        || trim($user['role']) === '') {
        throw new UnexpectedValueException('Les données utilisateur de la session sont invalides.');
    }

    return ['id' => $user['id'], 'role' => $user['role']];
}

function is_authenticated(): bool
{
    return current_authenticated_user() !== null;
}

function app_csrf_token(): string
{
    start_app_session();

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function app_csrf_token_is_valid(mixed $token): bool
{
    start_app_session();
    $expectedToken = $_SESSION['csrf_token'] ?? null;

    return is_string($token)
        && is_string($expectedToken)
        && hash_equals($expectedToken, $token);
}

function logout_user(): void
{
    start_app_session();
    $_SESSION = [];

    $cookie = session_get_cookie_params();
    if (!setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
    ])) {
        throw new RuntimeException('Impossible de supprimer le cookie de session.');
    }

    if (!session_destroy()) {
        throw new RuntimeException('Impossible de fermer la session PHP.');
    }
}

const DB_HOST = 'localhost';
const DB_NAME = 'web_app';
const DB_USER = 'root';
const DB_PASS = '';
const DEBUG = false;

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    return $pdo;
}

function jsonResponse(int $status, bool $success, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAdmin(): void
{
    $user = current_authenticated_user();
    if ($user === null || $user['role'] !== 'admin') {
        jsonResponse(403, false, "Accès refusé : réservé à l'administrateur.");
    }
}

function serverError(PDOException $e, string $message): never
{
    error_log($e->getMessage());
    jsonResponse(500, false, DEBUG ? $message . ' [' . $e->getMessage() . ']' : $message);
}

class UserController
{
    public function login(string $login, string $mdp): ?array
    {
        $statement = getPDO()->prepare('SELECT * FROM user WHERE login = :login');
        $statement->execute([':login' => $login]);
        $user = $statement->fetch();

        if ($user && password_verify($mdp, $user['mdp'])) {
            unset($user['mdp']);
            return $user;
        }

        return null;
    }
}

function controlLogin(): never
{
    start_app_session();
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, false, 'Méthode non autorisée.');
    }

    $login = $_POST['login'] ?? '';
    $password = $_POST['mdp'] ?? '';
    $area = $_POST['area'] ?? 'frontoffice';
    if (!is_string($login) || !is_string($password) || !is_string($area)) {
        jsonResponse(400, false, 'Données de connexion invalides.');
    }

    $login = trim($login);
    if (!in_array($area, ['frontoffice', 'backoffice'], true)) {
        jsonResponse(400, false, 'Type de connexion invalide.');
    }
    if ($login === '' || $password === '') {
        jsonResponse(422, false, 'Veuillez saisir votre identifiant et votre mot de passe.');
    }

    try {
        $user = (new UserController())->login($login, $password);
    } catch (PDOException $e) {
        serverError($e, 'Erreur lors de la connexion.');
    }

    if ($user === null) {
        jsonResponse(401, false, 'Identifiant ou mot de passe incorrect.');
    }
    if (($area === 'backoffice') !== ($user['role'] === 'admin')) {
        jsonResponse(
            403,
            false,
            $area === 'backoffice'
                ? "Ce compte n'est pas un compte administrateur."
                : 'Les administrateurs doivent utiliser la connexion du back-office.'
        );
    }

    set_authenticated_user((int) $user['id_user'], (string) $user['role']);
    echo json_encode([
        'success' => true,
        'redirect' => $area === 'backoffice' ? 'liste.php' : 'index.php',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function controlListUsers(): never
{
    requireAdmin();

    try {
        $users = getPDO()->query(
            'SELECT id_user, nom, prenom, age, genre, bloodtype, role, email, pseudo, login,
                    num_telephone, date_inscription
             FROM user ORDER BY id_user DESC'
        )->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'users' => $users], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (PDOException $e) {
        serverError($e, 'Erreur serveur lors du chargement des utilisateurs.');
    }
}

function controlAddUser(): never
{
    requireAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, false, 'Méthode non autorisée.');
    }

    $nom = trim((string) ($_POST['nom'] ?? ''));
    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $age = trim((string) ($_POST['age'] ?? ''));
    $genre = trim((string) ($_POST['genre'] ?? ''));
    $bloodtype = trim((string) ($_POST['bloodtype'] ?? ''));
    $role = trim((string) ($_POST['role'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telephone = trim((string) ($_POST['num_telephone'] ?? ''));
    $pseudo = trim((string) ($_POST['pseudo'] ?? ''));
    $login = trim((string) ($_POST['login'] ?? ''));
    $password = $_POST['mdp'] ?? '';
    if (!is_string($password)) {
        $password = '';
    }

    $errors = [];
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom)) $errors[] = 'Nom invalide.';
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) $errors[] = 'Prénom invalide.';
    if (!ctype_digit($age) || (int) $age > 120) $errors[] = 'Âge invalide.';
    if (!in_array($genre, ['Homme', 'Femme'], true)) $errors[] = 'Genre invalide.';
    if (!in_array($bloodtype, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) $errors[] = 'Groupe sanguin invalide.';
    if (!in_array($role, ['patient', 'pro', 'admin'], true)) $errors[] = 'Rôle invalide.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (!preg_match('/^\d{8}$/', $telephone)) $errors[] = 'Téléphone invalide (8 chiffres).';
    if (!preg_match('/^[\w.-]{3,30}$/', $pseudo)) $errors[] = 'Pseudo invalide.';
    if (!preg_match('/^[\w.-]{3,30}$/', $login)) $errors[] = 'Login invalide.';
    if (strlen($password) < 8) $errors[] = 'Mot de passe trop court (8 caractères minimum).';
    if ($errors !== []) {
        jsonResponse(422, false, implode(' ', $errors));
    }

    try {
        $statement = getPDO()->prepare(
            'INSERT INTO user
                (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
             VALUES
                (:nom, :prenom, :age, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, :telephone, CURDATE())'
        );
        $statement->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':age' => (int) $age,
            ':genre' => $genre,
            ':bloodtype' => $bloodtype,
            ':role' => $role,
            ':email' => $email,
            ':pseudo' => $pseudo,
            ':login' => $login,
            ':mdp' => password_hash($password, PASSWORD_DEFAULT),
            ':telephone' => (int) $telephone,
        ]);
        jsonResponse(201, true, 'Utilisateur ajouté avec succès (ID : ' . getPDO()->lastInsertId() . ').');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jsonResponse(409, false, 'Email, pseudo ou login déjà utilisé.');
        }
        serverError($e, "Erreur serveur lors de l'ajout.");
    }
}

function controlDeleteUser(): never
{
    requireAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, false, 'Méthode non autorisée.');
    }

    $id = trim((string) ($_POST['id_user'] ?? ''));
    if (!ctype_digit($id) || (int) $id < 1) {
        jsonResponse(422, false, 'ID invalide.');
    }
    $id = (int) $id;

    $currentUser = current_authenticated_user();
    if ($currentUser !== null && $id === $currentUser['id']) {
        jsonResponse(403, false, 'Tu ne peux pas supprimer ton propre compte.');
    }

    try {
        $statement = getPDO()->prepare('DELETE FROM user WHERE id_user = :id');
        $statement->execute([':id' => $id]);
        if ($statement->rowCount() === 0) {
            jsonResponse(404, false, "Aucun utilisateur avec l'ID $id.");
        }
        jsonResponse(200, true, "Utilisateur n°$id supprimé.");
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jsonResponse(409, false, "Suppression impossible : cet utilisateur est lié à d'autres données.");
        }
        serverError($e, 'Erreur serveur lors de la suppression.');
    }
}

function controlRegister(): never
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, false, 'Méthode non autorisée.');
    }

    $stringField = static function (string $name): string {
        $value = $_POST[$name] ?? '';
        return is_string($value) ? trim($value) : '';
    };

    $nom = $stringField('nom');
    $prenom = $stringField('prenom');
    $age = $stringField('age');
    $genre = $stringField('genre');
    $bloodtype = $stringField('bloodtype');
    $role = $stringField('role');
    $email = $stringField('email');
    $telephone = $stringField('num_telephone');
    $pseudo = $stringField('pseudo');
    $login = $stringField('login');
    $password = $_POST['mdp'] ?? '';
    $confirmation = $_POST['mdp_confirmation'] ?? '';
    if (!is_string($password) || !is_string($confirmation)) {
        jsonResponse(422, false, 'Données de mot de passe invalides.');
    }

    $errors = [];
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom)) $errors[] = 'Nom invalide.';
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) $errors[] = 'Prénom invalide.';
    if (!ctype_digit($age) || (int) $age > 120) $errors[] = 'Âge invalide.';
    if (!in_array($genre, ['Homme', 'Femme'], true)) $errors[] = 'Genre invalide.';
    if (!in_array($bloodtype, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) $errors[] = 'Groupe sanguin invalide.';
    if (!in_array($role, ['patient', 'pro'], true)) $errors[] = 'Type de compte invalide.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $errors[] = 'Adresse e-mail invalide.';
    if (!preg_match('/^\d{8}$/', $telephone)) $errors[] = 'Téléphone invalide (8 chiffres).';
    if (!preg_match('/^[\w.-]{3,30}$/', $pseudo)) $errors[] = 'Pseudo invalide (3 à 30 caractères).';
    if (!preg_match('/^[\w.-]{3,30}$/', $login)) $errors[] = 'Identifiant invalide (3 à 30 caractères).';
    if (strlen($password) < 8) $errors[] = 'Mot de passe trop court (8 caractères minimum).';
    if (!hash_equals($password, $confirmation)) $errors[] = 'Les mots de passe ne correspondent pas.';
    if ($errors !== []) {
        jsonResponse(422, false, implode(' ', $errors));
    }

    try {
        $statement = getPDO()->prepare(
            'INSERT INTO user
                (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
             VALUES
                (:nom, :prenom, :age, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, :telephone, CURDATE())'
        );
        $statement->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':age' => (int) $age,
            ':genre' => $genre,
            ':bloodtype' => $bloodtype,
            ':role' => $role,
            ':email' => $email,
            ':pseudo' => $pseudo,
            ':login' => $login,
            ':mdp' => password_hash($password, PASSWORD_DEFAULT),
            ':telephone' => (int) $telephone,
        ]);
        jsonResponse(201, true, 'Votre compte a été créé avec succès.');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jsonResponse(409, false, 'Cette adresse e-mail, ce pseudo ou cet identifiant est déjà utilisé.');
        }
        serverError($e, 'Erreur serveur lors de la création du compte.');
    }
}

function profileJsonResponse(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function profilePostString(string $name): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}

function controlProfile(): never
{
    header('Cache-Control: no-store');
    $authenticatedUser = current_authenticated_user();
    if ($authenticatedUser === null) {
        profileJsonResponse(401, ['success' => false, 'message' => 'Connectez-vous pour accéder à votre profil.']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $statement = getPDO()->prepare(
                'SELECT nom, prenom, age, genre, bloodtype, role, email, pseudo, login,
                        num_telephone, date_inscription, photo_profil
                 FROM user WHERE id_user = :id'
            );
            $statement->execute([':id' => $authenticatedUser['id']]);
            $profile = $statement->fetch();
            if ($profile === false) {
                profileJsonResponse(404, ['success' => false, 'message' => 'Compte utilisateur introuvable.']);
            }
            $profile['num_telephone'] = str_pad((string) $profile['num_telephone'], 8, '0', STR_PAD_LEFT);
            profileJsonResponse(200, ['success' => true, 'user' => $profile]);
        } catch (PDOException $e) {
            serverError($e, 'Erreur lors du chargement du profil.');
        }
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        profileJsonResponse(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
    }
    if (!app_csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
        profileJsonResponse(403, ['success' => false, 'message' => 'Votre session a expiré. Rechargez la page.']);
    }

    $nom = profilePostString('nom');
    $prenom = profilePostString('prenom');
    $age = profilePostString('age');
    $genre = profilePostString('genre');
    $bloodtype = profilePostString('bloodtype');
    $email = profilePostString('email');
    $pseudo = profilePostString('pseudo');
    $login = profilePostString('login');
    $telephone = profilePostString('num_telephone');

    $errors = [];
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom)) $errors[] = 'Nom invalide.';
    if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) $errors[] = 'Prénom invalide.';
    if (!ctype_digit($age) || (int) $age > 120) $errors[] = 'Âge invalide.';
    if (!in_array($genre, ['Homme', 'Femme'], true)) $errors[] = 'Genre invalide.';
    if (!in_array($bloodtype, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) $errors[] = 'Groupe sanguin invalide.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $errors[] = 'Adresse e-mail invalide.';
    if (!preg_match('/^[\w.-]{3,30}$/', $pseudo)) $errors[] = 'Pseudo invalide (3 à 30 caractères).';
    if (!preg_match('/^[\w.-]{3,30}$/', $login)) $errors[] = 'Identifiant invalide (3 à 30 caractères).';
    if (!preg_match('/^\d{8}$/', $telephone)) $errors[] = 'Téléphone invalide (8 chiffres).';
    if ($errors !== []) {
        profileJsonResponse(422, ['success' => false, 'message' => implode(' ', $errors)]);
    }

    $photoUpload = $_FILES['photo'] ?? null;
    if ($photoUpload !== null && !is_array($photoUpload)) {
        profileJsonResponse(400, ['success' => false, 'message' => 'Fichier photo invalide.']);
    }

    $hasPhotoUpload = is_array($photoUpload)
        && ($photoUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    $photoExtension = null;
    if ($hasPhotoUpload) {
        $uploadError = $photoUpload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError !== UPLOAD_ERR_OK) {
            profileJsonResponse(422, ['success' => false, 'message' => 'Le transfert de la photo a échoué.']);
        }
        if (($photoUpload['size'] ?? 0) > 2 * 1024 * 1024) {
            profileJsonResponse(422, ['success' => false, 'message' => 'La photo ne doit pas dépasser 2 Mo.']);
        }
        if (!isset($photoUpload['tmp_name']) || !is_string($photoUpload['tmp_name']) || !is_uploaded_file($photoUpload['tmp_name'])) {
            profileJsonResponse(422, ['success' => false, 'message' => 'Fichier photo invalide.']);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($photoUpload['tmp_name']);
        $imageInfo = getimagesize($photoUpload['tmp_name']);
        $allowedImages = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!is_string($mimeType)
            || !isset($allowedImages[$mimeType])
            || $imageInfo === false
            || $imageInfo[0] > 5000
            || $imageInfo[1] > 5000) {
            profileJsonResponse(422, ['success' => false, 'message' => 'Choisissez une image JPEG, PNG ou WebP valide (dimensions maximales : 5000 × 5000).']);
        }
        $photoExtension = $allowedImages[$mimeType];
    }

    $newPhotoRelativePath = null;
    $newPhotoAbsolutePath = null;
    $databaseUpdated = false;
    try {
        $pdo = getPDO();
        $existingStatement = $pdo->prepare('SELECT photo_profil FROM user WHERE id_user = :id');
        $existingStatement->execute([':id' => $authenticatedUser['id']]);
        $existingProfile = $existingStatement->fetch();
        if ($existingProfile === false) {
            profileJsonResponse(404, ['success' => false, 'message' => 'Compte utilisateur introuvable.']);
        }

        if ($hasPhotoUpload) {
            $uploadDirectory = __DIR__ . '/../view/assets/uploads/profiles';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                profileJsonResponse(500, ['success' => false, 'message' => 'Impossible de préparer le stockage des photos.']);
            }

            $filename = 'profile_' . $authenticatedUser['id'] . '_' . bin2hex(random_bytes(16)) . '.' . $photoExtension;
            $newPhotoAbsolutePath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
            $newPhotoRelativePath = 'assets/uploads/profiles/' . $filename;
            if (!move_uploaded_file($photoUpload['tmp_name'], $newPhotoAbsolutePath)) {
                profileJsonResponse(500, ['success' => false, 'message' => 'Impossible d’enregistrer la photo.']);
            }
        }

        $updateSql = 'UPDATE user
                      SET nom = :nom, prenom = :prenom, age = :age, genre = :genre,
                          bloodtype = :bloodtype, email = :email, pseudo = :pseudo,
                          login = :login, num_telephone = :telephone';
        if ($newPhotoRelativePath !== null) {
            $updateSql .= ', photo_profil = :photo_profil';
        }
        $updateSql .= ' WHERE id_user = :id';

        $parameters = [
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':age' => (int) $age,
            ':genre' => $genre,
            ':bloodtype' => $bloodtype,
            ':email' => $email,
            ':pseudo' => $pseudo,
            ':login' => $login,
            ':telephone' => (int) $telephone,
            ':id' => $authenticatedUser['id'],
        ];
        if ($newPhotoRelativePath !== null) {
            $parameters[':photo_profil'] = $newPhotoRelativePath;
        }

        try {
            $updateStatement = $pdo->prepare($updateSql);
            $updateStatement->execute($parameters);
            $databaseUpdated = true;
        } catch (PDOException $e) {
            if (!$databaseUpdated
                && $newPhotoAbsolutePath !== null
                && is_file($newPhotoAbsolutePath)
                && !unlink($newPhotoAbsolutePath)) {
                error_log('Unable to remove the unreferenced profile image: ' . $newPhotoAbsolutePath);
            }
            if ($e->getCode() === '23000') {
                profileJsonResponse(409, ['success' => false, 'message' => 'Cet e-mail, pseudo ou identifiant est déjà utilisé.']);
            }
            throw $e;
        }

        if ($newPhotoRelativePath !== null) {
            $oldPhoto = $existingProfile['photo_profil'];
            if (is_string($oldPhoto)
                && preg_match('~^assets/uploads/profiles/profile_\d+_[a-f0-9]{32}\.(?:jpg|png|webp)$~', $oldPhoto)) {
                $oldPhotoAbsolutePath = __DIR__ . '/../view/' . $oldPhoto;
                if (is_file($oldPhotoAbsolutePath) && !unlink($oldPhotoAbsolutePath)) {
                    error_log('Unable to remove the replaced profile image: ' . $oldPhotoAbsolutePath);
                }
            }
        }

        $statement = $pdo->prepare(
            'SELECT nom, prenom, age, genre, bloodtype, role, email, pseudo, login,
                    num_telephone, date_inscription, photo_profil
             FROM user WHERE id_user = :id'
        );
        $statement->execute([':id' => $authenticatedUser['id']]);
        $profile = $statement->fetch();
        if ($profile === false) {
            profileJsonResponse(404, ['success' => false, 'message' => 'Compte utilisateur introuvable.']);
        }
        $profile['num_telephone'] = str_pad((string) $profile['num_telephone'], 8, '0', STR_PAD_LEFT);

        profileJsonResponse(200, [
            'success' => true,
            'message' => 'Votre profil a été mis à jour.',
            'user' => $profile,
        ]);
    } catch (PDOException $e) {
        if ($newPhotoAbsolutePath !== null && is_file($newPhotoAbsolutePath) && !unlink($newPhotoAbsolutePath)) {
            error_log('Unable to remove the unreferenced profile image: ' . $newPhotoAbsolutePath);
        }
        serverError($e, 'Erreur lors de la mise à jour du profil.');
    }
}

function controlCreateAdmin(): never
{
    $login = $GLOBALS['argv'][2] ?? 'admin';
    if (!preg_match('/^[\w.-]{3,30}$/', $login)) {
        fwrite(STDERR, "Identifiant invalide (3 à 30 caractères).\n");
        exit(1);
    }

    try {
        $pdo = getPDO();
        $check = $pdo->prepare('SELECT role FROM user WHERE login = :login');
        $check->execute([':login' => $login]);
        if ($check->fetchColumn() !== false) {
            fwrite(STDERR, "\nCet identifiant existe déjà; aucun compte n'a été modifié.\n");
            exit(1);
        }

        fwrite(STDOUT, "Mot de passe du compte {$login}: ");
        $password = rtrim((string) fgets(STDIN), "\r\n");
        if (strlen($password) < 8) {
            fwrite(STDERR, "\nLe mot de passe doit contenir au moins 8 caractères.\n");
            exit(1);
        }

        $insert = $pdo->prepare(
            'INSERT INTO user
                (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
             VALUES
                (:nom, :prenom, 0, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, 0, CURDATE())'
        );
        $insert->execute([
            ':nom' => 'Administrateur',
            ':prenom' => 'Test',
            ':genre' => 'Homme',
            ':bloodtype' => 'O+',
            ':role' => 'admin',
            ':email' => $login . '@fadfed.local',
            ':pseudo' => $login,
            ':login' => $login,
            ':mdp' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        fwrite(STDOUT, "\nCompte administrateur créé. Vous pouvez vous connecter dans le back-office.\n");
        exit(0);
    } catch (PDOException $e) {
        fwrite(STDERR, "\nErreur lors de la création du compte administrateur: {$e->getMessage()}\n");
        exit(1);
    }
}

function dispatchUserControl(): void
{
    if (PHP_SAPI === 'cli') {
        if (($GLOBALS['argv'][1] ?? '') === 'create_admin') {
            controlCreateAdmin();
        }
        fwrite(STDERR, "Usage : php userControl.php create_admin [login]\n");
        exit(2);
    }

    $action = $_GET['action'] ?? '';
    if (!is_string($action)) {
        jsonResponse(400, false, 'Action invalide.');
    }

    switch ($action) {
        case 'login':
            controlLogin();
        case 'logout':
            logout_user();
            header('Location: ../view/frontOffice/index.php');
            exit;
        case 'list':
            controlListUsers();
        case 'add':
            controlAddUser();
        case 'delete':
            controlDeleteUser();
        case 'register':
            controlRegister();
        case 'profile':
            controlProfile();
        default:
            jsonResponse(404, false, 'Action introuvable.');
    }
}

$requestedScript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
if ($requestedScript !== false && $requestedScript === __FILE__) {
    dispatchUserControl();
}
