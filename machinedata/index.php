<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../connection.php");

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/getMachinedata.php';
        require __DIR__ . '/getFirstOrder.php';
        require __DIR__ . '/getLastOrder.php';
        require __DIR__ . '/getOrderCount.php';
        break;

    case 'POST':
        require __DIR__ . '/postMachinedata.php';
        break;

    case 'PATCH':
        require __DIR__ . '/patch.php';
        break;

    case 'DELETE':
        require __DIR__ . '/delete.php';
        break;

    default:
        http_response_code(400);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

