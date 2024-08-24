<?php
require_once __DIR__ . '/../connection.php';

header("Content-Type: application/json");

if (isset($_GET['id'])) {
    $id = $machineconn->real_escape_string($_GET['id']);

    $sql = "DELETE FROM machine WHERE idMachine = $id";

    if ($machineconn->query($sql) === TRUE) {
        if ($machineconn->affected_rows > 0) {
            echo json_encode(["message" => "Maschine erfolgreich gelöscht"]);
        } else {
            echo json_encode(["message" => "Keine Maschine mit dieser ID gefunden"]);
        }
    } else {
        http_response_code(400); 
        echo json_encode(["message" => "Fehler beim Löschen der Maschine"]);
    }
    
} else {
    http_response_code(400);
    echo json_encode(["message" => "ID fehlt"]);
}

$machineconn->close();
?>
