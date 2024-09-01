<?php
require_once("../connection.php");

$idShift = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null));
$machineId = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));

$sql = "SELECT * FROM shift"; 
$conditions = [];

if ($idShift) {
    $conditions[] = "idshift = '$idShift'";
}

if ($machineId) {
    $conditions[] = "machine_idMachine = '$machineId'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "Keine Schichten gefunden."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}

$machineconn->close();
