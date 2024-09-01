<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("../connection.php");

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . '/getMachine.php';
        break;

    case 'POST':
        require __DIR__ . '/postNewMachine.php';
        require __DIR__ . '/postToMachine.php';
        break;

    case 'PATCH':
        require __DIR__ . '/patchMachine.php';
        break;

    case 'DELETE':
        require __DIR__ . '/deleteMachine.php';
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

