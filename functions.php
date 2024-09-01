<?php

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
            logDB($machineconn, 'start', 'success: Maschine und Schicht gestartet.');
        } else {
            logDB($machineconn, 'start', 'Fehler beim Starten der Schicht: ' . $machineconn->error);
            return; 
        }
    } else {
        logDB($machineconn, 'start', 'Fehler beim Starten der Maschine: ' . $machineconn->error);
        return; 
    }
}

function handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_count) {
    $machine_id = getMachineIdByDCount($machineconn, $terminal_id, $d_entry_count);

    if (!$machine_id) {
        logDB($machineconn, 'count', 'Fehler: Maschine wurde nicht gefunden. machine_id: ' . $machine_id);
        return; 
    }

    $stateCheckSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $stateCheckResult = $machineconn->query($stateCheckSql);

    if ($stateCheckResult->num_rows > 0) {
        $machine = $stateCheckResult->fetch_assoc();

        if ($machine['state'] === 'stop') {
            logDB($machineconn, 'count', 'Fehler: Maschine ist nicht aktiv (State: stop). idMachine: ' . $machine_id);
            return;
        }
    } else {
        logDB($machineconn, 'count', 'Fehler: Maschine wurde nicht gefunden.');
        return;
    }

    $currentShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $currentShiftResult = $machineconn->query($currentShiftSql);

    if ($currentShiftResult->num_rows > 0) {
        $shift = $currentShiftResult->fetch_assoc();
        $shift_id = $shift['idshift'];

        $countSql = "SELECT d_entry_counter, userid, `order` FROM machine WHERE idMachine = $machine_id"; 
        $countResult = $machineconn->query($countSql);

        if ($countResult->num_rows > 0) {
            $machine = $countResult->fetch_assoc();

            if ($machine['d_entry_counter'] != $d_entry_count) {
                logDB($machineconn, 'count', 'Fehler: d_entry_count stimmt nicht mit der Maschine überein.');
                return; 
            }

            $userid = $machine['userid'] ?? 'anonym'; 
            $orderid = $machine['order'] ?? null;

            $machineDataSql = "INSERT INTO machinedata (timestamp, value, shift_idshift, userid, `order`)
                               VALUES ('$timestamp', '$value', '$shift_id', '$userid', '$orderid')";
            
            if ($machineconn->query($machineDataSql) === TRUE) {
                logDB($machineconn, 'count', 'success: Maschinendaten gespeichert.');
            } else {
                logDB($machineconn, 'count', 'Fehler beim Speichern der Maschinendaten: ' . $machineconn->error);
            }
        } else {
            logDB($machineconn, 'count', 'Fehler: Maschine wurde nicht gefunden.');
        }
    } else {
        logDB($machineconn, 'count', 'Fehler: Keine aktive Schicht an dieser Maschine gefunden.');
    }
}

function handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByDStartStop($machineconn, $terminal_id, $d_entry_startstop);

    if (!$machine_id) {
        logDB($machineconn, 'stop', 'Fehler: Maschine wurde nicht gefunden.');
        return; 
    }

    $updateMachineSql = "UPDATE machine SET state = 'stop', userid = NULL, `order` = NULL WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";

        if ($machineconn->query($updateShiftSql) === TRUE) {
            logDB($machineconn, 'stop', 'success: Maschine gestoppt und Schicht beendet.');
        } else {
            logDB($machineconn, 'stop', 'Fehler beim Beenden der Schicht: ' . $machineconn->error);
        }
    } else {
        logDB($machineconn, 'stop', 'Fehler beim Stoppen der Maschine: ' . $machineconn->error);
    }
}


