<?php

$data = json_decode(file_get_contents("php://input"), true);

$shiftId = $machineconn->real_escape_string(trim($data['shiftid'] ?? $_GET['shiftid'] ?? null));

if (!$shiftId) {
    http_response_code(400);
    $errorMessage = "shiftid is required.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
    exit();
}

$sqlCheck = "SELECT * FROM shift WHERE idShift = '$shiftId'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    $errorMessage = "shift not found.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage); 
    exit();
}

$updateFields = [];

if (isset($data['starttime']) && $data['starttime'] !== "") {
    $updateFields[] = "startTime = '" . $machineconn->real_escape_string(trim($data['starttime'])) . "'";
}
if (isset($data['endtime']) && $data['endtime'] !== "") {
    $updateFields[] = "endTime = '" . $machineconn->real_escape_string(trim($data['endtime'])) . "'";
}
if (isset($data['machineid']) && $data['machineid'] !== "") {
    $updateFields[] = "machine_idMachine = '" . $machineconn->real_escape_string(trim($data['machineid'])) . "'";
}

if (!empty($updateFields)) {
    $updateShiftSql = "UPDATE shift SET " . implode(", ", $updateFields) . " WHERE idShift = '$shiftId'";

    if ($machineconn->query($updateShiftSql) === TRUE) {
        $resultCheckUpdated = $machineconn->query($sqlCheck);
        $updatedData = $resultCheckUpdated->fetch_assoc();

        http_response_code(200);
        $successMessage = "shift information successfully patched.";
        echo json_encode([
            "message" => $successMessage,
            "shiftid" => $shiftId,
            "starttime" => $updatedData['startTime'] ?? null,
            "endtime" => $updatedData['endTime'] ?? null,
            "machineid" => $updatedData['machine_idMachine'] ?? null
        ], JSON_PRETTY_PRINT);
        logDB($machineconn, 'info', $successMessage . " shiftid: " . $shiftId); 
    } else {
        http_response_code(400);
        $errorMessage = "error updating shift data: " . $machineconn->error;
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'error', $errorMessage); 
    }
} else {
    http_response_code(400);
    $errorMessage = "no changes specified.";
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'warning', $errorMessage); 
}
