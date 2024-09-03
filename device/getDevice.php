<?php

$idDevice = $machineconn->real_escape_string(trim($_GET['deviceid'] ?? null));

if ($idDevice) {
    $sql = "SELECT * FROM device WHERE idDevice = '$idDevice'";
} else {
    $sql = "SELECT * FROM device";
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "database query failed: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);




