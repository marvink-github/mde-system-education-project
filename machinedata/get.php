<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php'; 

// Optional API Key check
// if ($_GET['apiKey'] != "12398712397123987sadsdaihusadohji") {
//     http_response_code(403);
//     echo json_encode(["message" => "Forbidden"]);
//     exit();
// }

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$userId = $_GET['userid'] ?? null;
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 200;

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM machinedata WHERE 1=1"; // Start mit einer immer wahren Bedingung, damit z.B. from && to nicht mit WHERE sondern mit AND angehängt wird.

if ($from && $to) {
    $sql .= " AND timestamp BETWEEN '$from' AND '$to'";
} elseif ($from) {
    $sql .= " AND timestamp >= '$from'";
} elseif ($to) {
    $sql .= " AND timestamp <= '$to'";
}

if ($userId) {
    $sql .= " AND employee_idEmployee = '$userId'";
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbank query fehlgeschlagen: " . $machineconn->error]);
    exit();
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$machineconn->close();
?>
