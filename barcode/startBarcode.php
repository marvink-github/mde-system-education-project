<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null)); 

$machineIdSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
$machineIdResult = $machineconn->query($machineIdSql);

if ($machineIdResult->num_rows > 0) {
    $machine = $machineIdResult->fetch_assoc();
    $machine_id = $machine['idMachine'];

    $activeShiftSql = "SELECT idShift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $activeShiftResult = $machineconn->query($activeShiftSql);

    if ($activeShiftResult->num_rows === 0) {
        $createShiftSql = "INSERT INTO shift (machine_idMachine, startTime) VALUES ($machine_id, DATE_FORMAT(NOW(), '%Y-%m-%dT%H:%i:%s'))";
        if ($machineconn->query($createShiftSql) === TRUE) {
            $updateMachineSql = "UPDATE machine SET state = 'start'";
            
            if ($orderid) {
                $updateMachineSql .= ", `order` = '$orderid'";
            }

            if ($userid) {
                $updateMachineSql .= ", `userid` = '$userid'";
            }

            $updateMachineSql .= " WHERE idMachine = $machine_id";

            if ($machineconn->query($updateMachineSql) === TRUE) {
                $machineDataSql = "SELECT * FROM machine WHERE idMachine = $machine_id";
                $machineDataResult = $machineconn->query($machineDataSql);

                if ($machineDataResult->num_rows > 0) {
                    $updatedData = $machineDataResult->fetch_assoc();

                    echo json_encode([
                        "message" => "Machine and shift successfully started.",
                        "idMachinedata" => $updatedData['idMachine'],
                        "machinename" => $updatedData['name'] ?? null,
                        "userid" => $updatedData['userid'] ?? null,
                        "orderid" => $updatedData['order'] ?? null,
                        "state" => $updatedData['state'] ?? null,
                        "device_idDevice" => $updatedData['device_idDevice'] ?? null
                    ], JSON_PRETTY_PRINT);
                    
                } else {
                    http_response_code(400);
                    $errorMessage = "Failed to retrieve updated machine data.";
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
            http_response_code(400);
            $errorMessage = "Failed to create shift: " . $machineconn->error;
            echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
            logDB($machineconn, 'error', $errorMessage);
        }
    } else {
        http_response_code(400);
        $errorMessage = "An active shift already exists for this machine.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(404);
    $errorMessage = "No machine found for orderid: $orderid";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}

