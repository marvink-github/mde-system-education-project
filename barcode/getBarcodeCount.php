<?php
require_once("../connection.php"); 

$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$value = $machineconn->real_escape_string(trim($_GET['value'] ?? null)); 

$sql = "SELECT COUNT(*) AS total FROM machinedata WHERE 1=1"; 

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

if ($orderid) {
    $sql .= " AND `order` = '$orderid'";
}

if ($value) {
    $sql .= " AND value = '$value'"; 
}

$result = $machineconn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();    
    echo json_encode([
        "status" => "success", 
        "userid" => $userid ?? null,
        "orderid" => $orderid ?? null,
        "value" => $value ?? null, 
        "total" => (int)$row['total'] 
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Fehler beim Abrufen der Daten."], JSON_PRETTY_PRINT);
}

$machineconn->close();

