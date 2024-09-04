<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['timestamp']) || !isset($data['value']) || !isset($data['idshift'])) {
    http_response_code(400);
    $errorMessage = "timestamp, value, and idshift are required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
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
    $successMessage = "machinedata successfully saved.";
    echo json_encode(["message" => $successMessage, "id" => $machineconn->insert_id], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', $successMessage . " ID: " . $machineconn->insert_id);
} else {
    http_response_code(500);  
    $errorMessage = "error saving machinedata: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
