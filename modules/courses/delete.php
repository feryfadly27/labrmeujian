<?php

declare(strict_types=1);

requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Invalid request'); }
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) { http_response_code(400); exit('Invalid course ID'); }
try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $find = $database->prepare('SELECT kode_mk FROM mata_kuliah WHERE id = :id');
    $find->execute(['id' => $id]);
    $deletedCode = $find->fetchColumn();

    $statement = $database->prepare('DELETE FROM mata_kuliah WHERE id = :id');
    $statement->execute(['id' => $id]);

    flashSuccess(($deletedCode !== false ? 'Mata kuliah "' . $deletedCode . '"' : 'Data mata kuliah') . ' berhasil dihapus.');
    header('Location: ' . $app['base_url'] . 'courses.php', true, 303); exit;
} catch (PDOException $exception) { error_log($exception->getMessage()); http_response_code(409); exit('Mata kuliah yang sudah digunakan dalam jadwal tidak dapat dihapus.'); }
