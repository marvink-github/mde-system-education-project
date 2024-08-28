<?php

function sendResponse($responseParams = [], $statuscode = 200) { 
    http_response_code($statuscode);

    $response = ['df_api' => 1];

    if(!empty($responseParams)){
        $response = array_merge($response, $responseParams);    
    }  

    $responseArray = [];
    foreach ($response as $key => $value) {
        $responseArray[] = $key . '=' . $value;
    }    

    $responseString = implode('&', $responseArray);

    echo $responseString;    
}


function logDB($machineconn, $logType, $logMessage) {
    if (is_array($logMessage)) {
        $logMessage = http_build_query($logMessage);
    }
    
    $sql = "INSERT INTO log (log_type, log_message) VALUES ('$logType', '$logMessage')";
    $result = $machineconn->query($sql);
    
    if (!$result) {
        error_log("Fehler bei der Log-Anweisung: " . mysqli_error($machineconn));
    }
}


function registryBadge($machineconn, $userid, $badge) {
    $employeeId = getEmployeeId($machineconn, $userid);
    
    if ($employeeId === false) {
        $employeeId = insertEmployee($machineconn, $userid);
    }

    $existingEmployeeId = getBadgeId($machineconn, $badge);

    if ($existingEmployeeId === false) {
        insertBadge($machineconn, $badge, $employeeId);
    } else if ($existingEmployeeId != $employeeId) {
        updateBadge($machineconn, $badge, $employeeId);
        logDB($machineconn, 'INFO', "Das Badge $badge wurde einem anderen Mitarbeiter zugewiesen.");
    } else {
        logDB($machineconn, 'ERROR', "Badge $badge ist bereits zugewiesen.");
    }
}


function getEmployeeId($machineconn, $userid) {
    $sql = $machineconn->query("SELECT idEmployee FROM employee WHERE userid = '$userid'");

    if ($sql->num_rows == 0) {
        return false; 
    }

    $employeeRow = $sql->fetch_assoc();
    $employeeId = $employeeRow['idEmployee'];

    return $employeeId;
}


function insertEmployee($machineconn, $userid) {
    $sql = "INSERT INTO employee (userid) VALUES ('$userid')";
    if (!$machineconn->query($sql)) {
        return false;
    }
    return $machineconn->insert_id; // insert_id -> Wert des zuletzt automatisch generierten Primärschlüssels
}


function getBadgeId($machineconn, $badge) {
    $sql = $machineconn->query("SELECT employee_idEmployee FROM authentication WHERE badge = '$badge'");

    if (!$sql) {
        return false;
    }

    if ($sql->num_rows == 0) {
        return false;
    }

    $row = $sql->fetch_assoc();
    return $row['employee_idEmployee'];
}


function insertBadge($machineconn, $badge, $employeeId) {
    $sql = "INSERT INTO authentication (badge, employee_idEmployee) VALUES ('$badge', $employeeId)";
    if (!$machineconn->query($sql)) {
        return false;
    }
}


function updateBadge($machineconn, $badge, $employeeId) {
    $sql = "UPDATE authentication SET employee_idEmployee = $employeeId WHERE badge = '$badge'";
    
    if (!$machineconn->query($sql)) {
        exit("Fehler beim Aktualisieren des Badges: " . $machineconn->error);
    }
}


function deleteBadge($machineconn, $badge) {
    $sql = "DELETE FROM authentication WHERE badge = '$badge'";
    
    if (!$machineconn->query($sql)) {
        exit("Fehler beim Löschen des Badges: " . $machineconn->error);
    }
}


function stopEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge, $timestamp) {
    $employeeId = getBadgeId($machineconn, $badge);

    if ($employeeId === false) {
        return ["success" => false, "message" => "Ungültiger Badge."];
    }

    $checkSql = "SELECT * FROM employee_has_machine 
                 WHERE machine_idMachine = (SELECT idMachine FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type') 
                 AND employee_idEmployee = $employeeId 
                 AND state = 'active'";
    
    $checkResult = $machineconn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        return ["success" => false, "message" => "Mitarbeiter ist nicht aktiv an dieser Maschine."];
    }

    $sql = "UPDATE employee_has_machine 
            SET state = 'end', endTime = '$timestamp' 
            WHERE machine_idMachine = (SELECT idMachine FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type') 
            AND employee_idEmployee = $employeeId 
            AND state = 'active'";
    
    if (!$machineconn->query($sql)) {
        return ["success" => false, "message" => "Fehler beim Aktualisieren der Sitzung: " . $machineconn->error];
    }

    updateMachineState($machineconn, $terminal_id, $terminal_type, 'inactive');

    return ["success" => true, "message" => "Mitarbeiter erfolgreich von der Maschine abgemeldet."];
}


function insertMachineData($machineconn, $timestamp, $digital_entry, $impulse, $machineId, $employeeId) {
    $sql = "INSERT INTO machinedata (timestamp, digital, value, employee_idEmployee, machine_idMachine)
              VALUES ('$timestamp', '$digital_entry', '$impulse', $employeeId, $machineId)";   

    if (!$machineconn->query($sql)) {
        exit("Fehler beim Einfügen der Maschinendaten: " . $machineconn->error);
    }
}


function finishOrder($machineconn, $badge, $timestamp) {
    $employeeId = getBadgeId($machineconn, $badge);      

    $selectSql = "SELECT idorder FROM `order` 
                  WHERE employee_idEmployee = $employeeId 
                  ORDER BY idorder DESC LIMIT 1";
    
    $selectResult = $machineconn->query($selectSql);

    if ($selectResult && $selectResult->num_rows > 0) {
        $lastOrder = $selectResult->fetch_assoc();
        $lastOrderId = $lastOrder['idorder'];
        
        $updateSql = "UPDATE `order` 
                      SET endTime = '$timestamp', state = 'end' 
                      WHERE idorder = $lastOrderId";
        
        if ($machineconn->query($updateSql) === TRUE) {
            return ["success" => true, "message" => "Auftrag erfolgreich beendet."];
        } else {
            return ["success" => false, "message" => "Fehler beim Beenden des Auftrags: " . $machineconn->error];
        }
    } else {
        return ["success" => false, "message" => "Kein aktiver Auftrag gefunden."];
    }
}


function isEmployeeLoggedIn($machineconn, $employeeId) {    
    $employeeId = intval($employeeId); 
    
    $sql = "SELECT COUNT(*) FROM employee_has_machine WHERE employee_idEmployee = $employeeId AND state = 'start'";
    
    $result = $machineconn->query($sql);
   
    if ($result === false) {
        error_log("Fehler bei der SQL-Abfrage: " . $machineconn->error);
        return false; 
    }
    
    $row = $result->fetch_row();
    
    return $row[0] > 0; 
}


function isOrderNumberExists($machineconn, $barcode) {   
    $sql = "SELECT COUNT(*) FROM `order` WHERE ordernumber = '$barcode'";
    $result = $machineconn->query($sql);
    $row = $result->fetch_row();
    return $row[0] > 0; 
}


function getMachineIdByDStartStop($machineconn, $terminal_id, $d_entry_startstop) {
    $machineSql = "SELECT m.idMachine FROM machine AS m 
                   JOIN device AS d ON m.device_idDevice = d.idDevice 
                   WHERE d.terminal_id = '$terminal_id' AND m.d_entry_startstop = '$d_entry_startstop'";

    $machineResult = $machineconn->query($machineSql);

    if ($machineResult->num_rows > 0) {
        $machine = $machineResult->fetch_assoc();
        return $machine['idMachine'];
    } else {
        logDB($machineconn, 'getMachineIdByDStartStop', 'Fehler: Maschine nicht gefunden für Terminal-ID: ' . $terminal_id . ' und d_entry_startstop: ' . $d_entry_startstop);
        return null; 
    }
}



function getMachineIdByDCount($machineconn, $terminal_id, $d_entry_count) {
    $machineSql = "SELECT m.idMachine FROM machine AS m 
                   JOIN device AS d ON m.device_idDevice = d.idDevice 
                   WHERE d.terminal_id = '$terminal_id' AND m.d_entry_counter = $d_entry_count";  

    $machineResult = $machineconn->query($machineSql);

    if ($machineResult->num_rows > 0) {
        $machine = $machineResult->fetch_assoc();
        return $machine['idMachine'];
    } else {
        return null; 
    }
}


function updateMachineState($machineconn, $terminal_id, $terminal_type, $action) {
    $sql = "UPDATE machine SET state = '$action' WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";

    if (!$machineconn->query($sql)) {
        return false;
    }
    return true;
}


function getMachineAndEmployeeId($machineconn, $terminal_id, $terminal_type) {
    $machineResult = $machineconn->query("SELECT idMachine FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'");
    
    if ($machineResult->num_rows == 0) {
        return false;
    }

    $machineRow = $machineResult->fetch_assoc();
    $machineId = $machineRow['idMachine'];

    $employeeResult = $machineconn->query("SELECT employee_idEmployee FROM employee_has_machine WHERE machine_idMachine = '$machineId' AND state = 'active'");

    if ($employeeResult->num_rows == 0) {
        return [
            'idMachine' => $machineId,
            'employee_idEmployee' => null 
        ];
    }

    $employeeRow = $employeeResult->fetch_assoc();
    return [
        'idMachine' => $machineId,
        'employee_idEmployee' => $employeeRow['employee_idEmployee']
    ];
}


function handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByDStartStop($machineconn, $terminal_id, $d_entry_startstop);

    if ($machine_id === null) {
        logDB($machineconn, 'start', 'Fehler: Maschine oder digitale Eingabe wurde nicht gefunden.');
        return; 
    }

    $machineStateSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $machineStateResult = $machineconn->query($machineStateSql);
    $machine = $machineStateResult->fetch_assoc();

    if ($machine['state'] === 'start') {
        logDB($machineconn, 'start', 'Fehler: Maschine ist bereits aktiv.');
        return; 
    }

    $checkShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $shiftResult = $machineconn->query($checkShiftSql);

    if ($shiftResult->num_rows > 0) {
        logDB($machineconn, 'start', 'Fehler: Eine aktive Schicht bei dieser Maschine existiert bereits.');
        return; 
    }

    $updateMachineSql = "UPDATE machine SET state = 'start' WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', $machine_id)";

        if ($machineconn->query($shiftSql) === TRUE) {
            logDB($machineconn, 'start', 'Erfolg: Maschine und Schicht erfolgreich gestartet.');
            return true;
        } else {
            logDB($machineconn, 'start', 'Fehler beim Starten der Schicht: ' . $machineconn->error);
        }
    } else {
        logDB($machineconn, 'start', 'Fehler beim Starten der Maschine: ' . $machineconn->error);
    }
}


function handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_count) {
    $machine_id = getMachineIdByDCount($machineconn, $terminal_id, $d_entry_count);

    if (!$machine_id) {
        logDB($machineconn, 'count', 'Fehler: Maschine wurde nicht gefunden.');
        return;
    }

    $currentShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $currentShiftResult = $machineconn->query($currentShiftSql);

    if ($currentShiftResult->num_rows > 0) {
        $shift = $currentShiftResult->fetch_assoc();
        $shift_id = $shift['idshift'];

        $countSql = "SELECT d_entry_counter, userid FROM machine WHERE idMachine = $machine_id";
        $countResult = $machineconn->query($countSql);

        if ($countResult->num_rows > 0) {
            $machine = $countResult->fetch_assoc();

            if ($machine['d_entry_counter'] != $d_entry_count) {
                logDB($machineconn, 'count', 'Fehler: d_entry_count stimmt nicht mit der Maschine überein.');
                return;
            }

            $userid = $machine['userid']; 
            $machineDataSql = "INSERT INTO machinedata (timestamp, value, shift_idshift, userid) 
                               VALUES ('$timestamp', '$value', '$shift_id', '$userid')";
            
            if ($machineconn->query($machineDataSql) === TRUE) {
                logDB($machineconn, 'count', 'Erfolg: Maschinendaten erfolgreich gespeichert.');
            } else {
                logDB($machineconn, 'count', 'Fehler beim Speichern der Maschinendaten.');
            }
        } else {
            logDB($machineconn, 'count', 'Fehler: Maschine wurde nicht gefunden.');
        }
    } else {
        logDB($machineconn, 'count', 'Fehler: Keine aktive Schicht an diese Maschine gefunden.');
    }
}


function handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByDStartStop($machineconn, $terminal_id, $d_entry_startstop);

    if (!$machine_id) {
        logDB($machineconn, 'stop', 'Fehler: Maschine wurde nicht gefunden.');
        return;
    }

    $updateMachineSql = "UPDATE machine SET state = 'stop', userid = NULL WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";

        if ($machineconn->query($updateShiftSql) === TRUE) {
            logDB($machineconn, 'stop', 'Erfolg: Maschine erfolgreich gestoppt und Schicht beendet.');
        } else {
            logDB($machineconn, 'stop', 'Fehler beim Beenden der Schicht: ' . $machineconn->error);
        }
    } else {
        logDB($machineconn, 'stop', 'Fehler beim Stoppen der Maschine: ' . $machineconn->error);
    }
}


?>