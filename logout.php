<?php
/**
 * auth/logout.php
 * Destroys the session and redirects to login with a message.
 */
session_start();
session_unset();
session_destroy();

// Prevent browser from caching logged-in pages after logout
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Location: login.php?msg=logged_out');
exit;
