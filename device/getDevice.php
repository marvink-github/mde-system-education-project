<?php

$idDevice = $machineconn->real_escape_string(trim($_GET['deviceid'] ?? null));
$terminalId = $machineconn->real_escape_string(trim($_GET['terminalid'] ?? null));
$terminalType = $machineconn->real_escape_string(trim($_GET['terminaltype'] ?? null));

$sql = "SELECT * FROM device";

$conditions = [];

if (!empty($idDevice)) {
    $conditions[] = "idDevice = '$idDevice'";
}

if (!empty($terminalId)) {
    $conditions[] = "terminal_id = '$terminalId'";
}

if (!empty($terminalType)) {
    $conditions[] = "terminal_type = '$terminalType'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "database query failed: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);

