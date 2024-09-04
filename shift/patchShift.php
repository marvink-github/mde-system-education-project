<?php

$data = json_decode(file_get_contents("php://input"), true);

$shiftId = $machineconn->real_escape_string(trim($data['shiftid'] ?? $_GET['shiftid'] ?? null));

if (!$shiftId) {
    http_response_code(400);
    echo json_encode(["message" => "shiftid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sqlCheck = "SELECT * FROM shift WHERE idShift = '$shiftId'";
$resultCheck = $machineconn->query($sqlCheck);

if ($resultCheck->num_rows == 0) {
    http_response_code(400);
    echo json_encode(["message" => "shift not found."], JSON_PRETTY_PRINT);
    exit();
}

$updateFields = [];

if (isset($data['starttime']) && $data['starttime'] !== "") {
    $updateFields[] = "starTtime = '" . $machineconn->real_escape_string(trim($data['starttime'])) . "'";
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
        echo json_encode([
            "message" => "shift information successfully patched.",
            "shiftid" => $shiftId,
            "starttime" => $updatedData['startTime'] ?? null,
            "endtime" => $updatedData['endTime'] ?? null,
            "machineid" => $updatedData['machine_idMachine'] ?? null
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "error updating shift data: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "no changes specified."], JSON_PRETTY_PRINT);
}
