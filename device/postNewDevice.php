<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['terminalid']) || !isset($data['terminaltype'])) {
    http_response_code(400); 
    echo json_encode(["message" => "terminalid and terminaltype are required."], JSON_PRETTY_PRINT);
    exit();
}

$terminal_id = $machineconn->real_escape_string(trim($data['terminalid']));
$terminal_type = $machineconn->real_escape_string(trim($data['terminaltype']));

$checkSql = "SELECT * FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows > 0) {
    http_response_code(409); 
    echo json_encode(["message" => "this device already exists."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "INSERT INTO device (terminal_id, terminal_type) VALUES ('$terminal_id', '$terminal_type')";

if ($machineconn->query($sql) === TRUE) {
    $last_id = $machineconn->insert_id;
    http_response_code(201); 
    echo json_encode(["message" => "device succesfully created.", "idDevice" => $last_id], JSON_PRETTY_PRINT);
} else {
    http_response_code(500); 
    echo json_encode(["message" => "error creating the device:" . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
