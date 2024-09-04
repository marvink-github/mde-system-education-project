<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['dataid'])) {
    http_response_code(400);
    $errorMessage = "dataid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$id = $machineconn->real_escape_string(trim($data['dataid']));
$orderid = isset($data['orderid']) ? $machineconn->real_escape_string(trim($data['orderid'])) : null;
$userid = isset($data['userid']) ? $machineconn->real_escape_string(trim($data['userid'])) : null;

$sqlCheck = "SELECT * FROM machinedata WHERE `idMachinedata` = '$id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    $errorMessage = "no entry found in machinedata.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$updateFields = [];

if ($orderid !== null) {
    $updateFields[] = "`order` = '$orderid'";
}

if ($userid !== null) {
    $updateFields[] = "`userid` = '$userid'";
}

if (!empty($updateFields)) {
    $sqlUpdate = "UPDATE machinedata SET " . implode(", ", $updateFields) . " WHERE `idMachinedata` = '$id'";

    if ($machineconn->query($sqlUpdate) === TRUE) {
        http_response_code(200);
        echo json_encode([
            "message" => "data successfully patched in machinedata.",
            "id" => $id,
            "orderid" => $orderid,
            "userid" => $userid
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "data successfully patched for idMachinedata: $id");
    } else {
        http_response_code(400);
        $errorMessage = "error patching machinedata: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "no changes specified.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}
