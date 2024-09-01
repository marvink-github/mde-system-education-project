<?php
require_once("../connection.php");

$orderid = $_GET['orderid'] ?? null;
$userid = $_GET['userid'] ?? null;

if (!$orderid) {
    http_response_code(400);
    echo json_encode(["message" => "orderid ist erforderlich."], JSON_PRETTY_PRINT);
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
    echo json_encode(["message" => "Datenbankabfrage für den ersten Eintrag fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$firstEntry = $firstResult->fetch_assoc();

if (empty($firstEntry)) {
    http_response_code(400);
    echo json_encode(["message" => "Kein erster Eintrag für diese Order gefunden."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($firstEntry, JSON_PRETTY_PRINT);
}

$machineconn->close();

