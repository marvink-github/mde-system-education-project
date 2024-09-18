<?php

// $designName = $_GET['design_name'] ?? 'default_design.iff';
// $designPath = __DIR__ . "/design/" . $designName;

// if (file_exists($designPath)) {
//     http_response_code(200);
//     echo json_encode([
//         'df_api' => 1,
//         'df_load_file' => "http://" . $_SERVER['SERVER_NAME'] . "/design/" . $designName
//     ]);
// } else {
//     http_response_code(400);
//     echo json_encode(['error' => 'Design-Datei nicht gefunden']);
// }

$apiKey = '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c';

$designName = $_GET['design_name'] ?? 'default_design.iff';

$url = "http://127.0.0.1/api/getdata.php?df_api=1&df_action=upload_design&design_name=" . urlencode($designName);

$ch = curl_init($url);

$headers = [
    "ApiKey: $apiKey",
    "Content-Type: application/json"
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

curl_close($ch);

echo $response;

