<?php
/**
 * auth/login.php
 * Staff login with session management. Links to register page.
 */
session_start();

if (isset($_SESSION['staff_id'])) {
    header('Location: ../index.php');
    exit;
}

define('BASE_URL', '../');
require_once '../config/db.php';

$error   = '';
$success = '';

// Show success message after registration
if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $success = 'Account created! Please sign in below.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success = 'You have been signed out successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $sql    = "SELECT id, name, username, role FROM staff
                   WHERE username = '$username' AND password = MD5('$password')
                   LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $staff = mysqli_fetch_assoc($result);
            $_SESSION['staff_id']   = $staff['id'];
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_user'] = $staff['username'];
            $_SESSION['staff_role'] = $staff['role'];
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | LibraryMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-footer-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
            font-size: .85rem;
            color: var(--text-muted);
            flex-wrap: wrap;
        }
        .login-footer-links a {
            color: var(--primary);
            font-weight: 600;
        }
        .login-footer-links span { color: var(--border); }

        .demo-box {
            background: #f8fafc;
            border: 1px dashed var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 20px;
            font-size: .8rem;
            color: var(--text-muted);
            text-align: center;
        }
        .demo-box strong { color: var(--text); display: block; margin-bottom: 6px; }
        .demo-box code {
            background: #e0e7ff;
            color: var(--primary-dark);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: .78rem;
        }
        .demo-row { margin-top: 4px; }
    </style>
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <span class="icon">📚</span>
        <h1>LibraryMS</h1>
        <p>Welcome back! Please sign in.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <div class="form-group" style="margin-bottom:16px;">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="Enter your username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   required autofocus autocomplete="username">
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password"
                   required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;justify-content:center;padding:12px;font-size:1rem;">
            🔐 Sign In
        </button>
    </form>

    <!-- Navigation Links -->
    <div class="login-footer-links">
        <span>New to LibraryMS?</span>
        <a href="register.php">📝 Create an Account →</a>
    </div>

    <!-- Demo Credentials -->
    <div class="demo-box">
        <strong>🧪 Demo Credentials</strong>
        <div class="demo-row">Admin: <code>admin</code> / <code>admin123</code></div>
        <div class="demo-row">Librarian: <code>librarian1</code> / <code>lib123</code></div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
