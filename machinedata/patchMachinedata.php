<?php

$data = json_decode(file_get_contents("php://input"), true);

$idMachinedata = $machineconn->real_escape_string(trim($data['dataid'] ?? $_GET['dataid'] ?? null));

if (!$idMachinedata) {
    http_response_code(400);
    $errorMessage = "dataid (idMachinedata) is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$userid = $machineconn->real_escape_string(trim($data['userid'] ?? null)); 
$orderid = $machineconn->real_escape_string(trim($data['orderid'] ?? null));
$shiftid = $machineconn->real_escape_string(trim($data['shiftid'] ?? null));

$sqlCheck = "SELECT * FROM machinedata WHERE idMachinedata = '$idMachinedata'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    $errorMessage = "machinedata not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
    exit();
}

$updateFields = [];

if (!empty($userid)) {
    $updateFields[] = "userid = '$userid'";
}
if ($orderid !== null && $orderid !== "") {
    $updateFields[] = "`order` = '$orderid'";
}
if ($shiftid !== null && $shiftid !== "") {
    $updateFields[] = "shift_idshift = '$shiftid'";
}
if (isset($data['value']) && $data['value'] !== "") {
    $updateFields[] = "value = '" . $machineconn->real_escape_string(trim($data['value'])) . "'";
}
if (isset($data['timestamp']) && $data['timestamp'] !== "") {
    $updateFields[] = "timestamp = '" . $machineconn->real_escape_string(trim($data['timestamp'])) . "'";
}

if (!empty($updateFields)) {
    $updateMachinedataSql = "UPDATE machinedata SET " . implode(", ", $updateFields) . " WHERE idMachinedata = '$idMachinedata'";

    if ($machineconn->query($updateMachinedataSql) === TRUE) {
        $resultCheckUpdated = $machineconn->query($sqlCheck); 
        $updatedData = $resultCheckUpdated->fetch_assoc();

        http_response_code(200);
        $successMessage = "Machinedata information successfully patched.";
        echo json_encode([
            "message" => $successMessage,
            "dataid" => $idMachinedata,
            "timestamp" => $updatedData['timestamp'] ?? null,
            "userid" => $updatedData['userid'] ?? null,
            "value" => $updatedData['value'] ?? null, 
            "orderid" => $updatedData['order'] ?? null,         
            "shiftid" => $updatedData['shift_idshift'] ?? null,   
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', $successMessage . " dataid: " . $idMachinedata);
    } else {
        http_response_code(400);
        $errorMessage = "Error updating machinedata: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage);
    }
} else {
    http_response_code(400);
    $errorMessage = "No changes specified.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage);
}
