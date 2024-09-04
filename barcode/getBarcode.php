<?php

$barcode = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

if (!$barcode) {
    http_response_code(400);
    $errorMessage = "orderid (barcode) is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));

$sql = "SELECT * FROM machinedata WHERE `order` = '$barcode'";
$result = $machineconn->query($sql);

if ($result) {
    if ($row = $result->fetch_assoc()) {
        if (!$userid) {
            $userid = $row['userid'] ?? '';
        }

        echo json_encode([
            "status" => "success",
            "userid" => $userid,
            "orderid" => $barcode,
            "total" => (int)$result->num_rows
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "entry found for orderid (barcode): $barcode");
    } else {
        http_response_code(400);
        $errorMessage = "no entry found for this orderid (barcode) in machinedata.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}

