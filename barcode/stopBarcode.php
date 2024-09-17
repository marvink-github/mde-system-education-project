<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

$machineIdSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
$machineIdResult = $machineconn->query($machineIdSql);

if ($machineIdResult->num_rows > 0) {
    $machine = $machineIdResult->fetch_assoc();
    $machine_id = $machine['idMachine'];

    $updateMachineSql = "UPDATE machine SET userid = NULL, `order` = NULL, state = 'stop' WHERE idMachine = $machine_id";
    
    if ($machineconn->query($updateMachineSql) === TRUE) {
        $endShiftSql = "UPDATE shift SET endTime = DATE_FORMAT(NOW(), '%Y-%m-%dT%H:%i:%s') WHERE machine_idMachine = $machine_id AND endTime IS NULL";
        if ($machineconn->query($endShiftSql) === TRUE) {
            $machineDataSql = "SELECT * FROM machine WHERE idMachine = $machine_id";
            $machineDataResult = $machineconn->query($machineDataSql);

            if ($machineDataResult->num_rows > 0) {
                $updatedData = $machineDataResult->fetch_assoc();

                echo json_encode([
                    "message" => "Machine and shift succesfully stopped.",
                    "machineid" => $updatedData['idMachine'],
                    "machinename" => $updatedData['name'] ?? null,
                    "userid" => $updatedData['userid'] ?? null,
                    "orderid" => $updatedData['order'] ?? null,
                    "state" => $updatedData['state'] ?? null,
                    "device_idDevice" => $updatedData['device_idDevice'] ?? null
                ], JSON_PRETTY_PRINT);
                http_response_code(200);
                logDB($machineconn, 'info', "Machine $machine_id and shift succesfully stopped.");
            } else {
                http_response_code(400);
                $errorMessage = "Failed to retrieve updated machine data.";
                echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
                logDB($machineconn, 'error', $errorMessage);
            }
        } else {
            http_response_code(400);
            $errorMessage = "Failed to end shift: " . $machineconn->error;
            echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
            logDB($machineconn, 'error', $errorMessage);
        }
    } else {
        http_response_code(400);
        $errorMessage = "Failed to update machine state: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(404);
    $errorMessage = "No machine found for orderid: $orderid";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}

