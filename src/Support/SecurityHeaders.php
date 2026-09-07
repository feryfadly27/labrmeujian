<?php

declare(strict_types=1);

function cspNonce(): string
{
    static $nonce = null;

    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }

    return $nonce;
}

function sendSecurityHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    $nonce = cspNonce();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
        . "img-src 'self' data:; "
        . "font-src 'self' data: https://fonts.gstatic.com; "
        . "frame-ancestors 'none'; "
        . "base-uri 'self'; "
        . "form-action 'self'"
    );

    if (($_SERVER['HTTPS'] ?? 'off') !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
