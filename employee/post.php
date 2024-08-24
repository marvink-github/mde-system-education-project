<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php'; 

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userid'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: userid."]);
    exit();
}

$userId = $machineconn->real_escape_string($userId);

$sql = "INSERT INTO employee (userid) VALUES ('$userId')";

if ($machineconn->query($sql)) {
    http_response_code(201);
    echo json_encode(["message" => "Mitarbeiter erfolgreich hinzugefügt", "idEmployee" => $machineconn->insert_id]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Hinzufügen des Mitarbeiters: " . $machineconn->error]);
}

$machineconn->close();
?>
