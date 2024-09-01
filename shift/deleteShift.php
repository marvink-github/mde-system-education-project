<?php
require_once("../connection.php");

$idShift = $machineconn->real_escape_string($_GET['shiftid'] ?? null);

if (!$idShift) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende erforderliche Felder: id erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "DELETE FROM shift WHERE idShift = '$idShift'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Schicht erfolgreich gelöscht."], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Keine Schicht gefunden, die gelöscht werden konnte."], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Löschen der Schicht: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();

