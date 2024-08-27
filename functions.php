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
    // Extra für alle $_GET 
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

function startEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge, $timestamp) {
    $checkBadgeSql = "SELECT employee_idEmployee FROM authentication WHERE badge = '$badge'";
    $badgeResult = $machineconn->query($checkBadgeSql); 
    
    if ($badgeResult->num_rows == 0) {
        return ["success" => false, "message" => "Ungültiges Badge. Es ist nicht in der Authentifizierung vorhanden."];
    }
    
    $employeeId = $badgeResult->fetch_assoc()['employee_idEmployee'];

    $machine_id = getMachineId($machineconn, $terminal_id, $terminal_type);

    if (!$machine_id) {
        return ["success" => false, "message" => "Maschine nicht gefunden."];
    }

    $checkSql = "SELECT * FROM employee_has_machine WHERE machine_idMachine = $machine_id AND employee_idEmployee = $employeeId AND state = 'active'";
    $checkResult = $machineconn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        return ["success" => false, "message" => "Mitarbeiter ist bereits aktiv an dieser Maschine."];
    }

    $sql = "INSERT INTO employee_has_machine (machine_idMachine, employee_idEmployee, startTime, state) 
            VALUES ($machine_id, $employeeId, '$timestamp', 'active')";

    if (!$machineconn->query($sql)) {
        logDB($machineconn, 'ERROR', 'Fehler beim Starten der Sitzung: ' . $machineconn->error);
        return ["success" => false, "message" => "Fehler beim Starten der Sitzung: " . $machineconn->error];
    }

    updateMachineState($machineconn, $terminal_id, $terminal_type, 'active');

    return ["success" => true, "message" => "Mitarbeiter erfolgreich an der Maschine gestartet."];
}

// function startEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge) {
//     $employeeId = getBadgeId($machineconn, $badge);

//     $result = $machineconn->query("SELECT employee_idEmployee FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'");
    
//     if ($result->num_rows == 0) {
//         return false;
//     }

//     $sql = "UPDATE machine SET employee_idEmployee = $employeeId WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    
//     if (!$machineconn->query($sql)) {
//         logDB($machineconn, 'ERROR', 'Badge ist nicht registriert.');
//         return false;
//     }

//     updateMachineState($machineconn, $terminal_id, $terminal_type, 'active'); 
// }

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


