<?php

$logType = $machineconn->real_escape_string(trim($_GET['type'] ?? null));
$from = $machineconn->real_escape_string(trim($_GET['from'] ?? null)); 
$to = $machineconn->real_escape_string(trim($_GET['to'] ?? null)); 
$limit = !empty($_GET['limit']) ? (int)($_GET['limit']) : 200; 
$page = !empty($_GET['page']) ? (int)($_GET['page']) : 1; 

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM log"; 

$conditions = [];

if ($logType) {
    $conditions[] = "log_type = '$logType'";
}

if ($from && $to) {
    $conditions[] = "timestamp BETWEEN '$from' AND '$to'";
} elseif ($from) {
    $conditions[] = "timestamp >= '$from'";
} elseif ($to) {
    $conditions[] = "timestamp <= '$to'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    $errorMessage = "database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    http_response_code(400);
    $errorMessage = "no logs found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
} else {
    http_response_code(200);
    echo json_encode($data, JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "logs retrieved successfully: " . count($data) . " entries found.");
}
