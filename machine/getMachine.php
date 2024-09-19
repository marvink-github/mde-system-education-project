<?php

$machine_id = $machineconn->real_escape_string(trim($_GET['machineid'] ?? null));
$userid = $machineconn->real_escape_string(trim($_GET['userid'] ?? null));
$state = $machineconn->real_escape_string(trim($_GET['state'] ?? null));
$orderid = $machineconn->real_escape_string(trim($_GET['orderid'] ?? null)); 

$sql = "SELECT * FROM machine WHERE 1=1"; 

if ($machine_id) {
    $sql .= " AND idMachine = '$machine_id'"; 
}

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

if ($state) {
    $sql .= " AND state = '$state'";
}

if ($orderid) {
    $sql .= " AND `order` = '$orderid'"; 
}

$result = $machineconn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        http_response_code(200);
        echo json_encode($data, JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        $errorMessage = "No machines found.";
        echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
        logDB($machineconn, 'warning', $errorMessage);
    }
} else {
    http_response_code(500); 
    $errorMessage = "Database query failed: " . $machineconn->error;
    echo json_encode(["message" => $errorMessage], JSON_PRETTY_PRINT);
    logDB($machineconn, 'error', $errorMessage);
}
