<?php
require_once 'config.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Clear display-name cookie so the next guest doesn’t inherit it
setcookie('pbj_display_name', '', time() - 3600, '/');

header('Location: /?logout=1');
exit();
