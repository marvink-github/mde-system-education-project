<?php

$data = json_decode(file_get_contents("php://input"), true);

$idMachinedata = $machineconn->real_escape_string(trim($data['dataid'] ?? $_GET['dataid'] ?? null));

if (!$idMachinedata) {
    http_response_code(400);
    echo json_encode(["message" => "dataid (idMachinedata) is required."], JSON_PRETTY_PRINT);
    exit();
}

$userid = $machineconn->real_escape_string(trim($data['userid'] ?? null)); 
$orderid = $machineconn->real_escape_string(trim($data['orderid'] ?? null));
$shiftid = $machineconn->real_escape_string(trim($data['shiftid'] ?? null));

$sqlCheck = "SELECT * FROM machinedata WHERE idMachinedata = '$idMachinedata'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "machinedata not found."], JSON_PRETTY_PRINT);
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
        echo json_encode([
            "message" => "machinedata information successfully patched.",
            "dataid" => $idMachinedata,
            "timestamp" => $updatedData['timestamp'] ?? null,
            "userid" => $updatedData['userid'] ?? null,
            "value" => $updatedData['value'] ?? null, 
            "orderid" => $updatedData['order'] ?? null,         
            "shiftid" => $updatedData['shift_idshift'] ?? null,   
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "error updating machinedata: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "no changes specified."], JSON_PRETTY_PRINT);
}

