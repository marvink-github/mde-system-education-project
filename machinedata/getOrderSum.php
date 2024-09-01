<?php
require_once("../connection.php");

$orderid = $_GET['orderid'] ?? null;
$userid = $_GET['userid'] ?? null;
$machine_id = $_GET['machineid'] ?? null;

if (!$orderid) {
    http_response_code(400);
    echo json_encode(["message" => "orderid ist erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT 
            SUM(value) AS total_value
        FROM 
            machinedata 
        WHERE 
            `order` = '$orderid'";

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

if ($machine_id) {
    $sql .= " AND shift_idShift IN (SELECT idShift FROM shift WHERE machine_idMachine = '$machine_id')";
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$row = $result->fetch_assoc();

if ($row['total_value'] === null) {
    http_response_code(200);
    echo json_encode(["total_value" => 0], JSON_PRETTY_PRINT); 
} else {
    echo json_encode(["total_value" => $row['total_value']], JSON_PRETTY_PRINT);
}

$machineconn->close();

