<?php

define('BASE_URL', '../');
require_once '../config/db.php';
$page_title = 'Return Book';
include '../includes/header.php';

define('FINE_PER_DAY', 5);  

$errors      = [];
$success_msg = '';


function get_issued($conn) {
    return mysqli_query($conn,
        "SELECT bi.id, bi.issue_date, bi.due_date,
                b.title, b.id AS book_id,
                r.name AS reader_name, r.member_id,
                DATEDIFF(CURDATE(), bi.due_date) AS days_overdue
         FROM   book_issues bi
         JOIN   books   b ON bi.book_id   = b.id
         JOIN   readers r ON bi.reader_id = r.id
         WHERE  bi.status IN ('issued','overdue')
         ORDER  BY bi.due_date ASC"
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_id    = intval(db_escape($conn, $_POST['issue_id']    ?? 0));
    $return_date = db_escape($conn, $_POST['return_date'] ?? date('Y-m-d'));

    if (!$issue_id)          $errors[] = 'Please select an issue record.';
    if (empty($return_date)) $errors[] = 'Return date is required.';

    if (empty($errors)) {
        // Fetch the issue record
        $iss = db_row($conn,
            "SELECT * FROM book_issues
             WHERE id = $issue_id AND status IN ('issued','overdue')"
        );

        if (!$iss) {
            $errors[] = 'Issue record not found or already returned.';
        } elseif ($return_date < $iss['issue_date']) {
            $errors[] = 'Return date cannot be before the issue date (' . $iss['issue_date'] . ').';
        } else {
            // --- Fine calculation ---
            $fine = 0;
            $due  = new DateTime($iss['due_date']);
            $ret  = new DateTime($return_date);
            if ($ret > $due) {
                $days_overdue = (int) $due->diff($ret)->days;
                $fine = $days_overdue * FINE_PER_DAY;
            }

            // Update issue record → returned
            $ok = mysqli_query($conn,
                "UPDATE book_issues
                 SET return_date = '$return_date',
                     fine        = $fine,
                     status      = 'returned'
                 WHERE id = $issue_id"
            );

            if ($ok) {
                // Restore one available copy
                mysqli_query($conn,
                    "UPDATE books
                     SET available_copies = available_copies + 1
                     WHERE id = " . intval($iss['book_id'])
                );

                $days_str = ($fine > 0)
                    ? " Fine collected: <strong>₹$fine</strong> (" . (int)$due->diff($ret)->days . " overdue days)."
                    : " No fine — returned on time!";
                $success_msg = "✅ Book returned successfully.$days_str";
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}

$issued_res = get_issued($conn);
?>

<div class="page-header">
    <h1>📥 Return Book</h1>
    <a href="index.php" class="btn btn-secondary">← All Issues</a>
</div>

<?php if ($success_msg): ?>
<div class="alert alert-success"><?= $success_msg ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ⚠️ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:22px;align-items:start;">

    <!-- ── Return Form ─────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header"><h2>Process Return</h2></div>
        <div class="card-body">
            <form method="POST" action="return.php" novalidate>

                <div class="form-group" style="margin-bottom:16px;">
                    <label for="issue_id">Select Issued Book *</label>
                    <select id="issue_id" name="issue_id" required>
                        <option value="">— Select Issue Record —</option>
                        <?php
                        $tmp = get_issued($conn);
                        if ($tmp && mysqli_num_rows($tmp) > 0):
                            while ($iss = mysqli_fetch_assoc($tmp)):
                                $overdue_label = ($iss['days_overdue'] > 0)
                                    ? ' ⚠️ OVERDUE ' . $iss['days_overdue'] . 'd' : '';
                        ?>
                        <option value="<?= $iss['id'] ?>">
                            #<?= $iss['id'] ?> — <?= htmlspecialchars($iss['title']) ?>
                            → <?= htmlspecialchars($iss['reader_name']) ?>
                            <?= $overdue_label ?>
                            (due: <?= $iss['due_date'] ?>)
                        </option>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <option disabled>✅ No books currently issued</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label for="return_date">Return Date *</label>
                    <input type="date" id="return_date" name="return_date"
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="alert alert-warning" style="margin-bottom:16px;font-size:.85rem;">
                    💰 Fine rate: <strong>₹<?= FINE_PER_DAY ?> per overdue day</strong><br>
                    Fine is calculated automatically on submission.
                </div>

                <button type="submit" class="btn btn-success"
                        style="width:100%;justify-content:center;padding:11px;">
                    📥 Process Return
                </button>
            </form>
        </div>
    </div>

    <!-- ── Currently Issued Table ──────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h2>Currently Issued Books</h2>
            <?php if ($issued_res): ?>
            <span class="badge badge-warning"><?= mysqli_num_rows($issued_res) ?> out</span>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Reader</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($issued_res && mysqli_num_rows($issued_res) > 0):
                    while ($row = mysqli_fetch_assoc($issued_res)):
                        $overdue = $row['days_overdue'] > 0;
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['title']) ?></strong>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['reader_name']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($row['member_id']) ?></small>
                    </td>
                    <td><?= $row['issue_date'] ?></td>
                    <td style="<?= $overdue ? 'color:var(--danger);font-weight:700;' : '' ?>">
                        <?= $row['due_date'] ?>
                        <?php if ($overdue): ?>
                        <br><small>+<?= $row['days_overdue'] ?> days</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($overdue): ?>
                            <span class="badge badge-danger">Overdue</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Issued</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding:28px;">
                        ✅ No books currently issued.
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
