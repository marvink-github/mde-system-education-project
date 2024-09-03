<?php

require_once __DIR__ . '/../connection.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['timestamp']) || !isset($data['value']) || !isset($data['idshift'])) {
    http_response_code(400);
    echo json_encode(["message" => "timestamp, value, and idshift are required."], JSON_PRETTY_PRINT);
    exit();
}

$timestamp = $machineconn->real_escape_string(trim($data['timestamp']));
$value = $machineconn->real_escape_string(trim($data['value']));
$idshift = $machineconn->real_escape_string(trim($data['idshift']));
$userid = isset($data['userid']) ? $machineconn->real_escape_string(trim($data['userid'])) : null;
$order = isset($data['order']) ? $machineconn->real_escape_string(trim($data['order'])) : null;

$sql = "INSERT INTO machinedata (timestamp, value, userid, shift_idshift, `order`) 
        VALUES ('$timestamp', '$value', '$userid', '$idshift', ";

$sql .= ($order !== null) ? "'$order')" : "NULL)";

if ($machineconn->query($sql) === TRUE) {
    http_response_code(201);  
    echo json_encode(["message" => "machinedata successfully saved.", "id" => $machineconn->insert_id], JSON_PRETTY_PRINT);
} else {
    http_response_code(500);  
    echo json_encode(["message" => "error saving machinedata:" . $machineconn->error], JSON_PRETTY_PRINT);
}


$machineconn->close();

