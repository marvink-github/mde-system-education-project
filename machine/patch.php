<?php

require_once __DIR__ . '/../connection.php'; 

$input = json_decode(file_get_contents('php://input'), true);
$machineId = $input['id'] ?? null;
$terminal_id = isset($input['terminal_id']) ? $machineconn->real_escape_string($input['terminal_id']) : null;
$terminal_type = isset($input['terminal_type']) ? $machineconn->real_escape_string($input['terminal_type']) : null;

if (!$machineId) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: id."]);
    exit();
}

$machineId = (int)$machineId; 

$sql = "SELECT terminal_id, terminal_type FROM machine WHERE idMachine = $machineId";
$currentResult = $machineconn->query($sql);

if ($currentResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Maschine nicht gefunden."]);
    exit();
}

$currentMachine = $currentResult->fetch_assoc();
$current_terminal_id = $currentMachine['terminal_id'];
$current_terminal_type = $currentMachine['terminal_type'];

$updateFields = [];
if ($terminal_id !== null && $terminal_id !== $current_terminal_id) {
    $updateFields[] = "terminal_id = '$terminal_id'";
}
if ($terminal_type !== null && $terminal_type !== $current_terminal_type) {
    $updateFields[] = "terminal_type = '$terminal_type'";
}

if (empty($updateFields)) {
    http_response_code(200);
    echo json_encode(["message" => "Es wurden keine Änderungen vorgenommen."]);
    exit();
}

$sql = "UPDATE machine SET " . implode(", ", $updateFields) . " WHERE idMachine = $machineId";
$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankaktualisierung fehlgeschlagen: " . $machineconn->error]);
    exit();
}

echo json_encode(["message" => "Maschine erfolgreich aktualisiert"]);
$machineconn->close();
?>
