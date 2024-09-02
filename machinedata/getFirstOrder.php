<?php
require_once("../connection.php");

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

if (!$orderid) {
    http_response_code(400);
    echo json_encode(["message" => "orderid is required."], JSON_PRETTY_PRINT);
    exit();
}

if ($userid) {
    $firstSql = "SELECT 
                    machinedata.idMachinedata AS entry_id, 
                    machinedata.userid, 
                    machinedata.`order` AS orderid, 
                    machinedata.shift_idShift AS shift_id,
                    shift.startTime AS shift_start_time,
                    shift.endTime AS shift_end_time
                FROM 
                    machinedata
                LEFT JOIN 
                    shift ON machinedata.shift_idShift = shift.idShift
                WHERE 
                    machinedata.`order` = '$orderid'
                AND 
                    machinedata.userid = '$userid' 
                ORDER BY 
                    machinedata.idMachinedata ASC 
                LIMIT 1";
} else {
    $firstSql = "SELECT 
                    machinedata.idMachinedata AS entry_id, 
                    machinedata.userid, 
                    machinedata.`order` AS orderid, 
                    machinedata.shift_idShift AS shift_id,
                    shift.startTime AS shift_start_time,
                    shift.endTime AS shift_end_time
                FROM 
                    machinedata
                LEFT JOIN 
                    shift ON machinedata.shift_idShift = shift.idShift
                WHERE 
                    machinedata.`order` = '$orderid' 
                ORDER BY 
                    machinedata.idMachinedata ASC 
                LIMIT 1";
}

$firstResult = $machineconn->query($firstSql);

if (!$firstResult) {
    http_response_code(400);
    echo json_encode(["message" => "Database query for the last entry failed: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$firstEntry = $firstResult->fetch_assoc();

if (empty($firstEntry)) {
    http_response_code(400);
    echo json_encode(["message" => "No last entry found for this order."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($firstEntry, JSON_PRETTY_PRINT);
}

$machineconn->close();

