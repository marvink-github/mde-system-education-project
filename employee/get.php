<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php';

$employeeid = $_GET['id'] ?? null;
$userId = $_GET['userid'] ?? null;

$sql = "SELECT * FROM employee WHERE 1=1"; 

if ($employeeid) {
    $employeeid = $machineconn->real_escape_string($employeeid);
    $sql .= " AND idEmployee = '$employeeid'"; 
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

if (empty($data)) {
    http_response_code(404); 
    echo json_encode(["message" => "Keine Mitarbeiter gefunden."]);
} else {
    echo json_encode($data); 
}

$machineconn->close();
?>
