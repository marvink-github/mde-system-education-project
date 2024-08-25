<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php'; 

$input = json_decode(file_get_contents('php://input'), true);
$machineId = $input['id'] ?? null;
$terminal_id = isset($input['terminal_id']) ? $machineconn->real_escape_string($input['terminal_id']) : null;
$terminal_type = isset($input['terminal_type']) ? $machineconn->real_escape_string($input['terminal_type']) : null;
$employee_id = isset($input['employee_id']) ? $machineconn->real_escape_string($input['employee_id']) : null;

if (!$machineId) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: id."]);
    exit();
}

$machineId = (int)$machineId; 

$updateFields = [];
if ($terminal_id !== null) {
    $updateFields[] = "terminal_id = '$terminal_id'";
}
if ($terminal_type !== null) {
    $updateFields[] = "terminal_type = '$terminal_type'";
}
if (isset($input['employee_id'])) {
    if ($employee_id === null) {
        $updateFields[] = "employee_idEmployee = NULL";
    } else {
        $updateFields[] = "employee_idEmployee = '$employee_id'";
    }
}

if (empty($updateFields)) {
    http_response_code(400);
    echo json_encode(["message" => "Es gibt keine zu aktualisierenden Felder."]);
    exit();
}

$sql = "UPDATE machine SET " . implode(", ", $updateFields) . " WHERE idMachine = $machineId";
$result = $machineconn->query($sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankaktualisierung fehlgeschlagen: " . $machineconn->error]);
    exit();
}

echo json_encode(["message" => "Maschine erfolgreich aktualisiert"]);
$machineconn->close();
?>
