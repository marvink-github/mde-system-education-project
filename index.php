<?php
// header("Content-Type: application/json");
// header("Access-Control-Allow-Origin: *");

require 'connection.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/shift/getShift.php';
        require __DIR__ . '/shift/getShiftActivityByUserId.php';
        require __DIR__ . '/machinedata/getMachinedata.php';
        require __DIR__ . '/machinedata/getOrderCountById.php';
        require __DIR__ . '/machinedata/getFirstOrderById.php';
        require __DIR__ . '/machinedata/getLastOrderById.php';
        require __DIR__ . '/machine/getMachine.php';
        require __DIR__ . '/log/getLog.php';
        require __DIR__ . '/device/getDevice.php';
        require __DIR__ . '/barcode/getBarcodeSum.php';
        require __DIR__ . '/alive/getAliveStatus.php';
        break;

    case 'POST':
        require __DIR__ . '/machinedata/postMachinedata.php';
        require __DIR__ . '/machine/postNewMachine.php';
        require __DIR__ . '/device/postNewDevice.php';
        break;

    case 'PATCH':
        require __DIR__ . '/machine/patchMachineById.php';
        require __DIR__ . '/barcode/patchBarcode.php';
        break;

    case 'DELETE':
        require __DIR__ . '/shift/deleteShift.php';
        require __DIR__ . '/machine/deleteMachineById.php';
        require __DIR__ . '/device/deleteDeviceById.php';
        break;

    default:
        http_response_code(400);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}
