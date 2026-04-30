<?php
/**
 * auth/register.php — now redirects to login with success msg after registration.
 */
session_start();
if (isset($_SESSION['staff_id'])) { header('Location: ../index.php'); exit; }

define('BASE_URL', '../');
require_once '../config/db.php';

define('ADMIN_SECRET', 'ADMIN2024');

$errors = [];
$v = ['name'=>'','username'=>'','email'=>'','phone'=>'','role'=>'librarian'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = db_escape($conn, $_POST['name']     ?? '');
    $username = db_escape($conn, $_POST['username'] ?? '');
    $email    = db_escape($conn, $_POST['email']    ?? '');
    $phone    = db_escape($conn, $_POST['phone']    ?? '');
    $password = trim($_POST['password']         ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin','librarian']) ? $_POST['role'] : 'librarian';
    $secret   = trim($_POST['admin_secret']     ?? '');
    $v        = compact('name','username','email','phone','role');

    // Validation
    if (empty($name))     $errors[] = 'Full name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username))
        $errors[] = 'Username: letters, numbers and underscores only.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if ($role === 'admin' && $secret !== ADMIN_SECRET)
        $errors[] = 'Incorrect admin secret code.';

    if (empty($errors)) {
        // Unique username
        if (db_scalar($conn, "SELECT COUNT(*) FROM staff WHERE username='$username'") > 0)
            $errors[] = 'Username already taken. Choose another.';
        // Unique email
        if ($email && db_scalar($conn, "SELECT COUNT(*) FROM staff WHERE email='$email'") > 0)
            $errors[] = 'An account with this email already exists.';
    }

    if (empty($errors)) {
        $ok = mysqli_query($conn,
            "INSERT INTO staff (name, username, password, role, phone, email)
             VALUES ('$name','$username', MD5('$password'), '$role', '$phone', '$email')"
        );
        if ($ok) {
            // Redirect to login with success banner
            header('Location: login.php?msg=registered');
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | LibraryMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-body {
            display:flex; align-items:center; justify-content:center;
            min-height:100vh; padding:24px;
            background: linear-gradient(135deg, #312e81 0%, #0891b2 100%);
        }
        .register-card {
            background:#fff; border-radius:16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.18);
            padding:40px 36px; width:100%; max-width:560px;
            animation: fadeUp .4s ease;
        }
        .reg-logo { text-align:center; margin-bottom:24px; }
        .reg-logo .icon { font-size:42px; display:block; margin-bottom:6px; }
        .reg-logo h1 { font-size:1.4rem; font-weight:700; color:var(--primary); }
        .reg-logo p  { color:var(--text-muted); font-size:.85rem; }

        .role-toggle { display:flex; border:1.5px solid var(--border); border-radius:8px; overflow:hidden; }
        .role-toggle input[type="radio"] { display:none; }
        .role-toggle label {
            flex:1; text-align:center; padding:10px 6px; cursor:pointer;
            font-size:.85rem; font-weight:600; color:var(--text-muted);
            background:var(--bg); transition:all .2s;
            text-transform:none; letter-spacing:0;
        }
        .role-toggle input[type="radio"]:checked + label { background:var(--primary); color:#fff; }

        .divider { display:flex; align-items:center; gap:10px; margin:18px 0 14px;
                   color:var(--text-muted); font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:var(--border); }

        #strengthBar  { height:4px; border-radius:4px; background:var(--border); margin-bottom:4px; transition:all .3s; }
        #strengthFill { height:100%; border-radius:4px; width:0%; transition:all .3s; }
    </style>
</head>
<body class="register-body">

<div class="register-card">
    <div class="reg-logo">
        <span class="icon">📚</span>
        <h1>Create Account</h1>
        <p>LibraryMS — Staff Registration</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="margin-bottom:18px;">
        ⚠️ <strong>Fix the following:</strong>
        <ul style="margin:6px 0 0 14px;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

        <!-- Role -->
        <div class="form-group" style="margin-bottom:18px;">
            <label>Account Role</label>
            <div class="role-toggle">
                <input type="radio" id="role_lib" name="role" value="librarian"
                       <?= $v['role'] !== 'admin' ? 'checked' : '' ?> onchange="toggleSecret(this)">
                <label for="role_lib">📚 Librarian</label>
                <input type="radio" id="role_admin" name="role" value="admin"
                       <?= $v['role'] === 'admin' ? 'checked' : '' ?> onchange="toggleSecret(this)">
                <label for="role_admin">🛡️ Admin</label>
            </div>
        </div>

        <!-- Admin Secret -->
        <div id="adminSecretBox" style="<?= $v['role']==='admin' ? '' : 'display:none;' ?>margin-bottom:14px;">
            <div class="form-group">
                <label for="admin_secret">🔑 Admin Secret Code</label>
                <input type="password" id="admin_secret" name="admin_secret"
                       placeholder="Required for admin role">
                <small class="text-muted" style="display:block;margin-top:4px;">
                    Contact your system admin for this code.
                </small>
            </div>
        </div>

        <div class="divider">Personal Info</div>

        <div class="form-grid" style="gap:14px;margin-bottom:14px;">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($v['name']) ?>"
                       placeholder="e.g. John Smith" required>
            </div>
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($v['username']) ?>"
                       placeholder="e.g. john_lib" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($v['email']) ?>"
                       placeholder="john@library.com">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone"
                       value="<?= htmlspecialchars($v['phone']) ?>"
                       placeholder="9876543210">
            </div>
        </div>

        <div class="divider">Set Password</div>

        <div class="form-grid" style="gap:14px;margin-bottom:8px;">
            <div class="form-group">
                <label for="password">Password * <small class="text-muted">(min 6 chars)</small></label>
                <input type="password" id="password" name="password"
                       placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Re-enter password" required>
            </div>
        </div>

        <!-- Strength bar -->
        <div style="margin-bottom:20px;">
            <div id="strengthBar"><div id="strengthFill"></div></div>
            <div id="strengthText" style="font-size:.72rem;color:var(--text-muted);margin-top:3px;"></div>
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;justify-content:center;padding:12px;font-size:1rem;">
            🚀 Create Account
        </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted);">
        Already have an account?
        <a href="login.php" style="font-weight:600;color:var(--primary);">Sign in →</a>
    </p>
</div>

<script>
function toggleSecret(radio) {
    document.getElementById('adminSecretBox').style.display =
        radio.value === 'admin' ? 'block' : 'none';
}

// Password strength
document.getElementById('password')?.addEventListener('input', function () {
    const pw = this.value;
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    if (!pw) { fill.style.width='0%'; text.textContent=''; return; }
    let s = 0;
    if (pw.length >= 6)  s++;
    if (pw.length >= 10) s++;
    if (/[A-Z]/.test(pw)) s++;
    if (/[0-9]/.test(pw)) s++;
    if (/[^a-zA-Z0-9]/.test(pw)) s++;
    const lvls=[
        {c:'#ef4444',l:'Very Weak', p:'20%'},
        {c:'#f97316',l:'Weak',      p:'40%'},
        {c:'#eab308',l:'Fair',      p:'60%'},
        {c:'#22c55e',l:'Strong',    p:'80%'},
        {c:'#10b981',l:'Very Strong',p:'100%'},
    ];
    const lvl = lvls[Math.min(s-1,4)]||lvls[0];
    fill.style.width=lvl.p; fill.style.background=lvl.c;
    text.textContent='🔒 Strength: '+lvl.l; text.style.color=lvl.c;
});

// Confirm match indicator
document.getElementById('confirm_password')?.addEventListener('input', function () {
    const pw = document.getElementById('password').value;
    this.style.borderColor = this.value
        ? (this.value===pw ? '#10b981' : '#ef4444')
        : '';
});
</script>

<script src="../assets/js/main.js"></script>
</body>
</html>
