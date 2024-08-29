<?php

// echo 'df_api=1'; // Für clear Datensatzcache im Datafox Terminal
// exit;

require 'functions.php';
require 'connection.php'; 

if (!isset($_GET['df_api']) || $_GET['df_api'] != 1) {
    logDB($machineconn, 'ERROR', 'df_api ist ungleich 1');
};

// logDB($machineconn, 'GET', $_GET); 

$table = $_GET['df_table'] ?? null;

if (empty($table)) exit;

switch ($table) {
    case 'Daten':
        $data = [             
            $_GET['df_col_DT'] ?? null,
            $_GET['df_col_Badge'] ?? null,
            $_GET['df_col_Identifier'] ?? null,
            $_GET['df_col_T_ID'] ?? null,
            $_GET['df_col_T_Type'] ?? null,
            $_GET['df_col_User_ID'] ?? null,
            $_GET['df_col_QR_Code'] ?? null,
            $_GET['df_col_Inputtyp'] ?? null,
            $_GET ['df_col_Projekt'] ?? null
        ];       
        logDB($machineconn, 'Daten', $data);
        break;

    case 'MDE':   
        $timestamp = $_GET['df_col_DT']; 
        $terminal_id = $_GET['df_col_T_ID']; 
        $terminal_type = $_GET['df_col_T_Type'] ?? null;
        $d_entry_count = $_GET['df_col_D_Counter'] ?? null; 
        $d_entry_startstop = $_GET['df_col_D_StartStop'] ?? null; 
        $action = $_GET['df_col_Identifier']; 
        $value = $_GET['df_col_Value'] ?? null;   

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

            default:
                exit;
        }
        break;

    case 'Einstellung':
        $data = [             
            $_GET['df_col_DU'] ?? null,
            $_GET['df_col_Wartung'] ?? null,
            $_GET['df_col_T_ID'] ?? null,
            $_GET['df_col_T_Typ'] ?? null,
        ];
        logDB($machineconn, 'Einstellung', $data);
        break;

    case 'Alive':
        $data = [            
            $_GET['df_col_Alive_DU'] ?? null,
            $_GET['df_col_T_ID'] ?? null,
            $_GET['df_col_T_Typ'] ?? null,
            $_GET['df_col_Count'] ?? null,
        ];
        //logDB($machineconn, 'Alive', $data);
        break;

    case 'System':
        $data = [              
            $_GET['df_col_DU'] ?? null,
            $_GET['df_col_T_ID'] ?? null,
            $_GET['df_col_T_Typ'] ?? null,
            $_GET['df_col_Typ'] ?? null,
            $_GET['df_col_Typ_Desc'] ?? null,
            $_GET['df_col_Gruppe'] ?? null,
            $_GET['df_col_Gruppe_Desc'] ?? null,
            $_GET['df_col_Grund'] ?? null,
            $_GET['df_col_Grund_Desc'] ?? null,
            $_GET['df_col_Detail_1'] ?? null,
            $_GET['df_col_Detail_2'] ?? null,
            $_GET['df_col_Detail_3'] ?? null,
        ];
        logDB($machineconn, 'System', $data);
        break;

    default:
        exit;
}
 
$machineconn->close();
 
echo 'df_api=1';

