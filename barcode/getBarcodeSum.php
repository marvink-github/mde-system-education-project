<?php
require_once("../connection.php");

$value = $machineconn->real_escape_string(trim($_GET['barcode'] ?? null));

if (!$value) {
    http_response_code(400);
    echo json_encode(["message" => "barcode is required."], JSON_PRETTY_PRINT);
    exit();
}

$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

$sql = "SELECT * FROM machinedata WHERE value = '$value'";
$result = $machineconn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!$userid) {
        $userid = $row['userid'] ?? '';
    }

    if (!$orderid) {
        $orderid = $row['order'] ?? '';
    }

    echo json_encode([
        "status" => "success",
        "userid" => $userid,
        "orderid" => $orderid,
        "barcode" => $value,
        "total" => (int)$result->num_rows
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "No entry found for this barcode in machinedata."], JSON_PRETTY_PRINT);
}

$machineconn->close();
