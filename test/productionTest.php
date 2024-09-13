<?php

function sendGetDataRequest($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function patchMachine($machineId, $userid, $orderid, $state, $d_entry_startstop, $d_entry_counter, $apiKey) {
    $url = "http://127.0.0.1/api/api/patchMachine";
    $data = [
        "machineid" => $machineId,
        "name" => "", 
        "userid" => $userid,
        "orderid" => $orderid,
        "state" => $state, 
        "d_entry_startstop" => $d_entry_startstop, 
        "d_entry_counter" => $d_entry_counter, 
        "device_idDevice" => 1 
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization:' . $apiKey 
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}


function startMachineAction($timestamp) {
    $url = "http://127.0.0.1/api/getdata.php?df_api=1&df_table=MDE&df_col_DT=$timestamp&df_col_T_ID=3533&df_col_T_Type=EVO%204.6%20FlexKey&df_col_D_StartStop=4&df_col_Identifier=start_m1";
    $response = sendGetDataRequest($url);
    echo "Maschine gestartet: " . $response . "\n";
}

function simulateProduction($timestamp, $cycles, $interval) {
    for ($i = 0; $i < $cycles; $i++) {
        $counterValue = $i + 1; 
        $url = "http://127.0.0.1/api/getdata.php?df_api=1&df_table=MDE&df_col_DT=$timestamp&df_col_T_ID=3533&df_col_T_Type=EVO%204.6%20FlexKey&df_col_D_Counter=$counterValue&df_col_Identifier=count&df_col_Value=1";
        $response = sendGetDataRequest($url);
        echo "Produktion Datensatz " . ($i + 1) . ": " . $response . "\n";
        
        sleep($interval); 
    }
}

function stopMachineAction($timestamp) {
    $url = "http://127.0.0.1/api/getdata.php?df_api=1&df_table=MDE&df_col_DT=$timestamp&df_col_T_ID=3533&df_col_T_Type=EVO%204.6%20FlexKey&df_col_D_StartStop=4&df_col_Identifier=stop_m1";
    $response = sendGetDataRequest($url);
    echo "Maschine gestoppt: " . $response . "\n";
}


$machineId = "1";
$userid = "dj198ma";
$orderid = "189023710";
$cycles = 10;
$interval = 5; 
$timestamp = date('Y-m-dTH:i:s'); 
$state = "";
$d_entry_startstop = 4;
$d_entry_counter = 3;
$apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";

echo "Start der Produktionssimulation...\n";

$patchResponse = patchMachine($machineId, $userid, $orderid, $state , $d_entry_startstop, $d_entry_counter, $apiKey);
echo "PatchMachine API Antwort: " . $patchResponse . "\n";

startMachineAction($timestamp);

simulateProduction($timestamp, $cycles, $interval);

stopMachineAction($timestamp);

echo "Produktionssimulation abgeschlossen.\n";
