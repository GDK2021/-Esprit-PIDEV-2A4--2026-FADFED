<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/user.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

       
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    private function existe(string $email, string $username): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1');
        $stmt->execute(['email' => $email, 'username' => $username]);
        return (bool) $stmt->fetch();
    }

    private function insertUser(UserModel $user): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)'
        );

        return $stmt->execute([
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'password' => $user->getPassword(),
            'role'     => $user->getRole(),
        ]);
    }
    
    public function register(array $post): array
    {
        $errors = [];

        $username        = trim($post['username'] ?? '');
        $email           = trim($post['email'] ?? '');
        $password        = $post['password'] ?? '';
        $passwordConfirm = $post['password_confirm'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $errors[] = "required.";
        }
        if (!preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $username)) {
            $errors[] = "username n'est pas alphanumerique ";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (strlen($password) < 8) {
            $errors[] = "verifier la longueur";
        }

        if ($password !== $passwordConfirm) {
            $errors[] = "Password incorrect";
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->existe($email, $username)) {
            $errors[] = " email ou username existe deja.";
            return $errors;
        }

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        
        $user = new UserModel();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($hashedPassword);
        $user->setRole('user');

        if (!$this->insertUser($user)) {
            $errors[] = "An error occurred while creating your account.";
        }

        return $errors; 
    }   

    private function findByEmail(string $email)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row) {
            return UserModel::fromArray($row);
        }
        return null;
    }

        public function login(array $post): array
    {
        $errors = [];

        $email    = '';
        $password = '';

        if (isset($post['email'])) {
            $email = trim($post['email']);
        }
        if (isset($post['password'])) {
            $password = $post['password'];
        }

        if ($email === '' || $password === '') {
            $errors[] = "Please enter your email and password.";
            return $errors;
        }

        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $errors[] = "Incorrect email or password.";
            return $errors;
        }

        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->getId();
        $_SESSION['username']      = $user->getUsername();
        $_SESSION['role']          = $user->getRole();
        $_SESSION['last_activity'] = time();

        return []; 
    }

        public function logout(): void
    {
        $_SESSION = [];
    }
    public function findById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?action=login');
            exit;
        }
    }
    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && ($_SESSION['role'] ) === $role;
    }
    public function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY id ASC');
        $rows = $stmt->fetchAll();

        $users = [];
        foreach ($rows as $row) {
            $users[] = UserModel::fromArray($row);
        }

        return $users;
    }


    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        $rows = $stmt->fetchAll();

        return count($rows);
    }

    public function countAllUsers(): int
    {
        $stmt = $this->db->query('SELECT * FROM users');
        $rows = $stmt->fetchAll();

        return count($rows);
    }
}