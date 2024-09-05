<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

if (!$orderid) {
    http_response_code(400);
    $errorMessage = "orderid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$machineIdSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
$machineIdResult = $machineconn->query($machineIdSql);

if ($machineIdResult->num_rows > 0) {
    $machine = $machineIdResult->fetch_assoc();
    $machine_id = $machine['idMachine'];

    // Überprüfe, ob bereits eine aktive Schicht für diese Maschine existiert
    $activeShiftSql = "SELECT idShift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $activeShiftResult = $machineconn->query($activeShiftSql);

    // Wenn keine aktive Schicht existiert, erstelle eine neue
    if ($activeShiftResult->num_rows === 0) {
        $createShiftSql = "INSERT INTO shift (machine_idMachine, startTime) VALUES ($machine_id, DATE_FORMAT(NOW(), '%Y-%m-%dT%H:%i:%s'))";
        if ($machineconn->query($createShiftSql) === TRUE) {
            // Update machine state and order
            $updateMachineSql = "UPDATE machine SET state = 'start', `order` = '$orderid' WHERE idMachine = $machine_id";
            if ($machineconn->query($updateMachineSql) === TRUE) {
                echo json_encode(["status" => "success", "message" => "machine state updated to 'start' with orderid: $orderid"], JSON_PRETTY_PRINT);
                logDB($machineconn, 'info', "Machine state updated to start with orderid: $orderid for machine_id: $machine_id.");
            } else {
                http_response_code(400);
                $errorMessage = "failed to update machine state: " . $machineconn->error;
                echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
                logDB($machineconn, 'error', $errorMessage);
            }
        } else {
            http_response_code(400);
            $errorMessage = "failed to create shift: " . $machineconn->error;
            echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
            logDB($machineconn, 'error', $errorMessage);
        }
    } else {
        http_response_code(400);
        $errorMessage = "an active shift already exists for this machine.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "no machine found for orderid: $orderid";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}
