<?php
/**
 * readers/index.php — All library members
 */
define('BASE_URL', '../');
require_once '../config/db.php';
$page_title = 'Readers';
include '../includes/header.php';

$msg    = $_GET['msg']    ?? '';
$search = db_escape($conn, $_GET['search'] ?? '');
$where  = $search
    ? "WHERE name LIKE '%$search%'
          OR member_id LIKE '%$search%'
          OR email LIKE '%$search%'
          OR phone LIKE '%$search%'"
    : '';

$result = mysqli_query($conn, "SELECT * FROM readers $where ORDER BY created_at DESC");
$count  = $result ? mysqli_num_rows($result) : 0;
?>
<div class="page-header">
    <h1>👥 Readers</h1>
    <a href="add.php" class="btn btn-primary">➕ Add Reader</a>
</div>

<?php
if ($msg === 'added')         echo '<div class="alert alert-success">✅ Reader added successfully!</div>';
if ($msg === 'updated')       echo '<div class="alert alert-success">✅ Reader updated successfully!</div>';
if ($msg === 'deleted')       echo '<div class="alert alert-success">✅ Reader deleted successfully!</div>';
if ($msg === 'cannot_delete') echo '<div class="alert alert-danger">🚫 Cannot delete — reader has active book loans. Process returns first.</div>';
if ($msg === 'not_found')     echo '<div class="alert alert-warning">⚠️ Reader not found.</div>';
?>

<div class="card">
    <div class="card-header">
        <h2>All Members <span class="badge badge-info"><?= $count ?></span></h2>
        <form method="GET" action="index.php" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <input type="text" id="liveSearch" name="search"
                       placeholder="Search name, ID, email, phone…"
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit">🔍</button>
            </div>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-secondary btn-sm">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            if ($result && $count > 0):
                while ($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>
                    <code style="background:#ede9fe;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.78rem;">
                        <?= htmlspecialchars($row['member_id']) ?>
                    </code>
                </td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? '—') ?></td>
                <td><?= $row['membership_date'] ? date('d M Y', strtotime($row['membership_date'])) : '—' ?></td>
                <td>
                    <span class="badge <?= $row['status']==='active' ? 'badge-success':'badge-secondary' ?>">
                        <?= $row['status']==='active' ? '✅ Active' : '❌ Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                        <form method="POST" action="delete.php" class="delete-form">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="8" class="text-center text-muted" style="padding:32px;">
                    <?= $search ? '🔍 No readers match your search.' : '📭 No readers registered yet.' ?>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
