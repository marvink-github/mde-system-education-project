<?php

$data = json_decode(file_get_contents("php://input"), true);

$machine_id = $machineconn->real_escape_string(trim($data['machineid'] ?? $_GET['machineid'] ?? null));

if (!$machine_id) {
    http_response_code(400);
    $errorMessage = "machineid required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'patch', $errorMessage);
    exit();
}

$userid = $machineconn->real_escape_string(trim($data['userid'] ?? null)); 
$orderid = $data['orderid'] ?? null;

$sqlCheck = "SELECT * FROM machine WHERE idMachine = '$machine_id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(404);
    $errorMessage = "Machine not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
    exit();
}

$updateFields = [];

if (!empty($userid)) {
    $updateFields[] = "userid = '$userid'";
}
if ($orderid !== null && $orderid !== "") {
    $updateFields[] = "`order` = '" . $machineconn->real_escape_string(trim($orderid)) . "'";
}
if (isset($data['name']) && $data['name'] !== "") {
    $updateFields[] = "name = '" . $machineconn->real_escape_string(trim($data['name'])) . "'";
}
if (isset($data['state']) && $data['state'] !== "") {
    $updateFields[] = "state = '" . $machineconn->real_escape_string(trim($data['state'])) . "'";
}
if (isset($data['d_entry_startstop']) && $data['d_entry_startstop'] !== "") {
    $updateFields[] = "d_entry_startstop = '" . $machineconn->real_escape_string(trim($data['d_entry_startstop'])) . "'";
}
if (isset($data['d_entry_counter']) && $data['d_entry_counter'] !== "") {
    $updateFields[] = "d_entry_counter = '" . $machineconn->real_escape_string(trim($data['d_entry_counter'])) . "'";
}
if (isset($data['device_idDevice']) && $data['device_idDevice'] !== "") {
    $updateFields[] = "device_idDevice = '" . $machineconn->real_escape_string(trim($data['device_idDevice'])) . "'";
}

if (!empty($updateFields)) {
    $updateMachineSql = "UPDATE machine SET " . implode(", ", $updateFields) . " WHERE idMachine = '$machine_id'";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $resultCheckUpdated = $machineconn->query($sqlCheck); 
        $updatedData = $resultCheckUpdated->fetch_assoc();

        http_response_code(200);
        echo json_encode([
            "message" => "Machine information successfully patched.",
            "machineId" => $machine_id,
            "machinename" => $updatedData['name'] ?? null,
            "userid" => $updatedData['userid'] ?? null,
            "order" => $updatedData['order'] ?? null,         
            "state" => $updatedData['state'] ?? null,   
            "d_entry_startstop" => $updatedData['d_entry_startstop'] ?? null,
            "d_entry_counter" => $updatedData['d_entry_counter'] ?? null,
            "device_idDevice" => $updatedData['device_idDevice'] ?? null             
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "Machine information updated successfully for id: $machine_id");
    } else {
        http_response_code(500);
        $errorMessage = "Error updating machine data: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "No changes specified.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}
