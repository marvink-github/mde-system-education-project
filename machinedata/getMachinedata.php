<?php

$from = $machineconn->real_escape_string(trim($_GET['from'] ?? null));
$to = $machineconn->real_escape_string(trim($_GET['to'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));  
$shiftid = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null)); 
$machineid = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));
$page = $machineconn->real_escape_string(trim($_GET['page'] ?? 1));
$limit = $machineconn->real_escape_string(trim($_GET['limit'] ?? 200));

$offset = ($page - 1) * $limit;

$sql = "SELECT md.* 
        FROM machinedata md
        JOIN shift s ON md.shift_idShift = s.idShift 
        WHERE 1=1"; 

if ($from && $to) {
    $sql .= " AND timestamp BETWEEN '$from' AND '$to'";
} elseif ($from) {
    $sql .= " AND timestamp >= '$from'";
} elseif ($to) {
    $sql .= " AND timestamp <= '$to'";
}

if ($userid) {
    $sql .= " AND userid = '$userid'"; 
}

if ($orderid) {
    $sql .= " AND `order` = '$orderid'"; 
}

if ($shiftid) {
    $sql .= " AND shift_idshift = '$shiftid'";
}

if ($machineid) {
    $sql .= " AND s.machine_idMachine = '$machineid'";
}

$sql .= " ORDER BY md.idMachinedata DESC LIMIT $limit OFFSET $offset";


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
    $message = "No machinedata found.";
    echo json_encode(["message" => $message], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $message);
} else {
    http_response_code(200);
    echo json_encode($data, JSON_PRETTY_PRINT);
}
