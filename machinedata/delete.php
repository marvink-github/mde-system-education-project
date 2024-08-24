<?php
require_once __DIR__ . '/../connection.php';

header("Content-Type: application/json");

if (isset($_GET['userid'])) {
    $userid = $machineconn->real_escape_string($_GET['userid']);
    
    $sql = "DELETE FROM machinedata WHERE employee_idEmployee = '$userid'";
    
    if ($machineconn->query($sql)) {
        echo json_encode(["message" => "Datensatz erfolgreich gelöscht"]);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Fehler beim Löschen des Datensatzes: " . $machineconn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "ID fehlt"]);
}

$machineconn->close();
?>
