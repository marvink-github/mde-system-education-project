<?php

$idMachinedata = $machineconn->real_escape_string(trim($_GET['dataid'] ?? null));

if (!$idMachinedata) {
    http_response_code(400); 
    $errorMessage = "dataid (idMachinedata) required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$checkSql = "SELECT * FROM machinedata WHERE idMachinedata = '$idMachinedata'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(404); 
    $errorMessage = "machinedata not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$deleteSql = "DELETE FROM machinedata WHERE idMachinedata = '$idMachinedata'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    echo json_encode(["message" => "machinedata successfully deleted."], JSON_PRETTY_PRINT);
    logDB($machineconn, 'info', "deleted machinedata with id: $idMachinedata");
} else {
    http_response_code(500); 
    $errorMessage = "failed to delete the machinedata: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
