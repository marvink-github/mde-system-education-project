<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php';

$id = $_GET['id'] ?? null;
$userId = $_GET['userid'] ?? null;

$sql = "SELECT * FROM employee WHERE 1=1"; 

if ($id) {
    $id = $machineconn->real_escape_string($id);
    $sql .= " AND idEmployee = '$id'";
}

if ($userId) {
    $userId = $machineconn->real_escape_string($userId);
    $sql .= " AND userid = '$userId'";
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error]);
    exit();
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$machineconn->close();
?>
