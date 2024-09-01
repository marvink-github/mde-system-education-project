<?php
require_once("../connection.php");

$orderid = $machineconn->real_escape_string($_GET['orderid'] ?? null);
$userid = $machineconn->real_escape_string($_GET['userid'] ?? null);
$machine_id = $machineconn->real_escape_string($_GET['machineid'] ?? null);

if (!$orderid) {
    http_response_code(400);
    echo json_encode(["message" => "orderid ist erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT 
            SUM(machinedata.value) AS total_value,
            COUNT(machinedata.idMachinedata) AS data_count,
            COUNT(DISTINCT machinedata.shift_idShift) AS shift_count,
            machinedata.`order`
        FROM 
            machinedata 
        LEFT JOIN 
            shift ON machinedata.shift_idShift = shift.idShift
        WHERE 
            machinedata.`order` = '$orderid'";

if ($userid) {
    $sql .= " AND machinedata.userid = '$userid'";
}

if ($machine_id) {
    $sql .= " AND machinedata.shift_idShift IN (SELECT idShift FROM shift WHERE machine_idMachine = '$machine_id')";
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$row = $result->fetch_assoc();

$data = [
    'orderid' => $orderid,
    'total_value' => $row['total_value'] ?? 0,
    'data_count' => $row['data_count'] ?? 0, 
    'shift_count' => $row['shift_count'] ?? 0
];

http_response_code(200);
echo json_encode($data, JSON_PRETTY_PRINT);

$machineconn->close();

