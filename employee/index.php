<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

switch ($requestMethod) {
    case 'GET':
        require __DIR__ . 'get.php';
        break;

    case 'POST':
        require __DIR__ . 'post.php';
        break;

    case 'PUT':
        require __DIR__ . 'patch.php';
        break;

    case 'DELETE':
        require __DIR__ . 'delete.php';
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}
?>
