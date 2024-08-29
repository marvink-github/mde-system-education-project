<?php

require_once __DIR__ . '/../connection.php'; 

// Optional API Key check
// if ($_GET['apiKey'] != "12398712397123987sadsdaihusadohji") {
//     http_response_code(403);
//     echo json_encode(["message" => "Forbidden"]);
//     exit();
// }

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$userid = $_GET['userid'] ?? null;
$order = $_GET['orderid'] ?? null; 
$shift = $_GET['shift'] ?? null; 
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 200;

$offset = ($page - 1) * $limit;

if ($userid) {
    $userCheckSql = "SELECT userid FROM machinedata WHERE userid = '$userid' LIMIT 1"; 
    $userCheckResult = $machineconn->query($userCheckSql);
    
    if ($userCheckResult->num_rows == 0) {
        http_response_code(404);
        echo json_encode(["message" => "Benutzer mit dieser ID existiert nicht in machinedata."], JSON_PRETTY_PRINT);
        exit();
    }
}

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

if ($shift) {
    $sql .= " AND shift_idshift = '$shift'";
}

$sql .= " LIMIT $limit OFFSET $offset";

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

echo json_encode($data, JSON_PRETTY_PRINT);

$machineconn->close();

