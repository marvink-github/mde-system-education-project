<?php

$data = json_decode(file_get_contents("php://input"), true);

$starttime = $machineconn->real_escape_string(trim($data['starttime'] ?? null));
$endtime = $machineconn->real_escape_string(trim($data['endtime'] ?? null));
$machineid = $machineconn->real_escape_string(trim($data['machineid'] ?? null));

if (!$starttime || !$endtime || !$machineid) {
    http_response_code(400);
    $errorMessage = "starttime, endtime, and machineid are required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'post', $errorMessage); 
    exit();
}

$sqlInsert = "INSERT INTO shift (startTime, endTime, machine_idMachine) VALUES ('$starttime', '$endtime', '$machineid')";

if ($machineconn->query($sqlInsert) === TRUE) {
    $shiftId = $machineconn->insert_id;
    http_response_code(201);
    $successMessage = "Shift successfully created.";
    echo json_encode([
        "message" => $successMessage,
        "shiftid" => $shiftId,
        "starttime" => $starttime,
        "endtime" => $endtime,
        "machineid" => $machineid
    ], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "$successMessage. shiftid: $shiftId"); 
} else {
    http_response_code(500);
    $errorMessage = "Failed to create shift: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
}
