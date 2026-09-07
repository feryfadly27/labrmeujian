<?php

declare(strict_types=1);

if (isAuthenticated()) {
    header('Location: dashboard.php', true, 302);
    exit;
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } elseif ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $database = createDatabaseConnection(databaseConfigFromEnvironment());

            if (isLoginRateLimited($database, $username)) {
                $error = 'Terlalu banyak percobaan. Silakan coba lagi beberapa menit lagi.';
            } else {
                $statement = $database->prepare(
                    'SELECT id, username, password_hash, nama_lengkap, role
                     FROM users
                     WHERE username = :username
                     LIMIT 1'
                );
                $statement->execute(['username' => $username]);
                $user = $statement->fetch();

                if (!is_array($user) || !password_verify($password, $user['password_hash'])) {
                    recordFailedLogin($database, $username);
                    $error = 'Username atau password tidak sesuai.';
                } else {
                    clearLoginAttempts($database, $username);
                    loginUser($user);

                    $updateLogin = $database->prepare(
                        'UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id'
                    );
                    $updateLogin->execute(['id' => $user['id']]);

                    header('Location: dashboard.php', true, 302);
                    exit;
                }
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $error = 'Login belum dapat diproses. Silakan coba lagi.';
        }
    }
}

renderView('auth/login', [
    'pageTitle' => 'Login',
    'csrfToken' => csrfToken(),
    'error' => $error,
    'username' => $username,
]);
