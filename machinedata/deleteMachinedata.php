<?php

$idMachinedata = $machineconn->real_escape_string(trim($_GET['dataid'] ?? null));

if (!$idMachinedata) {
    http_response_code(400); 
    echo json_encode(["message" => "dataid (idMachinedata) required."], JSON_PRETTY_PRINT);
    exit();
}

$checkSql = "SELECT * FROM machinedata WHERE idMachinedata = '$idMachinedata'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(400); 
    echo json_encode(["message" => "machinedata not found."], JSON_PRETTY_PRINT);
    exit();
}

$deleteSql = "DELETE FROM machinedata WHERE idMachinedata = '$idMachinedata'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    echo json_encode(["message" => "machinedata successfully deleted."], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "failed to delete the machinedata. " . $machineconn->error], JSON_PRETTY_PRINT);
}
