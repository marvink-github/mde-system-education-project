<?php 

header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php';

$machineId = $_GET['id'] ?? null;

$sql = "SELECT * FROM machine";
if ($machineId) {
    $sql .= " WHERE idMachine = '$machineId'";
}

$result = mysqli_query($machineconn, $sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen"]);
    exit();
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
mysqli_close($machineconn);

?>
