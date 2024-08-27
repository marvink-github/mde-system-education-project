<?php
require_once("../connection.php"); 

$machine_id = $_GET['machineid'] ?? null;
$userid = $_GET['userid'] ?? null;
$state = $_GET['state'] ?? null;

$sql = "SELECT * FROM machine WHERE 1=1"; 

if ($machine_id) {
    $sql .= " AND idMachine = $machine_id";
}

if ($userid) {
    $sql .= " AND userid = '$userid'";
}

if ($state) {
    $sql .= " AND state = '$state'";
}

$result = $machineconn->query($sql);

if ($result) {
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    http_response_code(404); 
    echo json_encode(["message" => "Keine Maschinen gefunden."], JSON_PRETTY_PRINT);
}

$machineconn->close();
?>