function startOrder($machineconn, $badge, $timestamp, $barcode, $terminal_id, $terminal_type) {   
    $employeeId = getBadgeId($machineconn, $badge);

    if ($employeeId === false) {
        return ["success" => false, "message" => "Ungültiges Badge."];
    }

    $machine_id = getMachineId($machineconn, $terminal_id, $terminal_type);
    if (!$machine_id) {
        return ["success" => false, "message" => "Maschine nicht gefunden oder ungültiger Maschinentyp."];
    }

    $checkEmployeeMachineSql = "SELECT COUNT(*) FROM employee_has_machine 
                                WHERE employee_idEmployee = $employeeId 
                                AND machine_idMachine = $machine_id 
                                AND state = 'active'";
    $checkEmployeeMachineResult = $machineconn->query($checkEmployeeMachineSql);
    $isEmployeeOnMachine = $checkEmployeeMachineResult->fetch_row()[0];

    if ($isEmployeeOnMachine == 0) {
        return ["success" => false, "message" => "Der Mitarbeiter ist nicht an dieser Maschine angemeldet."];
    }

    $activeOrderCheckSql =  "SELECT COUNT(*) FROM `order` 
                             WHERE ordernumber = '$barcode' 
                             AND state = 'active' 
                             AND employee_idEmployee = $employeeId";
    $activeOrderCheckResult = $machineconn->query($activeOrderCheckSql);
    $activeOrderCount = $activeOrderCheckResult->fetch_row()[0];

    if ($activeOrderCount > 0) {
        return ["success" => false, "message" => "Der Auftrag mit dieser Nummer ist bereits aktiv."];
    }

    if (isOrderNumberExists($machineconn, $barcode)) {
        return ["success" => false, "message" => "Die Auftragsnummer ist bereits vorhanden, aber nicht aktiv."];
    }

    $sql = "INSERT INTO `order` (ordernumber, startTime, employee_idEmployee) 
            VALUES ('$barcode', '$timestamp', $employeeId)"; 

    if ($machineconn->query($sql) === TRUE) {
        return ["success" => true, "message" => "Auftrag erfolgreich gestartet."];
    } else {
        return ["success" => false, "message" => "Fehler beim Erstellen des Auftrags: " . $machineconn->error];
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


function getMachineId($machineconn, $terminal_id, $d_entry_start) {
    $machineSql = "SELECT m.idMachine FROM machine AS m 
                   JOIN device AS d ON m.device_idDevice = d.idDevice 
                   WHERE d.terminal_id = '$terminal_id' AND m.d_entry_startstop = '$d_entry_start'";
    $machineResult = $machineconn->query($machineSql);

    if ($machineResult->num_rows > 0) {
        return $machineResult->fetch_assoc()['idMachine'];
    } else {
        return false;
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


function handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_start) {
    // Überprüfen, ob die Maschine existiert und den richtigen Status hat
    $machineSql = "SELECT m.idMachine, m.state, d.idDevice FROM machine AS m 
                   JOIN device AS d ON m.device_idDevice = d.idDevice 
                   WHERE d.terminal_id = '$terminal_id' AND m.d_entry_startstop = '$d_entry_start'";
                   
    $machineResult = $machineconn->query($machineSql);

    if ($machineResult->num_rows === 0) {
        http_response_code(400);
        echo json_encode(["message" => "Maschine oder digitale Eingabe wurde nicht gefunden."], JSON_PRETTY_PRINT);
        return;
    }

    $machine = $machineResult->fetch_assoc();

    if ($machine['state'] === 'active') {
        http_response_code(400);
        echo json_encode(["message" => "Maschine ist bereits aktiv."], JSON_PRETTY_PRINT);
        return;
    }

    // Überprüfen, ob bereits eine aktive Schicht existiert
    $checkShiftSql = "SELECT idshift FROM shift 
                      WHERE machine_idMachine = " . $machine['idMachine'] . " AND endTime IS NULL";

    $shiftResult = $machineconn->query($checkShiftSql);

    if ($shiftResult->num_rows > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Eine aktive Schicht bei dieser Maschine existiert bereits."], JSON_PRETTY_PRINT);
        return;
    }

    // Update machine status
    $updateMachineSql = "UPDATE machine SET state = 'active' WHERE idMachine = " . $machine['idMachine'];
    
    if ($machineconn->query($updateMachineSql) === TRUE) {
        // Update device status
        $updateDeviceSql = "UPDATE device SET state = 'active' WHERE idDevice = " . $machine['idDevice'];
        
        if ($machineconn->query($updateDeviceSql) === TRUE) {
            // Insert into shift table
            $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', " . $machine['idMachine'] . ")";
            
            if ($machineconn->query($shiftSql) === TRUE) {
                $idshift = $machineconn->insert_id;
                http_response_code(200);
                echo json_encode([
                    "message" => "Maschine und Device erfolgreich gestartet.",
                    "idshift" => $idshift,
                    "machineId" => $machine['idMachine'],
                    "startTime" => $timestamp
                ], JSON_PRETTY_PRINT);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Fehler beim Starten der Schicht: " . $machineconn->error], JSON_PRETTY_PRINT);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Fehler beim Aktualisieren des Gerätestatus: " . $machineconn->error], JSON_PRETTY_PRINT);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Fehler beim Starten der Maschine: " . $machineconn->error], JSON_PRETTY_PRINT);
    }
}


function handleMachineData($machineconn, $timestamp, $value, $machine_id) {
    // Überprüfen, ob eine aktive Schicht existiert für diese Machine
    $currentShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $currentShiftResult = $machineconn->query($currentShiftSql);

    if ($currentShiftResult->num_rows > 0) {
        $shift = $currentShiftResult->fetch_assoc();
        $shift_id = $shift['idshift'];

        // Maschinendaten speichern
        $machineDataSql = "INSERT INTO machinedata (timestamp, value, shift_idshift) 
                           VALUES ('$timestamp', '$value', '$shift_id')";
        
        if ($machineconn->query($machineDataSql) === TRUE) {
            http_response_code(200);
            echo json_encode(["message" => "Maschinendaten erfolgreich gespeichert."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Fehler beim Speichern der Maschinendaten: " . $machineconn->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Keine aktive Schicht für diese Maschine gefunden."]);
    }
}


function handleStopAction($machineconn, $timestamp, $machine_id) {

    $updateMachineSql = "UPDATE machine SET state = 'stop' WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";

        if ($machineconn->query($updateShiftSql) === TRUE) {
            http_response_code(200);
            echo json_encode(["message" => "Maschine erfolgreich gestoppt und Schicht beendet."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Fehler beim Beenden der Schicht: " . $machineconn->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Fehler beim Stoppen der Maschine: " . $machineconn->error]);
    }
}


?>