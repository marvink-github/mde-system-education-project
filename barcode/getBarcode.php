<?php

$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

$barcodeMachineSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
$barcodeMachineResult = $machineconn->query($barcodeMachineSql);

if ($barcodeMachineResult->num_rows > 0) {
    $barcodeMachine = $barcodeMachineResult->fetch_assoc();
    $barcodeMachineId = $barcodeMachine['idMachine'];

    $sql = "SELECT md.* 
            FROM machinedata md
            JOIN shift s ON md.shift_idShift = s.idShift 
            WHERE s.machine_idMachine = $barcodeMachineId"; 

    if ($orderid) {
        $sql .= " AND md.`order` = '$orderid'";
    }

    if ($userid) {
        $sql .= " AND md.userid = '$userid'";
    }

    $result = $machineconn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            echo json_encode([
                "status" => "success",
                "userid" => $userid ?? null,
                "orderid" => $orderid ?? null,
                "total" => (int)$result->num_rows
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(404);
            $errorMessage = "No entries found at the barcode machine.";
            echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
            logDB($machineconn, 'warning', $errorMessage);
        }
    } else {
        http_response_code(500);
        $errorMessage = "Database query failed: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(404);
    $errorMessage = "Barcode machine not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
