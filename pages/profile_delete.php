<?php

requireLogin();

if (!validateCsrfToken('delete-account', $_POST['_token'] ?? '')) {
    setFlash('danger', 'Invalid request.');
    header('Location: ' . BASE . '/index.php?route=/profile');
    exit;
}

$userId = getCurrentUserId();
logoutUser();        
deleteUser($userId); 

setFlash('success', 'Your account has been deleted.');
header('Location: ' . BASE . '/index.php');
exit;
