<?php

require_once("../../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['machineid']) || !isset($data['userid']) || !isset($data['orderId'])) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende erforderliche Felder: machineid, userid und orderId erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$machine_id = $data['machineid'];
$userid = $data['userid'];
$orderId = $data['orderId']; 
$timestamp = date('Y-m-d H:i:s'); 

$shiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = '$machine_id' AND endTime IS NULL";
$shiftResult = $machineconn->query($shiftSql);

if ($shiftResult->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Keine aktive Schicht für diese Maschine vorhanden."], JSON_PRETTY_PRINT);
    exit();
}

$shift = $shiftResult->fetch_assoc();
$shift_id = $shift['idshift'];

$insertSql = "INSERT INTO machinedata (timestamp, userid, value, orderIdentifier, shift_idShift) 
              VALUES ('$timestamp', '$userid', 'completed', '$orderId', '$shift_id')";

if ($machineconn->query($insertSql) === TRUE) {
    http_response_code(200);
    echo json_encode(["message" => "Auftrag erfolgreich beendet.", "orderId" => $orderId], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Beenden des Auftrags: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();

