<?php

$data = json_decode(file_get_contents("php://input"), true);

$deviceId = $machineconn->real_escape_string(trim($data['deviceid'] ?? $_GET['deviceid'] ?? null));

if (!$deviceId) {
    http_response_code(400);
    $errorMessage = "deviceid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
    exit();
}

$sqlCheck = "SELECT * FROM device WHERE idDevice = '$deviceId'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(404);
    $errorMessage = "Device not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
    exit();
}

$updateFields = [];

if (isset($data['terminalid']) && $data['terminalid'] !== "") {
    $updateFields[] = "terminal_id = '" . $machineconn->real_escape_string(trim($data['terminalid'])) . "'";
}
if (isset($data['terminaltype']) && $data['terminaltype'] !== "") {
    $updateFields[] = "terminal_type = '" . $machineconn->real_escape_string(trim($data['terminaltype'])) . "'";
}
if (isset($data['last_alive']) && $data['last_alive'] !== "") {
    $updateFields[] = "last_alive = '" . $machineconn->real_escape_string(trim($data['last_alive'])) . "'";
}

if (!empty($updateFields)) {
    $updateDeviceSql = "UPDATE device SET " . implode(", ", $updateFields) . " WHERE idDevice = '$deviceId'";

    if ($machineconn->query($updateDeviceSql) === TRUE) {
        $resultCheckUpdated = $machineconn->query($sqlCheck);
        $updatedData = $resultCheckUpdated->fetch_assoc();

        http_response_code(200);
        echo json_encode([
            "message" => "Device information successfully patched.",
            "deviceid" => $deviceId,
            "terminalid" => $updatedData['terminal_id'] ?? null,
            "terminaltype" => $updatedData['terminal_type'] ?? null,
            "last_alive" => $updatedData['last_alive'] ?? null
        ], JSON_PRETTY_PRINT);
        
        logDB($machineconn, 'info', "Device information successfully updated for device id: $deviceId");
    } else {
        http_response_code(500);
        $errorMessage = "Error updating device data: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "No changes specified.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}
