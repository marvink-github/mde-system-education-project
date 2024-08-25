<?php

require 'functions.php';
require 'connection.php';
    
// echo 'df_api=1'; // Für clear Datensatzcache im Datafox Terminal
// exit;

if (!isset($_GET['df_api']) || $_GET['df_api'] != 1) {
    logDB($machineconn, 'ERROR', 'df_api ist ungleich 1');
    exit();
};

logDB($machineconn, 'GET', $_GET); 

$table = $_GET['df_table'] ?? null;

if (empty($table)) exit;

switch ($table) {
    case 'Daten':
        $timestamp = $_GET['df_col_DT']; 
        $badge = $_GET['df_col_Badge']; 
        $action = $_GET['df_col_Identifier'];
        $terminal_id = $_GET['df_col_T_ID'];
        $terminal_type = $_GET['df_col_T_Type'];
        $userid = $_GET['df_col_User_ID'] ?? null;
        $barcode = $_GET['df_col_QR_Code'] ?? null; // Muss noch im Terminal in barcode umbenannt werden!!! df_col_Barcode
        // $inputtype = $_GET['df_col_Inputtyp']; 
        // $projekt = $_GET ['df_col_Projekt'];           
              
        switch ($action) {
            case 'insert':
                $employeeId = getEmployeeId($machineconn, $userid);
                registryBadge($machineconn, $userid, $badge, $employeeId);
                break;
        
            case 'delete':
                deleteBadge($machineconn, $badge);
                break;
        
            case 'start': 
                startEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge);
                break;
        
            case 'end':                
                stopEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge);
                break;
                
            case 'start_order':
                startOrder($machineconn, $badge, $timestamp, $barcode);
                break;
            
            case 'finish_order':
                finishOrder($machineconn, $badge, $timestamp);
                break;

            default:
                exit;
        }
        break;

    case 'MDE':   
        $timestamp = $_GET['df_col_DT']; 
        $terminal_id = $_GET['df_col_T_ID']; 
        $terminal_type = $_GET['df_col_T_Type']; 
        $digital_entry = $_GET['df_col_D_Entry'];
        $action = $_GET['df_col_Identifier'];
        $value = $_GET['df_col_Value'];     

        $ids = getMachineAndEmployeeId($machineconn, $terminal_id , $terminal_type);  
        $machineId = $ids['idMachine'];
        $employeeId = $ids['employee_idEmployee'];

        insertMachineData($machineconn, $timestamp, $digital_entry, $value, $machineId, $employeeId); 
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
        logDB($machineconn, 'Alive', $data);
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

?>
