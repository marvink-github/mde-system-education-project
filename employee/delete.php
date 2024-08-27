<?php

require_once __DIR__ . '/../connection.php'; 

$idEmployee = $_GET['id'] ?? null;

if (!$idEmployee) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: id."]);
    exit();
}

$idEmployee = $machineconn->real_escape_string($idEmployee);

$sql = "SELECT * FROM employee WHERE idEmployee = '$idEmployee'";
$result = $machineconn->query($sql);

if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["message" => "Mitarbeiter mit dieser ID wurde nicht gefunden."]);
    $machineconn->close();
    exit();
}


$checkEmployeeHasMachineSql = "SELECT * FROM employee_has_machine WHERE employee_idEmployee = '$idEmployee'";
$checkResult = $machineconn->query($checkEmployeeHasMachineSql);

if ($checkResult->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["message" => "Mitarbeiter kann nicht gelöscht werden, da er bei einer Maschine angemeldet ist."]);
    $machineconn->close();
    exit();
}

$sqlDeleteEmployee = "DELETE FROM employee WHERE idEmployee = '$idEmployee'";
if ($machineconn->query($sqlDeleteEmployee) === TRUE) {
    http_response_code(200);
    echo json_encode(["message" => "Mitarbeiter erfolgreich gelöscht."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Fehler beim Löschen des Mitarbeiters: " . $machineconn->error]);
}

$machineconn->close();
?>
