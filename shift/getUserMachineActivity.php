<?php
require_once("../connection.php");

$userid = $_GET['userid'] ?? null;
$machine_id = $_GET['machineid'] ?? null;
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

if (!$userid || !$machine_id) {
    http_response_code(400);
    echo json_encode(["message" => "userid und machineid sind erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT idShift, machine_idMachine AS idMachine, startTime, endTime 
        FROM shift 
        WHERE machine_idMachine = '$machine_id' AND EXISTS (
        SELECT 1 FROM machinedata WHERE shift_idShift = shift.idShift AND userid = '$userid'
        )";

if ($from) {
    $sql .= " AND startTime >= '$from'";
}

if ($to) {
    $sql .= " AND (endTime <= '$to' OR endTime IS NULL)";
}

$sql .= " ORDER BY startTime ASC";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'machineid' => $row['idMachine'],
        'shiftid' => $row['idShift'],    
        'userid' => $userid,   
        'startTime' => $row['startTime'],
        'endTime' => $row['endTime']
    ];
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "Keine Schichten für diesen Benutzer und diese Maschine gefunden."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}

$machineconn->close();

