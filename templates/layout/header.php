<?php

require_once BASE_PATH . '/src/Support/escape.php';
$pageTitle = $pageTitle ?? $app['name'];
$currentPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$currentSection = explode('/', trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'))[0] ?? '';
$isActive = static fn (array $pages, string $section = ''): string => in_array($currentPage, $pages, true) || $currentSection === $section ? ' active' : '';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($bulletinPage ?? false): ?><meta name="color-scheme" content="light"><?php endif; ?>
    <title><?= escapeHtml($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="<?= escapeHtml($app['base_url']) ?>assets/app.css" rel="stylesheet">
</head>
<body class="<?= ($bulletinPage ?? false) ? 'bulletin-page' : 'bg-light' ?>">
<header>
<?php if (isAuthenticated()): ?>
    <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
        <div class="offcanvas-header sidebar-header">
            <a class="sidebar-brand" id="appSidebarLabel" href="<?= escapeHtml($app['base_url']) ?>dashboard.php">Jadwal Lab</a>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Tutup menu"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div class="sidebar-filter">
                <label class="visually-hidden" for="sidebarFilter">Filter menu</label>
                <input id="sidebarFilter" type="search" placeholder="Filter menu..." autocomplete="off">
            </div>
            <div class="sidebar-caption">MENU UTAMA</div>
            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <a class="<?= $isActive(['dashboard.php']) ?>" data-menu-label="dashboard" href="<?= escapeHtml($app['base_url']) ?>dashboard.php"><span class="sidebar-icon">⌂</span><span>Dashboard</span></a>
                <a class="<?= $isActive(['classes.php'], 'classes') ?>" data-menu-label="data kelas" href="<?= escapeHtml($app['base_url']) ?>classes.php"><span class="sidebar-icon">○</span><span>Data Kelas</span></a>
                <a class="<?= $isActive(['students.php'], 'students') ?>" data-menu-label="data mahasiswa" href="<?= escapeHtml($app['base_url']) ?>students.php"><span class="sidebar-icon">○</span><span>Data Mahasiswa</span></a>
                <a class="<?= $isActive(['courses.php'], 'courses') ?>" data-menu-label="mata kuliah" href="<?= escapeHtml($app['base_url']) ?>courses.php"><span class="sidebar-icon">▣</span><span>Mata Kuliah</span></a>
                <a class="<?= $isActive(['dosen.php'], 'dosen') ?>" data-menu-label="data dosen" href="<?= escapeHtml($app['base_url']) ?>dosen.php"><span class="sidebar-icon">◆</span><span>Data Dosen</span></a>
                <a class="<?= $isActive(['schedules.php'], 'schedules') ?>" data-menu-label="jadwal ujian" href="<?= escapeHtml($app['base_url']) ?>schedules.php"><span class="sidebar-icon">□</span><span>Jadwal Ujian</span></a>
                <a class="<?= $isActive(['generate.php']) ?>" data-menu-label="generate workstation" href="<?= escapeHtml($app['base_url']) ?>generate.php"><span class="sidebar-icon">✦</span><span>Generate Workstation</span></a>
                <a class="<?= $isActive(['results.php']) ?>" data-menu-label="hasil generate" href="<?= escapeHtml($app['base_url']) ?>results.php"><span class="sidebar-icon">▤</span><span>Hasil Generate</span></a>
                <?php if ((currentUser()['role'] ?? null) === 'admin'): ?>
                    <a class="<?= $isActive(['users.php'], 'users') ?>" data-menu-label="manajemen user" href="<?= escapeHtml($app['base_url']) ?>users.php"><span class="sidebar-icon">☺</span><span>Manajemen User</span></a>
                    <a class="<?= $isActive(['logs.php']) ?>" data-menu-label="log aktivitas" href="<?= escapeHtml($app['base_url']) ?>logs.php"><span class="sidebar-icon">≡</span><span>Log Aktivitas</span></a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer mt-auto">
                <form method="post" action="<?= escapeHtml($app['base_url']) ?>logout.php">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <button class="btn btn-outline-light w-100" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </aside>
    <div class="app-content-shell">
        <div class="topbar bg-white border-bottom">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar">Menu</button>
            <span class="topbar-title">Administrasi Ujian Laboratorium</span>
        </div>
<?php endif; ?>
</header>
<main class="container py-4<?= isAuthenticated() ? ' app-main' : '' ?>">
<?php $flashSuccessMessage = takeFlashSuccess(); ?>
<?php if ($flashSuccessMessage !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= escapeHtml($flashSuccessMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
