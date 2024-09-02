<?php
require_once("../connection.php"); 

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name']) || !isset($data['d_entry_startstop']) || !isset($data['d_entry_counter']) || !isset($data['device_idDevice'])) {
    http_response_code(400); 
    echo json_encode(["message" => "Missing required fields."], JSON_PRETTY_PRINT);
    exit();
}

$name = $machineconn->real_escape_string(trim($data['name']));
$d_entry_startstop = $machineconn->real_escape_string(trim($data['d_entry_startstop']));
$d_entry_counter = $machineconn->real_escape_string(trim($data['d_entry_counter']));
$device_idDevice = $machineconn->real_escape_string(trim($data['device_idDevice']));

$checkSql = "SELECT * FROM machine WHERE name = '$name' OR d_entry_startstop = '$d_entry_startstop' OR d_entry_counter = '$d_entry_counter'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows > 0) {
    http_response_code(400); 
    echo json_encode(["message" => "This machine already exists."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "INSERT INTO machine (name, d_entry_startstop, d_entry_counter, device_idDevice) VALUES ('$name', '$d_entry_startstop', '$d_entry_counter', '$device_idDevice')";

if ($machineconn->query($sql) === TRUE) {
    $last_id = $machineconn->insert_id;
    http_response_code(200); 
    echo json_encode(["message" => "Machine succesfully created", "idMachine" => $last_id], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Error creating the machine:" . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();

