<?php
logoutUser();
setFlash('success', 'You have been logged out.');
header('Location: ' . BASE . '/index.php');
exit;
