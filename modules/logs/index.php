<?php

declare(strict_types=1);

requireRole('admin');

$action = trim((string) ($_GET['action'] ?? ''));
$logs = [];
$actionOptions = [];
$error = null;

$perPage = 30;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

try {
    $database = createDatabaseConnection(databaseConfigFromEnvironment());
    $actionOptions = $database->query('SELECT DISTINCT action FROM audit_log ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);

    $whereClause = '';
    $parameters = [];
    if ($action !== '' && in_array($action, $actionOptions, true)) {
        $whereClause = ' WHERE audit_log.action = :action';
        $parameters['action'] = $action;
    }

    $countStatement = $database->prepare('SELECT COUNT(*) FROM audit_log' . $whereClause);
    $countStatement->execute($parameters);
    $totalRows = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    $offset = ($page - 1) * $perPage;

    $statement = $database->prepare(
        'SELECT audit_log.id, audit_log.action, audit_log.entity_type, audit_log.entity_id,
                audit_log.metadata, audit_log.created_at, users.nama_lengkap, users.username
         FROM audit_log
         INNER JOIN users ON users.id = audit_log.user_id'
        . $whereClause
        . ' ORDER BY audit_log.created_at DESC, audit_log.id DESC
         LIMIT ' . $perPage . ' OFFSET ' . $offset
    );
    $statement->execute($parameters);
    $logs = $statement->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Log aktivitas belum dapat dimuat.';
    $totalRows = 0;
    $totalPages = 1;
}

renderView('logs/index', [
    'pageTitle' => 'Log Aktivitas',
    'logs' => $logs,
    'actionOptions' => $actionOptions,
    'action' => $action,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalRows' => $totalRows,
    'perPage' => $perPage,
    'error' => $error,
]);
