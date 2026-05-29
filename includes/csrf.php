<?php

function generateCsrfToken(string $intent): string {
    if (empty($_SESSION['csrf_tokens'][$intent])) {
        $_SESSION['csrf_tokens'][$intent] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_tokens'][$intent];
}

function validateCsrfToken(string $intent, string $token): bool {
    $stored = $_SESSION['csrf_tokens'][$intent] ?? '';
    return hash_equals($stored, $token);
}


function csrfField(string $intent): string {
    $token = generateCsrfToken($intent);
    return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
}
