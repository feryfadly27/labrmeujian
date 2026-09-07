<?php

declare(strict_types=1);

function requireAuthentication(): void
{
    if (!isAuthenticated()) {
        header('Location: login.php', true, 302);
        exit;
    }
}

function requireRole(string ...$roles): void
{
    requireAuthentication();
    $user = currentUser();

    if ($user === null || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        renderView('errors/forbidden', ['pageTitle' => 'Akses Ditolak']);
        exit;
    }
}
