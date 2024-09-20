<?php

// echo 'df_api=1'; // Für clear Datensatzcache im Datafox Terminal
// exit;

require 'functions.php';
require 'connection.php'; 

if (!isset($_GET['df_api']) || $_GET['df_api'] != 1) {
    logDB($machineconn, 'Error', 'df_api ist ungleich 1');
};

logDB($machineconn, 'GET', $_GET); 

$table = $machineconn->real_escape_string(trim($_GET['df_table'] ?? null));
$type = $machineconn->real_escape_string(trim($_GET['df_type'] ?? null));

// Hier weitermachen!
if ($type === 'kvp') {
    $kv = $_GET['kv'] ?? null;

    if ($kv) {
        $kv_parts = explode(',', $kv);
        
        if ($kv_parts[0] === 'firmwareversion' && isset($kv_parts[1])) {
            $currentFirmware = $kv_parts[1];
            logDB($machineconn, 'Firmware', "Current Firmware: $currentFirmware");

            // terminal_id und type noch dynamisch machen (maybe über kv=device%2C35&kv=serialnumber%2C3533&kv=setup%20terminal4.6.projekt.mde.alpha.conni.aes%2CDBED685)
            $query = "UPDATE device SET firmware_version = '$currentFirmware' WHERE terminal_id = '3533' AND terminal_type = 'EVO 4.6 FlexKey'";
            $result = mysqli_query($machineconn, $query);

            if (!$result) {
                logDB($machineconn, 'Error', 'Failed to update firmware in database.');
            }
        }
    }
}

// GET /api/getdata.php?df_api=1&df_type=kvp&kv=firmwareversion%2C04.03.22.09.35

// GET /api/getdata.php?df_api=1&df_type=kvp&kv=firmwareversion%2C04.03.22.09.35&kv=board%2C50007%2C5.0a&kv=module%2C102026%2C1.0a%2C0.12&kv=module%2C6%2C1.4a%2C1&kv=module%2C8%2C1.3a%2C2&kv=module%2C5%2C1.2f%2C5&
// kv=module%2C50%2C1.1a%2C6&kv=module%2C103012%2C1.0a%2C6.2&kv=module%2C11%2C1.6b%2C7&kv=module%2C107%2C1.1a%2C8&kv=module%2C106001%2C1.1a%2C8.2&kv=module%2C106001%2C1.1a%2C8.3&kv=module%2C86%2C1.0a%2C9&kv=module
// %2C110009%2C1.0a%2C9.1&kv=module%2C110104%2C1.0b%2C9.2&kv=module%2C85%2C1.0a%2C20&kv=module%2C49001%2C1.0a%2C22&kv=device%2C35&kv=serialnumber%2C3533&kv=setup%20terminal4.6.projekt.mde.alpha.conni.aes%2CDBED685

if (empty($table)) exit;

switch ($table) {
    case 'Daten':           
        //$machineconn->real_escape_string(trim($_GET['df_col_DT'] ?? null));
        $badge = $machineconn->real_escape_string(trim($_GET['df_col_Badge'] ?? null)); 
        $action = $machineconn->real_escape_string(trim($_GET['df_col_Identifier'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_T_Type'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_User_ID'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_QR_Code'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_Inputtyp'] ?? null));
        //$machineconn->real_escape_string(trim($_GET['df_col_Projekt'] ?? null));
        
        // Display-Designer
        // if ($action === 'start' && $badge === '232C416A') {  
        //     updateDisplayDesign($machineconn, 'default_design.dfui');  
        // }

        if ($action === 'start' && $badge === '232C416A') {  
            // echo 'df_api=1&df_kvp=extinfo'; df_api=1&df_kvp=firmwareversion&df_kvp=serialnumber
            echo 'df_api=1&df_kvp=firmwareversion';
        }
        
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
        logDB($machineconn, 'Einstellung', $data);
        break;

    case 'Alive':                   
        $timestamp = $machineconn->real_escape_string(trim($_GET['df_col_Alive_DU'] ?? null));
        $terminal_id = $machineconn->real_escape_string(trim($_GET['df_col_T_ID'] ?? null));
        $terminal_type = $machineconn->real_escape_string(trim($_GET['df_col_T_Typ'] ?? null));
        $alive_count = $machineconn->real_escape_string(trim($_GET['df_col_Count'] ?? null));

        updateAliveStatus($machineconn, $timestamp, $terminal_id, $terminal_type); 

        $currentFirmware = getFirmwareFromDevice($machineconn, $terminal_id, $terminal_type);

        if (!empty($currentFirmware)) {
            $latestFirmware = getLatestFirmwareVersion();
        
            if (isUpdateRequired($currentFirmware, $latestFirmware)) {
                $download_url = "http://127.0.0.1/api/firmware/files/$latestFirmware"; 
                triggerFirmwareUpdate($download_url);
            } else {
                logDB($machineconn, 'Firmware', 'Latest firmware installed, no update required');
            }
        } else {
            logDB($machineconn, 'Firmware', 'No firmware version found in the database.');
        }

        // // Firmware-Update
        // if (!empty($currentFirmware)) {
        //     $latestFirmware = getLatestFirmwareVersion();
            
        //     if (isUpdateRequired($currentFirmware, $latestFirmware)) {
        //         $download_url = "http://127.0.0.1/api/firmware/files/$latestFirmware"; 
        //         triggerFirmwareUpdate($download_url);
        //     } else {
        //         logDB($machineconn, 'Firmware', 'Latest firmware installed, no update required');
        //     }
        // } else {
        //     logDB($machineconn, 'Firmware', 'No firmware version found.');
        // }
        break;

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
        logDB($machineconn, 'System', $data);
        break;

    default:
        exit;
}
 
$machineconn->close();
 
echo 'df_api=1';
