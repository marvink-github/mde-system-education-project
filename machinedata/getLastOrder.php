<?php
require_once("../connection.php");

$orderid = $machineconn->real_escape_string($_GET['orderid'] ?? null);
$userid = $machineconn->real_escape_string($_GET['userid'] ?? null);

if (!$orderid) {
    http_response_code(400);
    echo json_encode(["message" => "orderid ist erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

if ($userid) {
    $lastSql = "SELECT 
                    machinedata.idMachinedata AS finish_id, 
                    machinedata.userid AS userid, 
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
                    machinedata.idMachinedata DESC 
                LIMIT 1";
} else {
    $lastSql = "SELECT 
                    machinedata.idMachinedata AS finish_id, 
                    machinedata.userid AS userid, 
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
                    machinedata.idMachinedata DESC 
                LIMIT 1";
}

$lastResult = $machineconn->query($lastSql);

if (!$lastResult) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage für den letzten Eintrag fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$lastEntry = $lastResult->fetch_assoc();

if (empty($lastEntry)) {
    http_response_code(400);
    echo json_encode(["message" => "Kein letzter Eintrag für diese Order gefunden."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($lastEntry, JSON_PRETTY_PRINT);
}

$machineconn->close();

