<?php

declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 300;
const LOOKUP_MAX_ATTEMPTS = 20;
const LOOKUP_LOCK_SECONDS = 300;

/**
 * Rate limit disimpan di database (bukan $_SESSION), dikunci ke kombinasi
 * scope + IP (+ username untuk login) — jadi tidak bisa dilewati hanya dengan
 * membuang cookie sesi / membuka sesi baru.
 */
function rateLimitAttemptKey(string ...$parts): string
{
    return hash('sha256', implode('|', $parts));
}

function isRateLimited(PDO $database, string $scope, string $key, int $maxAttempts, int $lockSeconds): bool
{
    // Selisih waktu dihitung di MySQL (TIMESTAMPDIFF terhadap NOW()), bukan dengan
    // PHP time() — last_attempt_at disimpan lewat CURRENT_TIMESTAMP MySQL, yang bisa
    // beda timezone dari date_default_timezone_set() aplikasi (mis. server DB di UTC,
    // aplikasi di Asia/Jakarta). Mencampur keduanya membuat rate limit tidak pernah aktif.
    $statement = $database->prepare(
        'SELECT attempt_count, TIMESTAMPDIFF(SECOND, last_attempt_at, NOW()) AS seconds_since_last_attempt
         FROM rate_limit_attempt
         WHERE scope = :scope AND attempt_key = :attempt_key'
    );
    $statement->execute(['scope' => $scope, 'attempt_key' => $key]);
    $row = $statement->fetch();

    if (!is_array($row)) {
        return false;
    }

    return (int) $row['attempt_count'] >= $maxAttempts && (int) $row['seconds_since_last_attempt'] < $lockSeconds;
}

function recordRateLimitAttempt(PDO $database, string $scope, string $key): void
{
    $statement = $database->prepare(
        'INSERT INTO rate_limit_attempt (scope, attempt_key, attempt_count, first_attempt_at, last_attempt_at)
         VALUES (:scope, :attempt_key, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
         ON DUPLICATE KEY UPDATE
             attempt_count = attempt_count + 1,
             last_attempt_at = CURRENT_TIMESTAMP'
    );
    $statement->execute(['scope' => $scope, 'attempt_key' => $key]);
}

function clearRateLimitAttempts(PDO $database, string $scope, string $key): void
{
    $statement = $database->prepare('DELETE FROM rate_limit_attempt WHERE scope = :scope AND attempt_key = :attempt_key');
    $statement->execute(['scope' => $scope, 'attempt_key' => $key]);
}

function isLoginRateLimited(PDO $database, string $username): bool
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = rateLimitAttemptKey(strtolower($username), $ipAddress);

    return isRateLimited($database, 'login', $key, LOGIN_MAX_ATTEMPTS, LOGIN_LOCK_SECONDS);
}

function recordFailedLogin(PDO $database, string $username): void
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    recordRateLimitAttempt($database, 'login', rateLimitAttemptKey(strtolower($username), $ipAddress));
}

function clearLoginAttempts(PDO $database, string $username): void
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    clearRateLimitAttempts($database, 'login', rateLimitAttemptKey(strtolower($username), $ipAddress));
}

function isLookupRateLimited(PDO $database): bool
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    return isRateLimited($database, 'lookup', rateLimitAttemptKey($ipAddress), LOOKUP_MAX_ATTEMPTS, LOOKUP_LOCK_SECONDS);
}

function recordLookupAttempt(PDO $database): void
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    recordRateLimitAttempt($database, 'lookup', rateLimitAttemptKey($ipAddress));
}
