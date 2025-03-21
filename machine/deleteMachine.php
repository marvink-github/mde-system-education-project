<?php

$machineId = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));

if (!$machineId) {
    http_response_code(400); 
    $errorMessage = "machineid required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'delete', $errorMessage);
    exit();
}

$checkSql = "SELECT * FROM machine WHERE idMachine = '$machineId'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(404); 
    $errorMessage = "Machine not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
    exit();
}

$deleteSql = "DELETE FROM machine WHERE idMachine = '$machineId'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    $errorMessage = "Machineid: $machineId successfully deleted.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', $errorMessage);
} else {
    http_response_code(500); 
    $errorMessage = "Failed to delete the machine: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
