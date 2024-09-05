<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

if (!$orderid) {
    http_response_code(400);
    $errorMessage = "orderid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    exit();
}

$machineIdSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
$machineIdResult = $machineconn->query($machineIdSql);

if ($machineIdResult->num_rows > 0) {
    $machine = $machineIdResult->fetch_assoc();
    $machine_id = $machine['idMachine'];

    $updateMachineSql = "UPDATE machine SET userid = NULL, `order` = NULL, state = 'stop' WHERE idMachine = $machine_id";
    
    if ($machineconn->query($updateMachineSql) === TRUE) {
        $endShiftSql = "UPDATE shift SET endTime = DATE_FORMAT(NOW(), '%Y-%m-%dT%H:%i:%s') WHERE machine_idMachine = $machine_id AND endTime IS NULL";
        $machineconn->query($endShiftSql);
        
        echo json_encode(["status" => "success", "message" => "machine state updated to 'stop' and shift ended."], JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "failed to update machine state: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "no machine found for orderid: $orderid"], JSON_PRETTY_PRINT);
}
