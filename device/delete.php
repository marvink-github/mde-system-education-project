<?php
require_once("../connection.php");

$idDevice = $_GET['id'] ?? null;

if (!$idDevice) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende erforderliche Felder: id erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "DELETE FROM device WHERE idDevice = '$idDevice'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Device erfolgreich geloescht."], JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Kein Device gefunden, das geloescht werden konnte."], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(500);
    echo json_encode(["message" => "Fehler beim Löschen des Devices: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
?>
