<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['barcode'])) {
    http_response_code(400);
    echo json_encode(["message" => "barcode is required."], JSON_PRETTY_PRINT);
    exit();
}

$barcode = $machineconn->real_escape_string(trim($data['barcode']));
$userid = isset($data['userid']) ? $machineconn->real_escape_string(trim($data['userid'])) : null;
$orderid = isset($data['orderid']) ? $machineconn->real_escape_string(trim($data['orderid'])) : null;

if (empty($userid) && empty($orderid)) {
    http_response_code(400);
    echo json_encode(["message" => "userid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sqlCheck = "SELECT * FROM machinedata WHERE value = '$barcode'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "No entry found for this barcode in machinedata."], JSON_PRETTY_PRINT);
    exit();
}

$currentData = $resultCheck->fetch_assoc();
$currentOrderId = $currentData['order'] ?? null;
$currentUserId = $currentData['userid'] ?? null;

$sqlUpdate = "UPDATE machinedata SET `order` = " . ($orderid !== null ? "'$orderid'" : "'$currentOrderId'");

if ($userid !== null) {
    $sqlUpdate .= ", `userid` = '$userid'";
} else {
    $sqlUpdate .= ", `userid` = '$currentUserId'"; 
}

$sqlUpdate .= " WHERE value = '$barcode'";

if ($machineconn->query($sqlUpdate) === TRUE) {
    http_response_code(200);
    echo json_encode([
        "message" => "Data successfully updated in machinedata.",
        "barcode" => $barcode,
        "userid" => $userid ?? $currentUserId, 
        "order" => $orderid ?? $currentOrderId
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Error updating machinedata: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
