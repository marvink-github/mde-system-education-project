<?php

$idDevice = $machineconn->real_escape_string(trim($_GET['deviceid'] ?? null));

if (!$idDevice) {
    http_response_code(400);
    echo json_encode(["message" => "deviceid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "DELETE FROM device WHERE idDevice = '$idDevice'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "device successfully deleted."], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "no device found that could be deleted."], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "error deleting the device: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
