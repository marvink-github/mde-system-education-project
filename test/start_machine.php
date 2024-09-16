<?php

$machineId = $argv[1];
$userid = $argv[2];
$orderid = $argv[3];
$apiKey = $argv[4];
$cycles = (int)$argv[5];
$interval = (int)$argv[6];
$d_entry_startstop = $argv[7];
$d_entry_counter = $argv[8];
$terminal_id = $argv[9];

startMachine($machineId, $userid, $orderid, $apiKey, $cycles, $interval, $d_entry_startstop, $d_entry_counter, $terminal_id);

function patchMachine($machineId, $userid, $orderid, $apiKey) {
    $url = "http://127.0.0.1/api/api/patchMachine";
    $data = [
        "machineid" => $machineId,
        "userid" => $userid,
        "orderid" => $orderid,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'ApiKey: ' . $apiKey 
    ]);

    $response = curl_exec($ch);   
    curl_close($ch);
    
    return $response;
}

function startMachine($machineId, $userid, $orderid, $apiKey, $cycles, $interval, $d_entry_startstop, $d_entry_counter, $terminal_id) {
    include "../connection.php"; 
    include "../functions.php"; 

    echo "Starte Maschine {$machineId}...\n";
    
    $patchResponse = patchMachine($machineId, $userid, $orderid, $apiKey);
    echo "PatchMachine API Antwort für Maschine {$machineId}: " . $patchResponse . "\n";

    handleStartAction($machineconn, date('Y-m-d H:i:s'), $terminal_id, $d_entry_startstop);
    echo "Maschine {$machineId} gestartet\n";

    $startTime = time();
    while (time() - $startTime < $cycles * $interval) { 
        $value = rand(1, 10); 
        handleMachineData($machineconn, date('Y-m-d H:i:s'), $terminal_id, $value, $d_entry_counter);
        echo "Produktion Datensatz für 'Maschine {$machineId}': count = $value\n";
        sleep($interval); 
    }

    handleStopAction($machineconn, date('Y-m-d H:i:s'), $terminal_id, $d_entry_startstop);
    echo "Maschine {$machineId} gestoppt\n";
}
