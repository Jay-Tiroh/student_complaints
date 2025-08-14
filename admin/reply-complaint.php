<?php
// filepath: c:\xampp\htdocs\student_complaints\admin\reply-complaint.php
session_start();
include '../config/config.php';

$id = intval($_POST['complaint_id'] ?? 0);
$status = $_POST['status'] ?? 'Pending';
$reply = $_POST['reply'] ?? '';
$admin_id = $_SESSION['user_id'] ?? 0;

if ($id && $reply && $admin_id) {
    // Insert reply
    $stmt = $conn->prepare("INSERT INTO replies (complaint_id, admin_id, reply) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $id, $admin_id, $reply);
    $stmt->execute();

    // Update complaint status
    $stmt2 = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt2->bind_param("si", $status, $id);
    $stmt2->execute();

    header("Location: admin-dash.php?success=1");
    exit();
} else {
    header("Location: admin-dash.php?error=1");
    exit();
}