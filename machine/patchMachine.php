<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

$machine_id = $machineconn->real_escape_string(trim($data['machineid'] ?? $_GET['machineid'] ?? null));

if (!$machine_id) {
    http_response_code(400);
    echo json_encode(["message" => "Machineid required."], JSON_PRETTY_PRINT);
    exit();
}

$userid = $machineconn->real_escape_string(trim($data['userid'] ?? null)); 
$orderid = $machineconn->real_escape_string(trim($data['orderid'] ?? null));

$sqlCheck = "SELECT * FROM machine WHERE idMachine = '$machine_id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Machine not found."], JSON_PRETTY_PRINT);
    exit();
}

if (empty($userid) && empty($orderid) && empty($data['name']) && empty($data['d_entry_startstop']) && empty($data['d_entry_counter']) && empty($data['device_idDevice'])) {
    http_response_code(400);
    echo json_encode(["message" => "At least one is required. (userid, orderid, machinename, d_entry_startstop, d_entry_counter or device_idDevice."], JSON_PRETTY_PRINT);
    exit();
}

$updateFields = [];

if (!empty($userid)) {
    $updateFields[] = "userid = '$userid'";
}
if (!empty($order)) {
    $updateFields[] = "`order` = '$orderid'";
}
if (isset($data['name'])) {
    $updateFields[] = "name = '" . $machineconn->real_escape_string(trim($data['name'])) . "'";
}
if (isset($data['d_entry_startstop'])) {
    $updateFields[] = "d_entry_startstop = '" . $machineconn->real_escape_string(trim($data['d_entry_startstop'])) . "'";
}
if (isset($data['d_entry_counter'])) {
    $updateFields[] = "d_entry_counter = '" . $machineconn->real_escape_string(trim($data['d_entry_counter'])) . "'";
}
if (isset($data['device_idDevice'])) {
    $updateFields[] = "device_idDevice = '" . $machineconn->real_escape_string(trim($data['device_idDevice'])) . "'";
}

if (!empty($updateFields)) {
    $updateMachineSql = "UPDATE machine SET " . implode(", ", $updateFields) . " WHERE idMachine = '$machine_id'";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $resultCheckUpdated = $machineconn->query($sqlCheck); 
        $updatedData = $resultCheckUpdated->fetch_assoc();

        http_response_code(200);
        echo json_encode([
            "message" => "Machineinformation successfully patched.",
            "machineId" => $machine_id,
            "userid" => $updatedData['userid'] ?? null,
            "orderid" => $updatedData['order'] ?? null,
            "machinename" => $updatedData['name'] ?? null,
            "d_entry_startstop" => $updatedData['d_entry_startstop'] ?? null,
            "d_entry_counter" => $updatedData['d_entry_counter'] ?? null,
            "device_idDevice" => $updatedData['device_idDevice'] ?? null
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Error updating machinedata: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "No changes specified."], JSON_PRETTY_PRINT);
}

$machineconn->close();
