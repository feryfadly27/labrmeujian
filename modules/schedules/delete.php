<?php

declare(strict_types=1);

requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Invalid request'); }
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) { http_response_code(400); exit('Invalid schedule ID'); }
try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $statement = $database->prepare('DELETE FROM jadwal_ujian WHERE id = :id');
    $statement->execute(['id' => $id]);

    flashSuccess('Jadwal ujian berhasil dihapus.');
    header('Location: ' . $app['base_url'] . 'schedules.php', true, 303); exit;
} catch (PDOException $exception) { error_log($exception->getMessage()); http_response_code(409); exit('Jadwal yang sudah memiliki hasil generate tidak dapat dihapus.'); }
