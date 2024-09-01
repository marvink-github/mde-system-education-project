<?php
require_once("../connection.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['barcode']) || !isset($data['orderid'])) {
    http_response_code(400);
    echo json_encode(["message" => "barcode und orderid sind erforderlich."], JSON_PRETTY_PRINT);
    exit();
}

$barcode = $machineconn->real_escape_string($data['barcode']);
$orderid = $machineconn->real_escape_string($data['orderid']);

$sqlCheck = "SELECT * FROM machinedata WHERE value = '$barcode'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "Kein Eintrag für das barcode in machinedata gefunden."], JSON_PRETTY_PRINT);
    exit();
}

$sqlUpdate = "UPDATE machinedata SET `order` = '$orderid' WHERE value = '$barcode'";

if ($machineconn->query($sqlUpdate) === TRUE) {
    http_response_code(200);
    echo json_encode([
        "message" => "orderid erfolgreich in machinedata aktualisiert.",
        "barcode" => $barcode,
        "orderid" => $orderid
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Aktualisieren der machinedata: " . $machineconn->error], JSON_PRETTY_PRINT);
}

$machineconn->close();
