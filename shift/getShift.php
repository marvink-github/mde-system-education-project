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
    $conditions[] = "shift.idshift = '$idShift'"; 
}

if (!empty($machineId)) {
    $conditions[] = "shift.machine_idMachine = '$machineId'";
}

if (!empty($orderId)) {
    $conditions[] = "machine.order = '$orderId'";
}

if (!empty($fromDate) && !empty($toDate)) {
    $conditions[] = "shift.start_time BETWEEN '$fromDate' AND '$toDate'";
} elseif (!empty($fromDate)) {
    $conditions[] = "shift.start_time >= '$fromDate'";
} elseif (!empty($toDate)) {
    $conditions[] = "shift.start_time <= '$toDate'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " LIMIT $limit OFFSET $offset";

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

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "no shifts found."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}
