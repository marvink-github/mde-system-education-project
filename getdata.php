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
                registryBadge($machineconn, $userid, $badge);
                break;
        
            case 'delete':
                deleteBadge($machineconn, $badge);
                break;
        
            case 'start': 
                startEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge, $timestamp);
                break;
        
            case 'end':                
                stopEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge, $timestamp);
                break; 
                
            case 'start_order':
                // Starten (Nicht notwendig)
                // ordernumber der order Tabelle hinzufügen 
                // startTime setzen und state ist default auf 'start'
                // idOrder mit machinedata verküpfen
                // brauchen erstmal nur barcode und timestamp
                startOrder($machineconn, $badge, $timestamp, $barcode, $terminal_id, $terminal_type);
                break;
            
            case 'finish_order':
                // Beenden (Nicht notwendig)
                // endTime setzen und state auf 'finished' setzen
                // Wenn order abgeschlossen ist, soll die id aus den zukünftige Machinedata entfernt werden.
                // barcode vergleichen, wenn vorhanden dann auftrag abschließen                
                // Maybe stückzahl hinzufügen, man kann auftrag erst abschließen wenn diese erfüllt worden sind.
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
        $d_entry_count = $_GET['df_col_D_Entry_Counter']; // Umbennen in df_col_D_Entry_Count
        $d_entry_start = $_GET['df_col_D_Entry_StartStop']; 
        $action = $_GET['df_col_Identifier'];
        $value = $_GET['df_col_Value'];     

        switch($action){
            case 'start':
                // Maschine wurde gestartet, Maschinendatensatz mit 'start' in value
                // shift beginnt und in tabelle shift dann df_col_DT timestamp für startTime
                // dig_entry = 3 prüfen und dann in shift die jeweilige idMachine angeben
                // die aktuelle shift id mit in die kommenden maschinendatensätze schreiben  
                handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_start);              
                break; 
            case 'startcount': // Wird evtl. immer 0 sein, wenn ja dann stopcount nehmen!
                // Produktion wurde begonnen, Maschinendatensatz mit '1' in value
                break;
            case 'stop':
                // Maschine wurde gestoppt, Maschinendatensatz mit 'stop' in value
                // shift beenden und in tabelle shift dann df_col_DT timestamp für endTime
                
        }


        // $ids = getMachineAndEmployeeId($machineconn, $terminal_id , $terminal_type);  
        // $machineId = $ids['idMachine'];
        // $employeeId = $ids['employee_idEmployee'];

        // insertMachineData($machineconn, $timestamp, $digital_entry, $value, $machineId, $employeeId); 
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
