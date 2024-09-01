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
        logDB($machineconn, 'action', "error: Machine not found. terminal_id: $terminal_id and $column: $value");
        return null; 
    }
}
// $machineIdStartStop = getMachineIdByCondition($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);
// $machineIdCount = getMachineIdByCondition($machineconn, $terminal_id, 'd_entry_counter', $d_entry_count);


// function getMachineIdByDStartStop($machineconn, $terminal_id, $d_entry_startstop) {
//     $machineSql = "SELECT m.idMachine FROM machine AS m 
//                    JOIN device AS d ON m.device_idDevice = d.idDevice 
//                    WHERE d.terminal_id = '$terminal_id' AND m.d_entry_startstop = '$d_entry_startstop'";

//     $machineResult = $machineconn->query($machineSql);

//     if ($machineResult->num_rows > 0) {
//         $machine = $machineResult->fetch_assoc();
//         return $machine['idMachine'];
//     } else {
//         logDB($machineconn, 'ERROR', "Maschine nicht gefunden für Terminal-ID: $terminal_id und d_entry_startstop: $d_entry_startstop");
//         return null; 
//     }
// }



// function getMachineIdByDCount($machineconn, $terminal_id, $d_entry_count) {
//     $machineSql = "SELECT m.idMachine FROM machine AS m 
//                    JOIN device AS d ON m.device_idDevice = d.idDevice 
//                    WHERE d.terminal_id = '$terminal_id' AND m.d_entry_counter = $d_entry_count";  

//     $machineResult = $machineconn->query($machineSql);

//     if ($machineResult->num_rows > 0) {
//         $machine = $machineResult->fetch_assoc();
//         return $machine['idMachine'];
//     } else {
//         return null; 
//     }
// }

function handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if ($machine_id === null) {
        logDB($machineconn, 'start', "error: Machine or digital entry not found. DeviceTime: $timestamp");
        return; 
    }

    $machineStateSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $machineStateResult = $machineconn->query($machineStateSql);
    $machine = $machineStateResult->fetch_assoc();

    if ($machine['state'] === 'start') {
        logDB($machineconn, 'start', "error: Machine is already active. DeviceTime: $timestamp");
        return; 
    }

    $checkShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $shiftResult = $machineconn->query($checkShiftSql);

    if ($shiftResult->num_rows > 0) {
        logDB($machineconn, 'start', "error: An active shift already exists for this machine. DeviceTime: $timestamp");
        return; 
    }

    $updateMachineSql = "UPDATE machine SET state = 'start' WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', $machine_id)";

        if ($machineconn->query($shiftSql) === TRUE) {
            logDB($machineconn, 'start', "success: Machine and shift started. DeviceTime: $timestamp");
        } else {
            logDB($machineconn, 'start', "error: Starting the shift: $machineconn->error. DeviceTime: $timestamp");
            return; 
        }
    } else {
        logDB($machineconn, 'start', "error: Starting the Machine: $machineconn->error. DeviceTime: $timestamp");
        return; 
    }
}

function handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_counter) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_count', $d_entry_counter);

    if (!$machine_id) {
        logDB($machineconn, 'count', "error: Machine not found. machine_id: $machine_id. DeviceTime: $timestamp");
        return; 
    }

    $stateCheckSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $stateCheckResult = $machineconn->query($stateCheckSql);

    if ($stateCheckResult->num_rows > 0) {
        $machine = $stateCheckResult->fetch_assoc();

        if ($machine['state'] === 'stop') {
            logDB($machineconn, 'count', "error: Machine is not active. (state: stop). idMachine: $machine_id. DeviceTime: $timestamp");
            return;
        }
    } else {
        logDB($machineconn, 'count', "error: Machine not found. DeviceTime: $timestamp");
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
                logDB($machineconn, 'count', "error: d_entry_counter does not match the machine. DeviceTime: $timestamp");
                return; 
            }

            $userid = $machine['userid'] ?? 'anonym'; 
            $orderid = $machine['order'] ?? null;

            $machineDataSql = "INSERT INTO machinedata (timestamp, value, shift_idshift, userid, `order`)
                               VALUES ('$timestamp', '$value', '$shift_id', '$userid', '$orderid')";
            
            if ($machineconn->query($machineDataSql) === TRUE) {
                logDB($machineconn, 'count', "success: Machinedata saved. DeviceTime: $timestamp");
            } else {
                logDB($machineconn, 'count', "error: Failed to save machinedata. $machineconn->error. DeviceTime: $timestamp");
            }
        } else {
            logDB($machineconn, 'count', "error: Machine not found. DeviceTime: $timestamp");
        }
    } else {
        logDB($machineconn, 'count', "error: No active shift found for this machine. DeviceTime: $timestamp");
    }
}

function handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if (!$machine_id) {
        logDB($machineconn, 'stop', "error: Machine not found. DeviceTime: $timestamp");
        return; 
    }

    // Hier ändern falls wir nicht mehr wollen das user id und state bei Maschinenstop auf NULL und stop gesetzt wird
    $updateMachineSql = "UPDATE machine SET state = 'stop', userid = NULL, `order` = NULL WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";

        if ($machineconn->query($updateShiftSql) === TRUE) {
            logDB($machineconn, 'stop', "success: Machine stopped and shift ended. DeviceTime: $timestamp");
        } else {
            logDB($machineconn, 'stop', "error: Ending the shift: $machineconn->error. DeviceTime: $timestamp");
        }
    } else {
        logDB($machineconn, 'stop', "error: Stopping the machine: $machineconn->error. DeviceTime: $timestamp");
    }
}


function handleScannerAction($machineconn, $timestamp, $terminal_id, $terminal_type, $badge, $value) {
    $deviceStateSql = "SELECT state FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    $deviceStateResult = $machineconn->query($deviceStateSql);

    if ($deviceStateResult->num_rows > 0) {
        $deviceState = $deviceStateResult->fetch_assoc();

        if ($deviceState['state'] !== 'active') {
            logDB($machineconn, 'scanner', "error: Device is not active. DeviceTime: $timestamp");
            return;
        }

        $scannerDataSql = "INSERT INTO machinedata (timestamp, userid, value) 
                           VALUES ('$timestamp', '$badge', '$value')";
        
        if ($machineconn->query($scannerDataSql) === TRUE) {
            logDB($machineconn, 'scanner', "success: Barcode $value has been scanned by $badge. DeviceTime: $timestamp");
        } else {
            logDB($machineconn, 'scanner', "error: Saving scan data: $machineconn->error. DeviceTime: $timestamp");
        }
    } else {
        logDB($machineconn, 'scanner', "error: Device not found for terminal ID: $terminal_id and type: $terminal_type. DeviceTime: $timestamp");
    }
}

