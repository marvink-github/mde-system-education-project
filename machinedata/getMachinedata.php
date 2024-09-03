<?php

// Optional API Key check
// if ($_GET['apiKey'] != "12398712397123987sadsdaihusadohji") {
//     http_response_code(403);
//     echo json_encode(["message" => "Forbidden"]);
//     exit();
// }

$from = $machineconn->real_escape_string(trim($_GET['from'] ?? null));
$to = $machineconn->real_escape_string(trim($_GET['to'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));  
$shiftid = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null)); 
$page = $machineconn->real_escape_string(trim($_GET['page'] ?? 1));
$limit = $machineconn->real_escape_string(trim($_GET['limit'] ?? 200));

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM machinedata WHERE 1=1"; 

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

$sql .= " LIMIT $limit OFFSET $offset";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "database query failed: " . $machineconn->error]);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);

$machineconn->close();
