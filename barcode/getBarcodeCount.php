<?php
require_once("../connection.php"); 
require_once("../functions.php");

$userid = trim($machineconn->real_escape_string($_GET['userid'] ?? null));
$orderid = trim($machineconn->real_escape_string($_GET['orderid'] ?? null));

$sql = "SELECT COUNT(*) AS total FROM machinedata WHERE 1=1"; 

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

if ($orderid) {
    $sql .= " AND `order` = '$orderid'";
}

$result = $machineconn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();    
    echo json_encode([
        "status" => "success", 
        "total" => (int)$row['total'] 
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Fehler beim Abrufen der Daten."], JSON_PRETTY_PRINT);
}

$machineconn->close();
