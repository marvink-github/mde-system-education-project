<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

if (!$orderid) {
    http_response_code(400);
    $errorMessage = "orderid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'firstorder', $errorMessage);
    exit();
}

if ($userid) {
    $firstSql = "SELECT 
                    machinedata.idMachinedata AS dataid, 
                    machinedata.userid, 
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
                    machinedata.idMachinedata ASC 
                LIMIT 1";
} else {
    $firstSql = "SELECT 
                    machinedata.idMachinedata AS dataid, 
                    machinedata.userid, 
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
                    machinedata.idMachinedata ASC 
                LIMIT 1";
}

$firstResult = $machineconn->query($firstSql);

if (!$firstResult) {
    http_response_code(500);
    $errorMessage = "Database query for the first entry failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$firstEntry = $firstResult->fetch_assoc();

if (empty($firstEntry)) {
    http_response_code(404);
    $message = "No first entry found for this order.";
    echo json_encode(["message" => $message], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $message);
} else {
    http_response_code(200);
    echo json_encode($firstEntry, JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "First entry retrieved for order: " . $orderid);
}
