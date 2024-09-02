<?php
require_once("../connection.php");

$idShift = $machineconn->real_escape_string(trim($_GET['shiftid'] ?? null));

if (!$idShift) {
    http_response_code(400);
    echo json_encode(["message" => "shiftid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "DELETE FROM shift WHERE idShift = '$idShift'";

if ($machineconn->query($sql) === TRUE) {
    if ($machineconn->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Shift successfully deleted."], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "No shift found that could be deleted."], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Error deleting shift: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
