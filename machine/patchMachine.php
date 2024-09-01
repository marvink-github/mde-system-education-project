<?php
require_once("../connection.php"); 

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    http_response_code(400); 
    echo json_encode(["message" => "Fehlende erforderliche Felder."], JSON_PRETTY_PRINT);
    exit();
}

$id = $machineconn->real_escape_string(trim($data['id']));

$checkSql = "SELECT * FROM machine WHERE idMachine = '$id'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Maschine nicht gefunden."], JSON_PRETTY_PRINT);
    exit();
}

$updateFields = [];
if (isset($data['name'])) {
    $updateFields[] = "name = '" . $machineconn->real_escape_string(trim($data['name'])) . "'";
}
if (isset($data['d_entry_startstop'])) {
    $updateFields[] = "d_entry_startstop = '" . $machineconn->real_escape_string(trim($data['d_entry_startstop'])) . "'";
}
if (isset($data['d_entry_counter'])) {
    $updateFields[] = "d_entry_counter = '" . $machineconn->real_escape_string(trim($data['d_entry_counter'])) . "'";
}
if (isset($data['device_idDevice'])) {
    $updateFields[] = "device_idDevice = '" . $machineconn->real_escape_string(trim($data['device_idDevice'])) . "'";
}

if (empty($updateFields)) {
    http_response_code(400);
    echo json_encode(["message" => "Keine Felder zum Aktualisieren angegeben."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "UPDATE machine SET " . implode(", ", $updateFields) . " WHERE idMachine = '$id'";

if ($machineconn->query($sql) === TRUE) {
    http_response_code(200); 
    echo json_encode(["message" => "Maschine erfolgreich aktualisiert."], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Fehler beim Aktualisieren der Maschine: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();

