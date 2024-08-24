<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php'; 

// if($_GET['apiKey']!="12398712397123987sadsdaihusadohji"){
//     http_response_code(403);
//     echo json_encode(["message" => "Forbidden"]);
//     exit();

// }

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$userId = $_GET['userid'] ?? null;
// $orderId = $_GET['order_id'] ?? null; Muss noch hinzugefügt werden in die Machinetabelle
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 200;

if (!$from || !$to) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Paramter: from and to."]);
    exit();
}

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM machinedata WHERE timestamp BETWEEN '$from' AND '$to'";

if ($userId) {
    $sql .= " AND employee_idEmployee = '$userId'";
}

// if ($orderId) {
//     $sql .= " AND order_id = '$orderId'";
// }

$sql .= " LIMIT $limit OFFSET $offset";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbank query fehlgeschlagen"]);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

mysqli_close($machineconn);

?>
