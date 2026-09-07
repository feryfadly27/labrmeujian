<?php

declare(strict_types=1);

requireRole('admin');

$error = null;
$result = null;
$maxBytes = 5 * 1024 * 1024;
$uploadDirectory = BASE_PATH . '/storage/uploads';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = 'Permintaan tidak valid.';
    } elseif (!isset($_FILES['student_file']) || !is_array($_FILES['student_file'])) {
        $error = 'File Excel wajib dipilih.';
    } else {
        $file = $_FILES['student_file'];
        $originalFilename = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $fileError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $fileSize = (int) ($file['size'] ?? 0);
        $mimeType = $temporaryPath !== '' && is_uploaded_file($temporaryPath)
            ? (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath)
            : null;
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ];

        if ($fileError !== UPLOAD_ERR_OK) {
            $error = 'Upload file gagal.';
        } elseif ($extension !== 'xlsx' || !in_array($mimeType, $allowedMimeTypes, true)) {
            $error = 'File harus berupa Excel .xlsx yang valid.';
        } elseif ($fileSize < 1 || $fileSize > $maxBytes) {
            $error = 'Ukuran file maksimal 5 MB.';
        } elseif (!is_uploaded_file($temporaryPath)) {
            $error = 'File upload tidak valid.';
        } elseif (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
            $error = 'Folder penyimpanan upload belum tersedia.';
        } else {
            $storedFilename = bin2hex(random_bytes(16)) . '.xlsx';
            $storedPath = $uploadDirectory . '/' . $storedFilename;
            if (!move_uploaded_file($temporaryPath, $storedPath)) {
                $error = 'File upload belum dapat disimpan.';
            } else {
                try {
                    $database = createDatabaseConnection(databaseConfigFromEnvironment());
                    $result = importStudentsFromXlsx($database, $storedPath, $originalFilename, $storedFilename, (int) currentUser()['id']);
                } catch (Throwable $exception) {
                    error_log($exception->getMessage());
                    $error = $exception instanceof InvalidArgumentException
                        ? $exception->getMessage()
                        : 'Isi file belum dapat diproses.';
                }
            }
        }
    }
}

renderView('students/import', [
    'pageTitle' => 'Import Mahasiswa dari Excel',
    'formAction' => $app['base_url'] . 'students/import.php',
    'error' => $error,
    'result' => $result,
]);
