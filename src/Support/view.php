<?php

declare(strict_types=1);

function renderView(string $template, array $data = []): void
{
    $templatePath = BASE_PATH . '/templates/' . $template . '.php';

    if (!is_file($templatePath)) {
        throw new RuntimeException('Template not found.');
    }

    global $app;
    $data['app'] = $app;
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/templates/layout/header.php';
    require $templatePath;
    require BASE_PATH . '/templates/layout/footer.php';
}
