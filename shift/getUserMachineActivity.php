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

$sql = "SELECT 
            shift.idShift AS shiftid, 
            shift.machine_idMachine AS machineid, 
            machinedata.userid, 
            machinedata.`order` AS orderid,
            shift.startTime, 
            shift.endTime, 
            SUM(machinedata.value) AS total_value
        FROM 
            shift 
        LEFT JOIN 
            machinedata 
        ON 
            shift.idShift = machinedata.shift_idShift 
        WHERE 
            shift.machine_idMachine = '$machine_id' 
        AND 
            machinedata.userid = '$userid'";

if ($order) {
    $sql .= " AND machinedata.`order` = '$order'"; 
}

if ($from) {
    $sql .= " AND shift.startTime >= '$from'";
}

if ($to) {
    $sql .= " AND (shift.endTime <= '$to' OR shift.endTime IS NULL)";
}

$sql .= " GROUP BY machinedata.`order`, shift.idShift"; // Gruppierung nach orderid UND idShift
$sql .= " ORDER BY shift.startTime ASC";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'shiftid' => $row['shiftid'], 
        'machineid' => $row['machineid'],   
        'userid' => $row['userid'],   
        'orderid' => $row['orderid'], 
        'startTime' => $row['startTime'],
        'endTime' => $row['endTime'],
        'total_value' => $row['total_value'] // Die Summe der Werte für diese Schicht und Order
    ];
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "Keine Schichten bei diesem Benutzer und dieser Maschine gefunden."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}

$machineconn->close();

