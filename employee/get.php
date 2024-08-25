<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php';

$employeeid = $_GET['employeeid'] ?? null;
$userId = $_GET['userid'] ?? null;

$sql = "SELECT * FROM employee WHERE 1=1"; // WHERE 1=1 ermöglicht einfaches Hinzufügen weiterer Bedingungen

if ($employeeid) {
    $employeeid = $machineconn->real_escape_string($employeeid);
    $sql .= " AND idEmployee = '$employeeid'";
}

if ($userId) {
    $userId = $machineconn->real_escape_string($userId);
    $sql .= " AND userid = '$userId'";
}

$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankabfrage fehlgeschlagen: " . $machineconn->error]);
    exit();
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Bei der Rückgabe prüfen, ob userid oder idEmployee gewünscht ist
    if ($employeeid) {
        // Wenn employeeid bereitgestellt wurde, return userid
        $data[] = ['userid' => $row['userid']];
    } elseif ($userId) {
        // Wenn userid bereitgestellt wurde, return idEmployee
        $data[] = ['idEmployee' => $row['idEmployee']];
    } else {
        // Ansonsten alle Daten zurückgeben
        $data[] = $row;
    }
}

echo json_encode($data);
$machineconn->close();
?>
