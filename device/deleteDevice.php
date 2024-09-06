<?php

$idDevice = $machineconn->real_escape_string(trim($_GET['deviceid'] ?? null));

if (!$idDevice) {
    http_response_code(400);
    $errorMessage = "deviceid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$sql = "DELETE FROM device WHERE idDevice = '$idDevice'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Device successfully deleted."], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "Device with idDevice: $idDevice successfully deleted.");
    } else {
        http_response_code(404);
        $errorMessage = "No device found that could be deleted.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(500);
    $errorMessage = "Error deleting the device: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
