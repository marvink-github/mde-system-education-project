<?php

$data = json_decode(file_get_contents("php://input"), true);

$deviceId = $machineconn->real_escape_string(trim($data['deviceid'] ?? $_GET['deviceid'] ?? null));

if (!$deviceId) {
    http_response_code(400);
    echo json_encode(["message" => "deviceid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sqlCheck = "SELECT * FROM device WHERE idDevice = '$deviceId'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "device not found."], JSON_PRETTY_PRINT);
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
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Error updating device data: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "No changes specified."], JSON_PRETTY_PRINT);
}
