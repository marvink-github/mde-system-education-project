<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['machineid']) || !isset($data['userid'])) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende erforderliche Felder: machineid und userid erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$machine_id = $data['machineid'];
$userid = $data['userid'];
$order = $data['order'] ?? null;

$sqlCheck = "SELECT * FROM machine WHERE idMachine = '$machine_id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Maschine nicht gefunden."], JSON_PRETTY_PRINT);
    exit();
}

// Hier wird `order` in Backticks gesetzt
$updateMachineSql = "UPDATE machine SET userid = '$userid'";

if ($order !== null) {
    $updateMachineSql .= ", `order` = '$order'"; // Backticks um `order`
}
$updateMachineSql .= " WHERE idMachine = '$machine_id'";

if ($machineconn->query($updateMachineSql) === TRUE) {
    http_response_code(200);
    echo json_encode([
        "message" => "User-ID erfolgreich aktualisiert." . ($order ? " Order-ID erfolgreich aktualisiert." : ""),
        "machineId" => $machine_id,
        "userid" => $userid,
        "order" => $order
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Aktualisieren der Maschinen: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
