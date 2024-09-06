<?php

$idShift = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null));

if (!$idShift) {
    http_response_code(400);
    $errorMessage = "shiftid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$sql = "DELETE FROM shift WHERE idShift = '$idShift'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        $successMessage = "Shift successfully deleted.";
        echo json_encode(["message" => $successMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', $successMessage);
    } else {
        http_response_code(404);
        $errorMessage = "No shift found that could be deleted.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', $errorMessage);
    }
} else {
    http_response_code(500);
    $errorMessage = "Error deleting shift: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
