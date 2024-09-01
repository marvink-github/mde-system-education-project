<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../connection.php");

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/getBarcodeSum.php';
        break;

    case 'POST':
        require __DIR__ . '/postOrderToBarcode.php';
        break;

    case 'PATCH':
        require __DIR__ . '/patchBarcode.php';
        break;

    case 'DELETE':
        require __DIR__ . '/deleteBarcode.php';
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

