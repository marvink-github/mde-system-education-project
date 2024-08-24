<?php
require_once __DIR__ . '/../connection.php';

header("Content-Type: application/json");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $machineconn->prepare("DELETE FROM machinedata WHERE employee_idEmployee = ?");
    
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Datensatz erfolgreich gelöscht"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Fehler beim Löschen des Datensatzes"]);
    }
    
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["message" => "ID fehlt"]);
}

$machineconn->close();
?>
