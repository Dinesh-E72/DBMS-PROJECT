<?php

define('BASE_URL', '../');
require_once '../config/db.php';
$page_title = 'Issue Book';
include '../includes/header.php';


$books_res   = mysqli_query($conn,
    "SELECT id, title, available_copies FROM books
     WHERE available_copies > 0
     ORDER BY title ASC"
);


$readers_res = mysqli_query($conn,
    "SELECT id, name, member_id FROM readers
     WHERE status = 'active'
     ORDER BY name ASC"
);

$errors = [];
$v = [
    'book_id'    => '',
    'reader_id'  => '',
    'issue_date' => date('Y-m-d'),
    'due_date'   => date('Y-m-d', strtotime('+14 days')),
    'notes'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id    = intval($_POST['book_id']    ?? 0);
    $reader_id  = intval($_POST['reader_id']  ?? 0);
    $issue_date = db_escape($conn, $_POST['issue_date'] ?? date('Y-m-d'));
    $due_date   = db_escape($conn, $_POST['due_date']   ?? '');
    $notes      = db_escape($conn, $_POST['notes']      ?? '');
    $staff_id   = intval($_SESSION['staff_id']);
    $v          = compact('book_id','reader_id','issue_date','due_date','notes');

    // --- Validation ---
    if (!$book_id)               $errors[] = 'Please select a book.';
    if (!$reader_id)             $errors[] = 'Please select a reader.';
    if (empty($due_date))        $errors[] = 'Due date is required.';
    if (!empty($issue_date) && !empty($due_date) && $due_date <= $issue_date)
                                 $errors[] = 'Due date must be after the issue date.';

    if (empty($errors)) {
        // Re-check availability (prevent race conditions)
        $available = db_scalar($conn,
            "SELECT available_copies FROM books WHERE id = $book_id"
        );
        if ($available < 1) {
            $errors[] = 'This book is no longer available (just taken). Please choose another.';
        }

        // Check if this reader already has this specific book issued/overdue
        $already = db_scalar($conn,
            "SELECT COUNT(*) FROM book_issues
             WHERE book_id = $book_id
               AND reader_id = $reader_id
               AND status IN ('issued','overdue')"
        );
        if ($already > 0) {
            $errors[] = 'This reader already has a copy of this book that has not been returned.';
        }
    }

    if (empty($errors)) {
        // Insert issue record
        $ok = mysqli_query($conn,
            "INSERT INTO book_issues
                 (book_id, reader_id, staff_id, issue_date, due_date, notes, status)
             VALUES
                 ($book_id, $reader_id, $staff_id,
                  '$issue_date', '$due_date', '$notes', 'issued')"
        );

        if ($ok) {
            // Decrement available copies
            mysqli_query($conn,
                "UPDATE books
                 SET available_copies = available_copies - 1
                 WHERE id = $book_id AND available_copies > 0"
            );
            header('Location: index.php?msg=issued');
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>

<div class="page-header">
    <h1>📤 Issue Book</h1>
    <a href="index.php" class="btn btn-secondary">← All Issues</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ⚠️ <strong>Please fix:</strong>
    <ul style="margin:6px 0 0 16px;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2>Issue Details</h2></div>
    <div class="card-body">
        <form method="POST" action="issue.php" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label for="book_id">
                        Select Book *
                        <small class="text-muted">(only books with available copies shown)</small>
                    </label>
                    <select id="book_id" name="book_id" required>
                        <option value="">— Select a Book —</option>
                        <?php
                        if ($books_res && mysqli_num_rows($books_res) > 0):
                            while ($bk = mysqli_fetch_assoc($books_res)):
                        ?>
                        <option value="<?= $bk['id'] ?>"
                            <?= $v['book_id'] == $bk['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bk['title']) ?>
                            (<?= $bk['available_copies'] ?> available)
                        </option>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <option value="" disabled>⚠️ No books available right now</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reader_id">Select Reader *</label>
                    <select id="reader_id" name="reader_id" required>
                        <option value="">— Select a Reader —</option>
                        <?php
                        if ($readers_res && mysqli_num_rows($readers_res) > 0):
                            while ($rd = mysqli_fetch_assoc($readers_res)):
                        ?>
                        <option value="<?= $rd['id'] ?>"
                            <?= $v['reader_id'] == $rd['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rd['name']) ?>
                            (<?= htmlspecialchars($rd['member_id']) ?>)
                        </option>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <option value="" disabled>⚠️ No active readers found</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="issue_date">Issue Date *</label>
                    <input type="date" id="issue_date" name="issue_date"
                           value="<?= htmlspecialchars($v['issue_date']) ?>"
                           max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="due_date">
                        Due Date *
                        <small class="text-muted">(auto: +14 days)</small>
                    </label>
                    <input type="date" id="due_date" name="due_date"
                           value="<?= htmlspecialchars($v['due_date']) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes"
                              placeholder="Any special notes about this issue…"><?= htmlspecialchars($v['notes']) ?></textarea>
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">📤 Issue Book</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info" style="margin-top:20px;">
    💡 <strong>Tip:</strong> Due date auto-fills to 14 days from issue date when you select the issue date.
    Fine rate: <strong>₹5 per overdue day</strong>.
</div>

<?php include '../includes/footer.php'; ?>
