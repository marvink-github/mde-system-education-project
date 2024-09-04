<?php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(["message" => "id is required."], JSON_PRETTY_PRINT);
    exit();
}

$id = $machineconn->real_escape_string(trim($data['id']));
$orderid = isset($data['orderid']) ? $machineconn->real_escape_string(trim($data['orderid'])) : null;
$userid = isset($data['userid']) ? $machineconn->real_escape_string(trim($data['userid'])) : null;

$sqlCheck = "SELECT * FROM machinedata WHERE `idMachinedata` = '$id'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "no entry found in machinedata."], JSON_PRETTY_PRINT);
    exit();
}

$sqlUpdate = "UPDATE machinedata SET";

if ($orderid !== null) {
    $sqlUpdate .= " `order` = '$orderid',";
}

if ($userid !== null) {
    $sqlUpdate .= " `userid` = '$userid',";
}

$sqlUpdate = rtrim($sqlUpdate, ',') . " WHERE `idMachinedata` = '$id'";

if ($machineconn->query($sqlUpdate) === TRUE) {
    http_response_code(200);
    echo json_encode([
        "message" => "data successfully patched in machinedata.",
        "id" => $id,
        "orderid" => $orderid,
        "userid" => $userid
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "error patching machinedata: " . $machineconn->error], JSON_PRETTY_PRINT);
}

