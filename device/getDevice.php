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
    http_response_code(500);
    $errorMessage = "Database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    http_response_code(404);
    echo json_encode(["message" => "No devices found."], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', "No devices found matching the criteria.");
} else {
    http_response_code(200);
    echo json_encode($data, JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "Devices retrieved successfully: " . json_encode($data));
}
