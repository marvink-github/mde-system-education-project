<?php

$designName = $machineconn->real_escape_string(trim($_GET['design_name'] ?? 'default_design.dfui'));
$designPath = __DIR__ . "../design/" . $designName;

if (file_exists($designPath)) {
    echo json_encode([
        'df_api' => 1,
        'df_load_file' => "http://" . $_SERVER['SERVER_NAME'] . "../design/" . $designName
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Design-Datei nicht gefunden']);
}

