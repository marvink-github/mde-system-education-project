<?php
require_once("../connection.php"); 

$machineId = $_GET['id'] ?? null;

// Überprüfen, ob die ID vorhanden ist
if (!$machineId) {
    http_response_code(400); 
    echo json_encode(["message" => "Fehlende Maschinen-ID."], JSON_PRETTY_PRINT);
    exit();
}

// Überprüfen, ob die Maschine existiert
$checkSql = "SELECT * FROM machine WHERE idMachine = '$machineId'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(400); 
    echo json_encode(["message" => "Maschine nicht gefunden."], JSON_PRETTY_PRINT);
    exit();
}

// Löschen der Maschine
$deleteSql = "DELETE FROM machine WHERE idMachine = '$machineId'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    echo json_encode(["message" => "Maschine erfolgreich deleted."], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Fehler beim Löschen der Maschine: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
?>
