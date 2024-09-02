<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../connection.php");

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/getDevice.php';
        break;

    case 'POST':
        require __DIR__ . '/postDevice.php';
        break;

    case 'PATCH':
        require __DIR__ . '/patchDevice.php';
        break;

    case 'DELETE':
        require __DIR__ . '/deleteDevice.php';
        break;

    default:
        http_response_code(400);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

