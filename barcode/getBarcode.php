<?php

$barcode = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

if (!$barcode) {
    http_response_code(400);
    echo json_encode(["message" => "orderid (barcode) is required."], JSON_PRETTY_PRINT);
    exit();
}

$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

$sql = "SELECT * FROM machinedata WHERE `order` = '$barcode'";
$result = $machineconn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    if (!$userid) {
        $userid = $row['userid'] ?? '';
    }

    echo json_encode([
        "status" => "success",
        "userid" => $userid,
        "orderid" => $barcode,
        "total" => (int)$result->num_rows
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "no entry found for this orderid (barcode) in machinedata."], JSON_PRETTY_PRINT);
}
