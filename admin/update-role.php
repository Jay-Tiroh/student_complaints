<?php
// filepath: c:\xampp\htdocs\student_complaints\admin\update-role.php
session_start();
include '../config/config.php';

$user_id = intval($_POST['user_id'] ?? 0);
$new_role = $_POST['role'] ?? '';

$response = ['success' => false];

if ($user_id && in_array($new_role, ['admin', 'student'])) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['role'] = $new_role;
    }
}

header('Content-Type: application/json');
echo json_encode($response);