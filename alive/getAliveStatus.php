<?php

$terminal_id = $machineconn->real_escape_string(trim($_GET['terminalid'] ?? null));
$terminal_type = $machineconn->real_escape_string(trim($_GET['terminaltype'] ?? null));

if (!$terminal_id || !$terminal_type) {
    http_response_code(400);
    $errorMessage = "terminalid and terminaltype are required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$sql = "SELECT last_alive FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$result = $machineconn->query($sql);

if ($result) {
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "last_alive" => $row['last_alive']
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "Successfully retrieved last_alive for terminal_id: $terminal_id, terminal_type: $terminal_type.");
    } else {
        http_response_code(404);
        $errorMessage = "No entry found for this device.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(500);
    $errorMessage = "Database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
