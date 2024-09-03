<?php

require 'connection.php';

try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];

    switch ($requestMethod) {
        case 'GET':
            if (strpos($requestUri, 'getShift') !== false) {
                require __DIR__ . '/shift/getShift.php';
            } elseif (strpos($requestUri, 'getShiftActivityByUserId') !== false) {
                require __DIR__ . '/shift/getShiftActivityByUserId.php';
            } elseif (strpos($requestUri, 'getMachinedata') !== false) {
                require __DIR__ . '/machinedata/getMachinedata.php';
            } elseif (strpos($requestUri, 'getOrderCountById') !== false) {
                require __DIR__ . '/machinedata/getOrderCountById.php';
            } elseif (strpos($requestUri, 'getFirstOrderById') !== false) {
                require __DIR__ . '/machinedata/getFirstOrderById.php';
            } elseif (strpos($requestUri, 'getLastOrderById') !== false) {
                require __DIR__ . '/machinedata/getLastOrderById.php';
            } elseif (strpos($requestUri, 'getMachine') !== false) {
                require __DIR__ . '/machine/getMachine.php';
            } elseif (strpos($requestUri, 'getLog') !== false) {
                require __DIR__ . '/log/getLog.php';
            } elseif (strpos($requestUri, 'getDevice') !== false) {
                require __DIR__ . '/device/getDevice.php';
            } elseif (strpos($requestUri, 'getBarcodeSum') !== false) {
                require __DIR__ . '/barcode/getBarcodeSum.php';
            } elseif (strpos($requestUri, 'getAliveStatus') !== false) {
                require __DIR__ . '/alive/getAliveStatus.php';
            }
            break;

        case 'POST':
            if (strpos($requestUri, 'postMachinedata') !== false) {
                require __DIR__ . '/machinedata/postMachinedata.php';
            } elseif (strpos($requestUri, 'postNewMachine') !== false) {
                require __DIR__ . '/machine/postNewMachine.php';
            } elseif (strpos($requestUri, 'postNewDevice') !== false) {
                require __DIR__ . '/device/postNewDevice.php';
            }
            break;

        case 'PATCH':
            if (strpos($requestUri, 'patchMachineById') !== false) {
                require __DIR__ . '/machine/patchMachineById.php';
            } elseif (strpos($requestUri, 'patchBarcode') !== false) {
                require __DIR__ . '/barcode/patchBarcode.php';
            }
            break;

        case 'DELETE':
            if (strpos($requestUri, 'deleteShift') !== false) {
                require __DIR__ . '/shift/deleteShift.php';
            } elseif (strpos($requestUri, 'deleteMachineById') !== false) {
                require __DIR__ . '/machine/deleteMachineById.php';
            } elseif (strpos($requestUri, 'deleteDeviceById') !== false) {
                require __DIR__ . '/device/deleteDeviceById.php';
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(["message" => "Method Not Allowed"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    logDB($machineconn, 'catch', 'error: internal server error' . $e->getMessage());
} finally {
    $machineconn->close();
}
