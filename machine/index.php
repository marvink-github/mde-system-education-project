<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../connection.php");

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/getMachineById.php';
        break;

    case 'POST':
        require __DIR__ . '/postNewMachine.php';
        break;

    case 'PATCH':
        require __DIR__ . '/patchMachineById.php';
        break;

    case 'DELETE':
        require __DIR__ . '/deleteMachineById.php';
        break;

    default:
        http_response_code(400);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

