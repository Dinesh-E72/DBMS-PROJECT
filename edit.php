<?php
/**
 * readers/edit.php — Edit an existing reader
 */
define('BASE_URL', '../');
require_once '../config/db.php';
$page_title = 'Edit Reader';
include '../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$reader = db_row($conn, "SELECT * FROM readers WHERE id = $id");
if (!$reader) { header('Location: index.php?msg=not_found'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = db_escape($conn, $_POST['name']            ?? '');
    $email    = db_escape($conn, $_POST['email']           ?? '');
    $phone    = db_escape($conn, $_POST['phone']           ?? '');
    $address  = db_escape($conn, $_POST['address']         ?? '');
    $mem_date = db_escape($conn, $_POST['membership_date'] ?? '');
    $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($name)) $errors[] = 'Full name is required.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';

    if (empty($errors)) {
        $ok = mysqli_query($conn,
            "UPDATE readers SET
                name='$name', email='$email', phone='$phone',
                address='$address', membership_date='$mem_date', status='$status'
             WHERE id = $id"
        );
        if ($ok) {
            header('Location: index.php?msg=updated');
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
    // Repopulate on error
    $reader = array_merge($reader, compact('name','email','phone','address','status'));
    $reader['membership_date'] = $mem_date;
}

// Borrow history for this reader
$history = mysqli_query($conn,
    "SELECT b.title, bi.issue_date, bi.due_date, bi.return_date, bi.status, bi.fine
     FROM book_issues bi
     JOIN books b ON bi.book_id = b.id
     WHERE bi.reader_id = $id
     ORDER BY bi.issue_date DESC
     LIMIT 10"
);
?>
<div class="page-header">
    <h1>✏️ Edit Reader</h1>
    <a href="index.php" class="btn btn-secondary">← Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ⚠️ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;align-items:start;">

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h2>
                Editing: <?= htmlspecialchars($reader['name']) ?>
                <code style="font-size:.75rem;margin-left:8px;"><?= htmlspecialchars($reader['member_id']) ?></code>
            </h2>
        </div>
        <div class="card-body">
            <form method="POST" action="edit.php?id=<?= $id ?>" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name"
                               value="<?= htmlspecialchars($reader['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Member ID <small class="text-muted">(cannot change)</small></label>
                        <input type="text" value="<?= htmlspecialchars($reader['member_id']) ?>"
                               disabled style="background:var(--bg);cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($reader['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($reader['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="membership_date">Membership Date</label>
                        <input type="date" id="membership_date" name="membership_date"
                               value="<?= htmlspecialchars($reader['membership_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active"   <?= $reader['status']==='active'   ? 'selected':'' ?>>✅ Active</option>
                            <option value="inactive" <?= $reader['status']==='inactive' ? 'selected':'' ?>>❌ Inactive</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?= htmlspecialchars($reader['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:20px;">
                    <button type="submit" class="btn btn-primary">💾 Update Reader</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Borrow History -->
    <div class="card">
        <div class="card-header"><h2>📋 Borrow History</h2></div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Book</th><th>Issued</th><th>Due</th><th>Status</th><th>Fine</th></tr></thead>
                <tbody>
                <?php
                if ($history && mysqli_num_rows($history) > 0):
                    while ($h = mysqli_fetch_assoc($history)):
                ?>
                <tr>
                    <td><?= htmlspecialchars($h['title']) ?></td>
                    <td><?= $h['issue_date'] ?></td>
                    <td><?= $h['due_date'] ?></td>
                    <td>
                        <?php match ($h['status']) {
                            'returned' => print '<span class="badge badge-success">Returned</span>',
                            'overdue'  => print '<span class="badge badge-danger">Overdue</span>',
                            default    => print '<span class="badge badge-warning">Issued</span>',
                        }; ?>
                    </td>
                    <td><?= $h['fine'] > 0 ? '₹'.$h['fine'] : '—' ?></td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr><td colspan="5" class="text-muted text-center" style="padding:20px;">No borrow history.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
