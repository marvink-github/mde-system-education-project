<?php
require_once("../connection.php"); 

$machineId = $machineconn->real_escape_string(trim($_GET['id'] ?? null));

if (!$machineId) {
    http_response_code(400); 
    echo json_encode(["message" => "machineid required."], JSON_PRETTY_PRINT);
    exit();
}

$checkSql = "SELECT * FROM machine WHERE idMachine = '$machineId'";
$checkResult = $machineconn->query($checkSql);

if ($checkResult->num_rows === 0) {
    http_response_code(400); 
    echo json_encode(["message" => "Machine not found."], JSON_PRETTY_PRINT);
    exit();
}

$deleteSql = "DELETE FROM machine WHERE idMachine = '$machineId'";

if ($machineconn->query($deleteSql) === TRUE) {
    http_response_code(200); 
    echo json_encode(["message" => "Machine successfully deleted."], JSON_PRETTY_PRINT);
} else {
    http_response_code(400); 
    echo json_encode(["message" => "Failed to delete the machine." . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();

