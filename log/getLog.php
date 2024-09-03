<?php

$logType = $machineconn->real_escape_string(trim($_GET['type'] ?? null));
$from = $machineconn->real_escape_string(trim($_GET['from'] ?? null)); 
$to = $machineconn->real_escape_string(trim($_GET['to'] ?? null)); 
$page = $machineconn->real_escape_string(trim($_GET['page'] ?? 1));
$limit = $machineconn->real_escape_string(trim($_GET['limit'] ?? 200));

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
    echo json_encode(["message" => "database query failed: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "no logs found."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}


