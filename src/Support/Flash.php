<?php

declare(strict_types=1);

function flashSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
}

/**
 * Ambil dan hapus pesan flash sekaligus (tampil sekali saja, tidak nempel
 * setelah reload/navigasi berikutnya).
 */
function takeFlashSuccess(): ?string
{
    if (!isset($_SESSION['flash_success'])) {
        return null;
    }

    $message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);

    return is_string($message) ? $message : null;
}
