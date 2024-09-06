<?php

require 'connection.php';
require 'functions.php';

header('Content-Type: application/json');

$apiKey = '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c';

if (!isset($_SERVER['HTTP_APIKEY']) || $_SERVER['HTTP_APIKEY'] != $apiKey) {
    http_response_code(403);
    echo json_encode(["message" => "Forbidden"]);
    exit();
}

try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];

    $routes = [
        'GET' => [
            'getShift' => '/shift/getShift.php',
            'getMachinedata' => '/machinedata/getMachinedata.php',
            'getSum' => '/machinedata/getSum.php',
            'getFirstOrder' => '/machinedata/getFirstOrder.php',
            'getLastOrder' => '/machinedata/getLastOrder.php',
            'getMachine' => '/machine/getMachine.php',
            'getLog' => '/log/getLog.php',
            'getDevice' => '/device/getDevice.php',
            'getBarcode' => '/barcode/getBarcode.php',
            'getAliveStatus' => '/alive/getAliveStatus.php',               
        ],
        'POST' => [
            'postMachinedata' => '/machinedata/postMachinedata.php',
            'postMachine' => '/machine/postMachine.php',
            'postDevice' => '/device/postDevice.php',
            'postShift' => '/shift/postShift.php',
        ],
        'PATCH' => [
            'patchMachinedata' => '/machinedata/patchMachinedata.php',
            'patchMachine' => '/machine/patchMachine.php',
            'patchBarcode' => '/barcode/patchBarcode.php',
            'patchShift' => '/shift/patchShift.php',
            'patchDevice' => '/device/patchDevice.php',
            'startBarcode' => '/barcode/startBarcode.php',
            'stopBarcode' => '/barcode/stopBarcode.php',
        ],
        'DELETE' => [
            'deleteMachinedata' => '/machinedata/deleteMachinedata.php',
            'deleteShift' => '/shift/deleteShift.php',
            'deleteMachine' => '/machine/deleteMachine.php',
            'deleteDevice' => '/device/deleteDevice.php',
        ],
    ];

    if (isset($routes[$requestMethod])) {
        foreach ($routes[$requestMethod] as $route => $file) {
            if (strpos($requestUri, $route) !== false) {
                require __DIR__ . $file;
                exit; 
            }
        }
    }

    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);

} catch (Exception $e) {
    http_response_code(500);
    logDB($machineconn, 'api', 'Internal server error: ' . $e->getMessage());
} finally {
    $machineconn->close();
}

