<?php

// echo 'df_api=1'; // Für clear Datensatzcache im Datafox Terminal
// exit;

require 'functions.php';
require 'connection.php'; 

if (!isset($_GET['df_api']) || $_GET['df_api'] != 1) {
    logDB($machineconn, 'ERROR', 'df_api ist ungleich 1');
};

// logDB($machineconn, 'GET', $_GET); 

$table = $machineconn->real_escape_string($_GET['df_table'] ?? null);

if (empty($table)) exit;

switch ($table) {
    case 'Daten':
        $data = [             
            $machineconn->real_escape_string($_GET['df_col_DT'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Badge'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Identifier'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_ID'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_Type'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_User_ID'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_QR_Code'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Inputtyp'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Projekt'] ?? null)
        ];       
        logDB($machineconn, 'Daten', $data);
        break;

    case 'MDE':   
        $timestamp = $machineconn->real_escape_string($_GET['df_col_DT']); 
        $terminal_id = $machineconn->real_escape_string($_GET['df_col_T_ID']); 
        $terminal_type = $machineconn->real_escape_string($_GET['df_col_T_Type'] ?? null);
        $d_entry_count = $machineconn->real_escape_string($_GET['df_col_D_Counter'] ?? null); 
        $d_entry_startstop = $machineconn->real_escape_string($_GET['df_col_D_StartStop'] ?? null); 
        $action = $machineconn->real_escape_string($_GET['df_col_Identifier']); 
        $value = $machineconn->real_escape_string($_GET['df_col_Value'] ?? null); 
        $barcode = $machineconn->real_escape_string($_GET['df_col_Barcode'] ?? null);
        $userid = $machineconn->real_escape_string($_GET['df_col_Badge'] ?? null);

        switch($action){
            case 'start':        
                handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop);
                break;        
            
            case 'count':                 
                handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_count);
                break;
            
            case 'stop':                             
                handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop);                  
                break;
            case 'scanner':
                handleScannerAction($machineconn, $timestamp, $terminal_id, $terminal_type, $userid, $value, $barcode);
                break;

            default:
                exit;
        }
        break;

    case 'Einstellung':
        $data = [             
            $machineconn->real_escape_string($_GET['df_col_DU'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Wartung'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_ID'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_Typ'] ?? null),
        ];
        logDB($machineconn, 'Einstellung', $data);
        break;

    case 'Alive':
        $data = [            
            $machineconn->real_escape_string($_GET['df_col_Alive_DU'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_ID'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_Typ'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Count'] ?? null),
        ];
        //logDB($machineconn, 'Alive', $data);
        break;

    case 'System':
        $data = [              
            $machineconn->real_escape_string($_GET['df_col_DU'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_ID'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_T_Typ'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Typ'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Typ_Desc'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Gruppe'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Gruppe_Desc'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Grund'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Grund_Desc'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Detail_1'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Detail_2'] ?? null),
            $machineconn->real_escape_string($_GET['df_col_Detail_3'] ?? null),
        ];
        logDB($machineconn, 'System', $data);
        break;

    default:
        exit;
}
 
$machineconn->close();
 
echo 'df_api=1';
