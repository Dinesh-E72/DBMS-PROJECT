<?php
/**
 * readers/delete.php — Delete a reader (blocks if they have active loans)
 */
session_start();
if (!isset($_SESSION['staff_id'])) { header('Location: ../auth/login.php'); exit; }

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        // Block delete if reader has active (issued/overdue) loans
        $active = db_scalar($conn,
            "SELECT COUNT(*) FROM book_issues
             WHERE reader_id = $id AND status IN ('issued','overdue')"
        );
        if ($active > 0) {
            header('Location: index.php?msg=cannot_delete');
            exit;
        }
        mysqli_query($conn, "DELETE FROM readers WHERE id = $id");
    }
}
header('Location: index.php?msg=deleted');
exit;
