<?php

$machineid = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$shiftid = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null));

if (!$machineid && !$userid && !$orderid && !$shiftid) {
    http_response_code(400);
    $errorMessage = "At least one parameter (machineid, userid, orderid, shiftid) is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
    exit();
}

$sql = "SELECT 
            COUNT(machinedata.idMachinedata) AS sum_value,
            COUNT(DISTINCT machinedata.shift_idShift) AS shift_count,
            machinedata.`order` AS order_number
        FROM 
            machinedata 
        LEFT JOIN 
            shift ON machinedata.shift_idShift = shift.idShift
        WHERE 1=1"; 

if ($orderid) {
    $sql .= " AND machinedata.`order` = '$orderid'";
}

if ($userid) {
    $sql .= " AND machinedata.userid = '$userid'";
}

if ($machineid) {
    $sql .= " AND machinedata.shift_idShift IN (SELECT idShift FROM shift WHERE machine_idMachine = '$machineid')";
}

if ($shiftid) {
    $sql .= " AND machinedata.shift_idShift = '$shiftid'";
}

$sql .= " GROUP BY machinedata.`order`";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    $errorMessage = "Database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$row = $result->fetch_assoc();

if (isset($row) && $row['sum_value'] == 0) {
    http_response_code(400);
    $errorMessage = "No entries found for the provided filters.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
    exit();
}

$data = [
    'machineid' => $machineid ?? null,
    'userid' => $userid ?? null, 
    'orderid' => $orderid ?? null, 
    'shiftid' => $shiftid ?? null,
    'sum_value' => $row['sum_value'] ?? 0, 
];

http_response_code(200);
echo json_encode($data, JSON_PRETTY_PRINT);
