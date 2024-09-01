<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['machineid'])) {
    http_response_code(400);
    echo json_encode(["message" => "machineid erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$machine_id = $machineconn->real_escape_string($data['machineid']);
$userid = $machineconn->real_escape_string($data['userid'] ?? null); 
$order = $machineconn->real_escape_string($data['orderid'] ?? null);

$sqlCheck = "SELECT * FROM machine WHERE idMachine = '$machine_id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Maschine nicht gefunden."], JSON_PRETTY_PRINT);
    exit();
}

if (empty($userid) && empty($order)) {
    http_response_code(400);
    echo json_encode(["message" => "Mindestens userid oder orderid ist erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$updateMachineSql = "UPDATE machine SET";

$updates = [];
if ($userid !== null && !empty($userid)) {
    $updates[] = "userid = '$userid'";
}
if ($order !== null) {
    $updates[] = "`order` = '$order'"; 
}

if (!empty($updates)) {
    $updateMachineSql .= " " . implode(", ", $updates) . " WHERE idMachine = '$machine_id'";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        http_response_code(200);
        echo json_encode([
            "message" => "Maschineninformationen erfolgreich aktualisiert.",
            "machineId" => $machine_id,
            "userid" => $userid,
            "orderid" => $order
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Fehler beim Aktualisieren der Maschinen: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Keine Änderungen angegeben."], JSON_PRETTY_PRINT);
}

$machineconn->close();
?>
