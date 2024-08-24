<?php
require_once __DIR__ . '/../connection.php';

header("Content-Type: application/json");

// Nur um bestimmte user Datensätze zu löschen, sollte aber eigentlich über die employee tabelle passieren, 
// dort wird dann die userid gelöscht mitcascade sollten alle datensätze von dem employee gelöscht werden
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
