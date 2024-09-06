<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name']) || !isset($data['d_entry_startstop']) || !isset($data['d_entry_counter']) || !isset($data['device_idDevice'])) {
    http_response_code(400); 
    $errorMessage = "name, d_entry_startstop, d_entry_counter and device_idDevice are required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'post', $errorMessage); 
    exit();
}

$name = $machineconn->real_escape_string(trim($data['name']));
$d_entry_startstop = $machineconn->real_escape_string(trim($data['d_entry_startstop']));
$d_entry_counter = $machineconn->real_escape_string(trim($data['d_entry_counter']));
$device_idDevice = $machineconn->real_escape_string(trim($data['device_idDevice']));

$checkSql = "SELECT * FROM machine WHERE name = '$name' OR d_entry_startstop = '$d_entry_startstop' OR d_entry_counter = '$d_entry_counter'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows > 0) {
    http_response_code(409); 
    $errorMessage = "This machine already exists.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage); 
    exit();
}

$sql = "INSERT INTO machine (name, d_entry_startstop, d_entry_counter, device_idDevice) VALUES ('$name', '$d_entry_startstop', '$d_entry_counter', '$device_idDevice')";

if ($machineconn->query($sql) === TRUE) {
    $last_id = $machineconn->insert_id;
    http_response_code(201); 
    $errorMessage = "Machine successfully created: idMachine => $last_id";
    echo json_encode(["message" => $errorMessage, JSON_PRETTY_PRINT]);
    logDB($machineconn, 'info', $errorMessage); 
} else {
    http_response_code(500); 
    $errorMessage = "Error creating the machine: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
