<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['terminalid']) || !isset($data['terminaltype'])) {
    http_response_code(400); 
    $errorMessage = "terminalid and terminaltype are required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'post', $errorMessage); 
    exit();
}

$terminal_id = $machineconn->real_escape_string(trim($data['terminalid']));
$terminal_type = $machineconn->real_escape_string(trim($data['terminaltype']));

$checkSql = "SELECT * FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows > 0) {
    http_response_code(409); 
    $errorMessage = "This device already exists.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage); 
    exit();
}

$sql = "INSERT INTO device (terminal_id, terminal_type) VALUES ('$terminal_id', '$terminal_type')";

if ($machineconn->query($sql) === TRUE) {
    $last_id = $machineconn->insert_id;
    http_response_code(201); 
    echo json_encode(["message" => "Device successfully created.", "idDevice" => $last_id], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "device successfully created with id: $last_id"); 
} else {
    http_response_code(500); 
    $errorMessage = "Error creating the device: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
}
