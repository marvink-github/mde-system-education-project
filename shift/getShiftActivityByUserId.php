<?php

$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$machine_id = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));
$from = $machineconn->real_escape_string(trim($_GET['from'] ?? null));
$to = $machineconn->real_escape_string(trim($_GET['to'] ?? null));
$order = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null));

if (!$userid) {
    http_response_code(400);
    echo json_encode(["message" => "userid is required."], JSON_PRETTY_PRINT);
    exit();
}

$sql = "SELECT 
            shift.idShift AS shiftid, 
            shift.machine_idMachine AS machineid, 
            machinedata.userid, 
            machinedata.`order` AS orderid,
            shift.startTime, 
            shift.endTime, 
            SUM(machinedata.value) AS total_value
        FROM 
            shift 
        LEFT JOIN 
            machinedata 
        ON 
            shift.idShift = machinedata.shift_idShift 
        WHERE 
            machinedata.userid = '$userid'"; 

if ($machine_id) {
    $sql .= " AND shift.machine_idMachine = '$machine_id'"; 
}

if ($from) {
    $sql .= " AND shift.startTime >= '$from'";
}

if ($to) {
    $sql .= " AND (shift.endTime <= '$to' OR shift.endTime IS NULL)";
}

if ($order) {
    $sql .= " AND machinedata.`order` = '$order'";
}

$sql .= " GROUP BY machinedata.`order`, shift.idShift"; 
$sql .= " ORDER BY shift.startTime ASC";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "database query failed: " . $machineconn->error], JSON_PRETTY_PRINT);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'shiftid' => $row['shiftid'], 
        'machineid' => $row['machineid'],   
        'userid' => $row['userid'],   
        'orderid' => $row['orderid'], 
        'startTime' => $row['startTime'],
        'endTime' => $row['endTime'],
        'total_value' => $row['total_value'] 
    ];
}

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["message" => "no shifts found for this user."], JSON_PRETTY_PRINT);
} else {
    echo json_encode($data, JSON_PRETTY_PRINT);
}



