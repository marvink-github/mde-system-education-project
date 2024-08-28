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
$timestamp = date('Y-m-d H:i:s'); 

$sqlCheck = "SELECT * FROM machine WHERE idMachine = '$machine_id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Maschine nicht gefunden."], JSON_PRETTY_PRINT);
    exit();
}

$machineStateSql = "SELECT state, (SELECT idDevice FROM device WHERE idDevice = (SELECT device_idDevice FROM machine WHERE idMachine = $machine_id)) AS idDevice FROM machine WHERE idMachine = $machine_id";
$machineStateResult = $machineconn->query($machineStateSql);
$machine = $machineStateResult->fetch_assoc();

if ($machine['state'] === 'start') {
    http_response_code(400);
    echo json_encode(["message" => "Maschine ist bereits aktiv."], JSON_PRETTY_PRINT);
    return;
}

$checkShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
$shiftResult = $machineconn->query($checkShiftSql);

if ($shiftResult->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["message" => "Eine aktive Schicht für diese Maschine existiert bereits."], JSON_PRETTY_PRINT);
    return;
}

$sqlStart = "UPDATE machine SET state = 'start', userid = '$userid' WHERE idMachine = '$machine_id'";

if ($machineconn->query($sqlStart) === TRUE) {
    $updateDeviceSql = "UPDATE device SET state = 'active' WHERE idDevice = " . $machine['idDevice'];

    if ($machineconn->query($updateDeviceSql) === TRUE) {
        $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', $machine_id)";

        if ($machineconn->query($shiftSql) === TRUE) {
            $idshift = $machineconn->insert_id;
            http_response_code(200);
            echo json_encode([
                "message" => "Maschine und Schicht erfolgreich gestartet.",
                "machineId" => $machine_id,
                "shiftid" => $idshift,                
                "userid" => $userid,
                "startTime" => $timestamp
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Fehler beim Starten der Schicht: " . $machineconn->error], JSON_PRETTY_PRINT);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Fehler beim Aktualisieren des Gerätestatus: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Starten der Maschine: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
?>
