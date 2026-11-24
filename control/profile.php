<?php
require_once __DIR__ . '/config.php';

header('Cache-Control: no-store');

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
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom)) {
    $errors[] = 'Nom invalide.';
}
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) {
    $errors[] = 'Prénom invalide.';
}
if (!ctype_digit($age) || (int) $age > 120) {
    $errors[] = 'Âge invalide.';
}
if (!in_array($genre, ['Homme', 'Femme'], true)) {
    $errors[] = 'Genre invalide.';
}
if (!in_array($bloodtype, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) {
    $errors[] = 'Groupe sanguin invalide.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = 'Adresse e-mail invalide.';
}
if (!preg_match('/^[\w.-]{3,30}$/', $pseudo)) {
    $errors[] = 'Pseudo invalide (3 à 30 caractères).';
}
if (!preg_match('/^[\w.-]{3,30}$/', $login)) {
    $errors[] = 'Identifiant invalide (3 à 30 caractères).';
}
if (!preg_match('/^\d{8}$/', $telephone)) {
    $errors[] = 'Téléphone invalide (8 chiffres).';
}

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
    $allowedImages = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
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
