<?php

// echo 'df_api=1'; 
// exit;

require 'functions.php';
require 'connection.php'; 

if (!isset($_GET['df_api']) || $_GET['df_api'] != 1) {
    logDB($machineconn, 'error', 'df_api != 1');
};

logDB($machineconn, 'GET', $_GET); 

$table = $machineconn->real_escape_string(trim($_GET['df_table'] ?? null));

$type = $machineconn->real_escape_string(trim($_GET['df_type'] ?? null));

if ($type === 'kvp') {
    $kv = $_GET['kv'] ?? null;

    if ($kv) {
        $kv_parts = explode(',', $kv);
        
        if ($kv_parts[0] === 'firmwareversion' && isset($kv_parts[1])) {
            $currentFirmware = $kv_parts[1];
            logDB($machineconn, 'firmware', "Current firmware: $currentFirmware");

            $query = "UPDATE device SET firmware_version = '$currentFirmware' 
                      WHERE terminal_id = '3533' AND terminal_type = 'EVO 4.6 FlexKey'";
            $result = mysqli_query($machineconn, $query);

            if (!$result) {
                logDB($machineconn, 'error', 'Failed to update firmware in database.');
            }
        }
    }
}

switch ($table) {
    case 'Daten':           
        //$machineconn->real_escape_string(trim($_GET['df_col_DT'] ?? null));
        $badge = $machineconn->real_escape_string(trim($_GET['df_col_Badge'] ?? null)); 
        $action = $machineconn->real_escape_string(trim($_GET['df_col_Identifier'] ?? null));
        $order = $machineconn->real_escape_string(trim($_GET['df_col_Order'] ?? null)); 
        //$terminal_id = $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_T_Type'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_User_ID'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_QR_Code'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_Inputtyp'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_Projekt'] ?? null));
        
        // Mitarbeiter und Order in der Maschine über den Transponder eintragen
        if ($action === 'start') {  
            $machine_id = 1; 

            $updateQuery = "UPDATE machine SET userid = '$badge', `order` = '$order' WHERE idMachine = '$machine_id'";
            
            if ($machineconn->query($updateQuery) === TRUE) {
                logDB($machineconn, 'Daten', 'Werte erfolgreich aktualisiert.');
            } else {
                logDB($machineconn, 'Daten', 'Fehler beim Aktualisieren der Werte.');
            }
        } else {
            logDB($machineconn, 'Daten', 'Ungültige Eingabewerte oder fehlende Aktion.');
        }        
        
        // Display-Designer
        // if ($action === 'start' && $badge === '232C416A') {  
        //     updateDisplayDesign($machineconn, 'default_design.dfui');  
        // }
        
        break;

    case 'MDE':   
        $timestamp = $machineconn->real_escape_string(trim($_GET['df_col_DT'] ?? null)); 
        $terminal_id = $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null)); 
        $terminal_type = $machineconn->real_escape_string(trim($_GET['df_col_T_Type'] ?? null)); 
        $d_entry_counter = $machineconn->real_escape_string(trim($_GET['df_col_D_Counter'] ?? null)); 
        $d_entry_startstop = $machineconn->real_escape_string(trim($_GET['df_col_D_StartStop'] ?? null)); 
        $action = $machineconn->real_escape_string(trim($_GET['df_col_Identifier'] ?? null)); 
        $value = $machineconn->real_escape_string(trim($_GET['df_col_Value'] ?? null)); 
        $badge = $machineconn->real_escape_string(trim($_GET['df_col_Badge'] ?? null)); 

        switch($action){
            case 'start':        
                handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop);
                break;        
            
            case 'count':                 
                handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_counter);
                break;
            
            case 'stop':                             
                handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop);                  
                break;

            case 'scanner':
                handleScannerAction($machineconn, $timestamp, $terminal_id, $terminal_type, $value, $badge);
                break;
                
            default:
                exit;
        }
        break;

    case 'Einstellung':
        $data = [             
            $machineconn->real_escape_string(trim($_GET['df_col_DU'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Wartung'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_T_Typ'] ?? null)),
        ];
        logDB($machineconn, 'settings', $data);
        break;

    case 'Alive':                   
        $timestamp = $machineconn->real_escape_string(trim($_GET['df_col_Alive_DU'] ?? null));
        $terminal_id = $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null));
        $terminal_type = $machineconn->real_escape_string(trim($_GET['df_col_T_Typ'] ?? null));
        $alive_count = $machineconn->real_escape_string(trim($_GET['df_col_Count'] ?? null));

        // updateAliveStatus($machineconn, $timestamp, $terminal_id, $terminal_type); 

        $currentFirmware = getFirmwareFromDevice($machineconn, $terminal_id, $terminal_type);
        
        if (!empty($currentFirmware)){
            logDB($machineconn, 'firmware', 'Latest firmware installed, no update required');
        } else {
            echo 'df_api=1&df_kvp=firmwareversion';
            logDB($machineconn, 'firmware', 'No firmware version found, requesting now from device...');
        }

        // if (!empty($currentFirmware)) {
        //     $latestFirmware = getFirmwareFromFileserver('latest');            
        //     if (isUpdateRequired($currentFirmware, $latestFirmware)) {
        //         $download_url = "http://127.0.0.1/api/firmware/files/$latestFirmware";         
        //         echo "df_api=1&df_load_file=$download_url"; 
        //         logDB($machineconn, 'firmware', 'Firmware updating to latest...');
        //         exit;
        //     } else {
        //         logDB($machineconn, 'firmware', 'Latest firmware installed, no update required');
        //     }
        // } else {
        //     // echo 'df_api=1&df_kvp=extinfo'; // Für Alle Infos vom Terminal
        //     echo 'df_api=1&df_kvp=firmwareversion';
        //     logDB($machineconn, 'firmware', 'No firmware version found, requesting now from device...');
        //     exit;
        // }
        break;
        
        // Antwort auf df_api=1&df_kvp=firmwareversion
        // GET /api/getdata.php?df_api=1&df_type=kvp&kv=firmwareversion%2C04.03.22.10.35

        // Antwort auf df_api=1&df_kvp=extinfo
        // GET /api/getdata.php?df_api=1&df_type=kvp&kv=firmwareversion%2C04.03.22.10.35&kv=board%2C50007%2C5.0a&kv=module%2C102026%2C1.0a%2C0.12&kv=module%2C6%2C1.4a%2C1&kv=module%2C8%2C1.3a%2C2&kv=module%2C5%2C1.2f%2C5&
        // kv=module%2C50%2C1.1a%2C6&kv=module%2C103012%2C1.0a%2C6.2&kv=module%2C11%2C1.6b%2C7&kv=module%2C107%2C1.1a%2C8&kv=module%2C106001%2C1.1a%2C8.2&kv=module%2C106001%2C1.1a%2C8.3&kv=module%2C86%2C1.0a%2C9&kv=module
        // %2C110009%2C1.0a%2C9.1&kv=module%2C110104%2C1.0b%2C9.2&kv=module%2C85%2C1.0a%2C20&kv=module%2C49001%2C1.0a%2C22&kv=device%2C35&kv=serialnumber%2C3533&kv=setup%20terminal4.6.projekt.mde.alpha.conni.aes%2CDBED685B

    case 'System':
        $data = [              
            $machineconn->real_escape_string(trim($_GET['df_col_DU'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_T_Typ'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Typ'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Typ_Desc'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Gruppe'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Gruppe_Desc'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Grund'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Grund_Desc'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Detail_1'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Detail_2'] ?? null)),
            $machineconn->real_escape_string(trim($_GET['df_col_Detail_3'] ?? null)),
        ];
        logDB($machineconn, 'system', $data);
        break;

    default:
        exit;
}
 
$machineconn->close();
 
echo 'df_api=1';
