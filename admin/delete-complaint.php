<?php
// filepath: c:\xampp\htdocs\student_complaints\admin\delete-complaint.php
session_start();
include '../config/config.php';

$id = intval($_GET['id'] ?? 0);
$response = ['success' => false];

if ($id) {
    // Delete related replies first
    $conn->query("DELETE FROM replies WHERE complaint_id = $id");

    // Before deleting the complaint, ensure that it exists

    $stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $response['success'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);