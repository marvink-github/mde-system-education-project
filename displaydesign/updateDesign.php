<?php

$designName = trim($_GET['design_name'] ?? 'default_design.iff');
$designPath = __DIR__ . "/design/" . $designName;

if (file_exists($designPath)) {
    http_response_code(200);
    echo json_encode([
        'df_api' => 1,
        'df_load_file' => "http://" . $_SERVER['SERVER_NAME'] . "/design/" . $designName
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Design-Datei nicht gefunden']);
}
