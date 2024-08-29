<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['type'])) {
    http_response_code(400); 
    echo json_encode(["message" => "Fehlende erforderliche Felder."], JSON_PRETTY_PRINT);
    exit();
}

$terminal_id = $data['id'];
$terminal_type = $data['type'];

$checkSql = "SELECT * FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows > 0) {
    http_response_code(409); 
    echo json_encode(["message" => "Device mit dieser id und type existiert bereits."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "INSERT INTO device (terminal_id, terminal_type) VALUES ('$terminal_id', '$terminal_type')";

if ($machineconn->query($sql) === TRUE) {
    $last_id = $machineconn->insert_id;
    http_response_code(201); 
    echo json_encode(["message" => "Device erfolgreich erstellt.", "idDevice" => $last_id], JSON_PRETTY_PRINT);
} else {
    http_response_code(500); 
    echo json_encode(["message" => "Fehler beim Erstellen des Devices: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
