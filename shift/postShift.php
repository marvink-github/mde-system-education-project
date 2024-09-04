<?php

$data = json_decode(file_get_contents("php://input"), true);

$starttime = $machineconn->real_escape_string(trim($data['starttime'] ?? null));
$endtime = $machineconn->real_escape_string(trim($data['endtime'] ?? null));
$machineid = $machineconn->real_escape_string(trim($data['machineid'] ?? null));

if (!$starttime || !$endtime || !$machineid) {
    http_response_code(400);
    echo json_encode(["message" => "starttime, endtime, and machineid are required."], JSON_PRETTY_PRINT);
    exit();
}

$sqlInsert = "INSERT INTO shift (startTime, endTime, machine_idMachine) VALUES ('$starttime', '$endtime', '$machineid')";

if ($machineconn->query($sqlInsert) === TRUE) {
    $shiftId = $machineconn->insert_id;
    http_response_code(201); 
    echo json_encode([
        "message" => "shift successfully created.",
        "shiftid" => $shiftId,
        "starttime" => $starttime,
        "endtime" => $endtime,
        "machineid" => $machineid
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "failed to create shift: " . $machineconn->error], JSON_PRETTY_PRINT);
}
