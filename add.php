<?php
/**
 * readers/add.php — Add a new library member
 */
define('BASE_URL', '../');
require_once '../config/db.php';
$page_title = 'Add Reader';
include '../includes/header.php';

$errors = [];
$v = [
    'name'            => '',
    'email'           => '',
    'phone'           => '',
    'address'         => '',
    'member_id'       => '',
    'membership_date' => date('Y-m-d'),
    'status'          => 'active',
];

// Auto-generate next member ID
$last_id  = db_scalar($conn, "SELECT member_id FROM readers ORDER BY id DESC LIMIT 1") ?? 'MEM000';
preg_match('/(\d+)$/', $last_id, $m);
$next_id  = 'MEM' . str_pad((intval($m[1] ?? 0) + 1), 3, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = db_escape($conn, $_POST['name']            ?? '');
    $email    = db_escape($conn, $_POST['email']           ?? '');
    $phone    = db_escape($conn, $_POST['phone']           ?? '');
    $address  = db_escape($conn, $_POST['address']         ?? '');
    $mem_id   = db_escape($conn, $_POST['member_id']       ?? '');
    $mem_date = db_escape($conn, $_POST['membership_date'] ?? date('Y-m-d'));
    $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $v        = compact('name','email','phone','address','status');
    $v['member_id']       = $mem_id;
    $v['membership_date'] = $mem_date;

    // Validation
    if (empty($name))   $errors[] = 'Full name is required.';
    if (empty($mem_id)) $errors[] = 'Member ID is required.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

    // Unique member ID check
    if ($mem_id && db_scalar($conn, "SELECT COUNT(*) FROM readers WHERE member_id='$mem_id'") > 0)
        $errors[] = "Member ID '$mem_id' already exists.";

    if (empty($errors)) {
        $ok = mysqli_query($conn,
            "INSERT INTO readers (name, email, phone, address, member_id, membership_date, status)
             VALUES ('$name','$email','$phone','$address','$mem_id','$mem_date','$status')"
        );
        if ($ok) {
            header('Location: index.php?msg=added');
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<div class="page-header">
    <h1>👤 Add Reader</h1>
    <a href="index.php" class="btn btn-secondary">← Back to Readers</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ⚠️ <strong>Please fix:</strong>
    <ul style="margin:6px 0 0 14px;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Reader Details</h2></div>
    <div class="card-body">
        <form method="POST" action="add.php" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($v['name']) ?>"
                           placeholder="e.g. Alice Johnson" required>
                </div>

                <div class="form-group">
                    <label for="member_id">
                        Member ID *
                        <small class="text-muted">(auto-suggested)</small>
                    </label>
                    <input type="text" id="member_id" name="member_id"
                           value="<?= htmlspecialchars($v['member_id'] ?: $next_id) ?>"
                           placeholder="e.g. MEM007" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($v['email']) ?>"
                           placeholder="alice@email.com">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($v['phone']) ?>"
                           placeholder="9876543210">
                </div>

                <div class="form-group">
                    <label for="membership_date">Membership Date</label>
                    <input type="date" id="membership_date" name="membership_date"
                           value="<?= htmlspecialchars($v['membership_date']) ?>">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active"   <?= $v['status']==='active'   ? 'selected':'' ?>>✅ Active</option>
                        <option value="inactive" <?= $v['status']==='inactive' ? 'selected':'' ?>>❌ Inactive</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"
                              placeholder="Street, City, State…"><?= htmlspecialchars($v['address']) ?></textarea>
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">💾 Save Reader</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
