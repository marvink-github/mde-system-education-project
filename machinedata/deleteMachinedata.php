<?php

$idMachinedata = $machineconn->real_escape_string(trim($_GET['dataid'] ?? null));

if (!$idMachinedata) {
    http_response_code(400); 
    $errorMessage = "machinedataid required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'delete', $errorMessage);
    exit();
}

$checkSql = "SELECT * FROM machinedata WHERE idMachinedata = '$idMachinedata'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(404); 
    $errorMessage = "Machinedata not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
    exit();
}

$deleteSql = "DELETE FROM machinedata WHERE idMachinedata = '$idMachinedata'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    $errorMessage = "Machinedata successfully deleted. id: $idMachinedata";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', $errorMessage);
} else {
    http_response_code(500); 
    $errorMessage = "Failed to delete machinedata: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
