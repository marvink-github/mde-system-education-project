<?php


function logDB($machineconn, $logType, $logMessage) {
    // Für GET-Array
    if (is_array($logMessage)) {
        $logMessage = http_build_query($logMessage);
    }
    
    $sql = "INSERT INTO log (log_type, log_message) VALUES ('$logType', '$logMessage')";
    $result = $machineconn->query($sql);
    
    if (!$result) {
        error_log('logDB' . mysqli_error($machineconn));
    }
}


function getMachineIdByAction($machineconn, $terminal_id, $column, $value) {
    $machineSql = "SELECT m.idMachine FROM machine AS m 
                   JOIN device AS d ON m.device_idDevice = d.idDevice 
                   WHERE d.terminal_id = '$terminal_id' AND m.$column = '$value'"; 

    $machineResult = $machineconn->query($machineSql);

    if ($machineResult->num_rows > 0) {
        $machine = $machineResult->fetch_assoc();
        return $machine['idMachine'];
    } else {
        logDB($machineconn, 'action', "error: machine not found. terminal_id: $terminal_id and $column: $value");
        return null; 
    }
}


function handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if ($machine_id === null) {
        logDB($machineconn, 'start', "error: machine or d_entry_startstop not found. devicetime: $timestamp");
        return; 
    }

    $machineStateSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $machineStateResult = $machineconn->query($machineStateSql);
    $machine = $machineStateResult->fetch_assoc();

    if ($machine['state'] === 'start') {
        logDB($machineconn, 'start', "error: machine is already active. devicetime: $timestamp");
        return; 
    }

    $checkShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $shiftResult = $machineconn->query($checkShiftSql);

    if ($shiftResult->num_rows > 0) {
        logDB($machineconn, 'start', "error: an active shift already exists for this machine. devicetime: $timestamp");
        return; 
    }

    $updateMachineSql = "UPDATE machine SET state = 'start' WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', $machine_id)";

        if ($machineconn->query($shiftSql) === TRUE) {
            logDB($machineconn, 'start', "success: machine and shift started. devicetime: $timestamp");
        } else {
            logDB($machineconn, 'start', "error: starting the shift: $machineconn->error. devicetime: $timestamp");
            return; 
        }
    } else {
        logDB($machineconn, 'start', "error: starting the achine: $machineconn->error. devicetime: $timestamp");
        return; 
    }
}


function handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_counter) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_counter', $d_entry_counter);

    if (!$machine_id) {
        logDB($machineconn, 'count', "error: machine not found. machine_id: $machine_id. devicetime: $timestamp");
        return; 
    }

    $stateCheckSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $stateCheckResult = $machineconn->query($stateCheckSql);

    if ($stateCheckResult->num_rows > 0) {
        $machine = $stateCheckResult->fetch_assoc();

        if ($machine['state'] === 'stop') {
            logDB($machineconn, 'count', "error: machine is not active. (state: stop). idMachine: $machine_id. devicetime: $timestamp");
            return;
        }
    } else {
        logDB($machineconn, 'count', "error: machine not found. devicetime: $timestamp");
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

            if ($machine['d_entry_counter'] != $d_entry_counter) {
                logDB($machineconn, 'count', "error: d_entry_counter does not match the machine. devicetime: $timestamp");
                return; 
            }

            $userid = $machine['userid'] ?? 'anonym'; 
            $orderid = $machine['order'] ?? null;

            $machineDataSql = "INSERT INTO machinedata (timestamp, value, shift_idshift, userid, `order`)
                               VALUES ('$timestamp', '$value', '$shift_id', '$userid', '$orderid')";
            
            if ($machineconn->query($machineDataSql) === TRUE) {
                logDB($machineconn, 'count', "success: machinedata saved. devicetime: $timestamp");
            } else {
                logDB($machineconn, 'count', "error: failed to save machinedata. $machineconn->error. devicetime: $timestamp");
            }
        } else {
            logDB($machineconn, 'count', "error: machine not found. devicetime: $timestamp");
        }
    } else {
        logDB($machineconn, 'count', "error: no active shift found for this machine. devicetime: $timestamp");
    }
}


function handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if (!$machine_id) {
        logDB($machineconn, 'stop', "error: machine not found. devicetime: $timestamp");
        return; 
    }

    $machineDataSql = "SELECT userid, state FROM machine WHERE idMachine = $machine_id";
    $machineDataResult = $machineconn->query($machineDataSql);
    $machineData = $machineDataResult->fetch_assoc();

    $useridExists = !empty($machineData['userid']);
    
    $updateMachineSql = "UPDATE machine SET state = 'stop', userid = NULL, `order` = NULL WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";
        $shiftUpdated = $machineconn->query($updateShiftSql) === TRUE;

        if ($useridExists && $shiftUpdated) {
            $logMessage = "success: machine stopped, shiftid and userid removed. devicetime: $timestamp";
        } elseif ($useridExists) {
            $logMessage = "success: machine stopped and userid removed. devicetime: $timestamp";
        } elseif ($shiftUpdated) {
            $logMessage = "success: machine stopped and shiftid removed. devicetime: $timestamp";
        } else {
            $logMessage = "success: machine stopped. devicetime: $timestamp";
        }

        logDB($machineconn, 'stop', $logMessage);

    } else {
        logDB($machineconn, 'stop', "error: stopping the machine: $machineconn->error. devicetime: $timestamp");
    }
}


function handleScannerAction($machineconn, $timestamp, $terminal_id, $terminal_type, $badge, $barcode) {
    $deviceExistsSql = "SELECT 1 FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    $deviceExistsResult = $machineconn->query($deviceExistsSql);

    if ($deviceExistsResult->num_rows > 0) {
        $scannerDataSql = "INSERT INTO machinedata (timestamp, userid, value, `order`) 
                           VALUES ('$timestamp', '$badge', '1', '$barcode')";
        
        if ($machineconn->query($scannerDataSql) === TRUE) {
            logDB($machineconn, 'scanner', "success: $barcode has been scanned by $badge. devicetime: $timestamp");
        } else {
            logDB($machineconn, 'scanner', "error: saving scan data: $machineconn->error. devicetime: $timestamp");
        }
    } else {
        logDB($machineconn, 'scanner', "error: device not found for terminal_id: $terminal_id and terminal_type: $terminal_type. devicetime: $timestamp");
    }
}


function updateAliveStatus($machineconn, $timestamp, $terminal_id, $terminal_type) {
    $sqlUpdate = "UPDATE device SET last_alive = '$timestamp' WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";

    if (!$machineconn->query($sqlUpdate)) {
        logDB($machineconn, 'alive', "error updating last_alive for ($terminal_id, $terminal_type): " . $machineconn->error);
    }
}

