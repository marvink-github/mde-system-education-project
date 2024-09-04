<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

if (!$orderid) {
    http_response_code(400);
    $errorMessage = "orderid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

if ($userid) {
    $lastSql = "SELECT 
                    machinedata.idMachinedata AS dataid, 
                    machinedata.userid AS userid, 
                    machinedata.`order` AS orderid, 
                    machinedata.shift_idShift AS shiftid,
                    shift.startTime AS startTime,
                    shift.endTime AS endTime
                FROM 
                    machinedata
                LEFT JOIN 
                    shift ON machinedata.shift_idShift = shift.idShift
                WHERE 
                    machinedata.`order` = '$orderid' 
                AND 
                    machinedata.userid = '$userid'
                ORDER BY 
                    machinedata.idMachinedata DESC 
                LIMIT 1";
} else {
    $lastSql = "SELECT 
                    machinedata.idMachinedata AS dataid, 
                    machinedata.userid AS userid, 
                    machinedata.`order` AS orderid, 
                    machinedata.shift_idShift AS shiftid,
                    shift.startTime AS startTime,
                    shift.endTime AS endTime
                FROM 
                    machinedata
                LEFT JOIN 
                    shift ON machinedata.shift_idShift = shift.idShift
                WHERE 
                    machinedata.`order` = '$orderid' 
                ORDER BY 
                    machinedata.idMachinedata DESC 
                LIMIT 1";
}

$lastResult = $machineconn->query($lastSql);

if (!$lastResult) {
    http_response_code(400);
    $errorMessage = "database query for the last entry failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$lastEntry = $lastResult->fetch_assoc();

if (empty($lastEntry)) {
    http_response_code(400);
    $message = "no last entry found for this order.";
    echo json_encode(["message" => $message], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', $message);
} else {
    http_response_code(200);
    echo json_encode($lastEntry, JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "last entry retrieved for order: " . $orderid);
}
