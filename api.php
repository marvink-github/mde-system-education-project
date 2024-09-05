<?php

require 'connection.php';
require 'functions.php';

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

    http_response_code(400);
    echo json_encode(["message" => "method not allowed"]);

} catch (Exception $e) {
    http_response_code(500);
    logDB($machineconn, 'catch', 'internal server error: ' . $e->getMessage());
} finally {
    $machineconn->close();
}

