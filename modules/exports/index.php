<?php

declare(strict_types=1);

requireAuthentication();
$generateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$format = strtolower(trim((string) ($_GET['format'] ?? '')));
if ($generateId === false || $generateId === null || $generateId < 1 || !in_array($format, ['pdf', 'xlsx', 'docx', 'print'], true)) {
    http_response_code(400);
    exit('Invalid export request');
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $data = loadAttendanceExportData($database, $generateId);
    if ($format === 'pdf') {
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml(renderAttendanceHtml($data), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('daftar-hadir-' . $generateId . '.pdf', ['Attachment' => true]);
        exit;
    }

    if ($format === 'print') {
        header('Content-Type: text/html; charset=UTF-8');
        echo renderPrintableAttendanceHtml($data);
        exit;
    }

    require BASE_PATH . '/modules/exports/' . $format . '.php';
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Dokumen belum dapat dibuat.');
}
