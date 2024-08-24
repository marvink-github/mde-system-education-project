<?php

header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php';

$input = json_decode(file_get_contents('php://input'), true);

$terminal_id = $input['terminal_id'] ?? null;
$terminal_type = $input['terminal_type'] ?? null;

if (!$terminal_id || !$terminal_type) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: Maschinen id und type."]);
    exit();
}

$checkSql = "SELECT * FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
$checkResult = mysqli_query($machineconn, $checkSql);

if (mysqli_num_rows($checkResult) > 0) {
    http_response_code(409); 
    echo json_encode(["message" => "Die Maschine ist bereits vorhanden."]);
    mysqli_close($machineconn);
    exit();
}

$sql = "INSERT INTO machine (terminal_id, terminal_type) VALUES ('$terminal_id', '$terminal_type')";
$result = mysqli_query($machineconn, $sql);

if (!$result) {
    http_response_code(400);
    echo json_encode(["message" => "Datenbankeintrag fehlgeschlagen"]);
    exit();
}

echo json_encode(["message" => "Maschine erfolgreich hinzugefügt"]);
mysqli_close($machineconn);

?>

