<?php
require_once("../connection.php");

$terminal_id = $machineconn->real_escape_string(trim($_GET['terminalid'] ?? null));
$terminal_type = $machineconn->real_escape_string(trim($_GET['terminaltype'] ?? null));

if (!$terminal_id || !$terminal_type) {
    http_response_code(400);
    echo json_encode(["message" => "terminalid and terminaltype are required."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT last_alive FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$result = $machineconn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        "last_alive" => $row['last_alive'] 
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "no entry found for this device."], JSON_PRETTY_PRINT);
}

$machineconn->close();
