<?php
require_once("../connection.php");

$userid = $_GET['userid'] ?? null;
$machine_id = $_GET['machineid'] ?? null;
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$order = $_GET['order'] ?? null;

if (!$userid || !$machine_id) {
    http_response_code(400);
    echo json_encode(["message" => "userid und machineid sind erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT idShift, machine_idMachine AS idMachine, startTime, endTime, `order` AS order_id 
        FROM shift 
        LEFT JOIN machinedata ON shift.idShift = machinedata.shift_idShift 
        WHERE machine_idMachine = '$machine_id' 
        AND machinedata.userid = '$userid'";

if ($order) {
    $sql .= " AND machinedata.`order` = '$order'"; 
}

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
        'shiftid' => $row['idShift'], 
        'machineid' => $row['idMachine'],   
        'userid' => $userid,   
        'orderid' => $row['order_id'], 
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
