<?php
session_start();
include '../config/config.php';

$id = intval($_GET['id'] ?? 0);

$sql = "
    SELECT c.id, c.subject, c.message, c.status, c.created_at,
           u.first_name, u.last_name, u.matric_number, u.email
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($data);