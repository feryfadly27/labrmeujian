<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

$app = require BASE_PATH . '/config/app.php';
date_default_timezone_set($app['timezone']);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Support/view.php';
require_once BASE_PATH . '/src/Support/AuditLog.php';
require_once BASE_PATH . '/src/Support/SecurityHeaders.php';
require_once BASE_PATH . '/src/Support/IndonesianDate.php';
require_once BASE_PATH . '/src/Support/Flash.php';
require_once BASE_PATH . '/src/Auth/Session.php';
require_once BASE_PATH . '/src/Auth/RateLimiter.php';
require_once BASE_PATH . '/src/Auth/Guard.php';
require_once BASE_PATH . '/src/Security/Csrf.php';
require_once BASE_PATH . '/src/Import/StudentXlsxImporter.php';
require_once BASE_PATH . '/src/Export/AttendanceData.php';
require_once BASE_PATH . '/src/Export/AttendanceTemplate.php';

startSecureSession();
sendSecurityHeaders();
