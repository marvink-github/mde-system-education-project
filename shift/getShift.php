<?php

$idShift = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null));
$machineId = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));
$orderId = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$fromDate = $machineconn->real_escape_string(trim($_GET['from'] ?? null));
$toDate = $machineconn->real_escape_string(trim($_GET['to'] ?? null));
$limit = !empty($_GET['limit']) ? (int)($_GET['limit']) : 200; 
$page = !empty($_GET['page']) ? (int)($_GET['page']) : 1; 

$offset = ($page - 1) * $limit;

$sql = "SELECT shift.*, machine.order FROM shift 
        LEFT JOIN machine ON shift.machine_idMachine = machine.idMachine"; 

$conditions = [];

if (!empty($idShift)) {
    $conditions[] = "shift.idShift = '$idShift'"; 
}

if (!empty($machineId)) {
    $conditions[] = "shift.machine_idMachine = '$machineId'";
}

if (!empty($orderId)) {
    $conditions[] = "machine.order = '$orderId'";
}

if (!empty($fromDate) && !empty($toDate)) {
    $conditions[] = "shift.startTime BETWEEN '$fromDate' AND '$toDate'";
} elseif (!empty($fromDate)) {
    $conditions[] = "shift.startTime >= '$fromDate'";
} elseif (!empty($toDate)) {
    $conditions[] = "shift.startTime <= '$toDate'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " LIMIT $limit OFFSET $offset";

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
    $errorMessage = "No shifts found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "Shifts retrieved: " . count($data));
}
