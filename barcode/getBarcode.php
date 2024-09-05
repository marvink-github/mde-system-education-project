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

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

$result = $machineconn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "success",
            "orderid" => $barcode,
            "total" => (int)$result->num_rows 
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', "entries found for orderid (barcode): $barcode");
    } else {
        http_response_code(404);
        $errorMessage = "no entries found for this orderid (barcode) in machinedata.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(500);
    $errorMessage = "database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
