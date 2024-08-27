<?php

require_once __DIR__ . '/../connection.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$log_type = isset($_GET['log_type']) ? $_GET['log_type'] : null;
$limit = 200; 
$offset = ($page - 1) * $limit;

$sql = "SELECT idLog, timestamp, log_type, log_message FROM log";


if ($log_type) {
    $sql .= " WHERE log_type = '" . $log_type . "'";
}

$sql .= " ORDER BY timestamp DESC LIMIT $limit OFFSET $offset";

$result = $machineconn->query($sql);

$logs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode([
    'page' => $page,
    'log_type' => $log_type,
    'logs' => $logs,
]);

$machineconn->close();
?>
