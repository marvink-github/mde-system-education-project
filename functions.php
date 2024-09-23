<?php

function logDB($machineconn, $logType, $logMessage) {
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


function getMachineAndValidateState($machineconn, $machine_id, $timestamp, $expectedState) {
    $stateCheckSql = "SELECT state FROM machine WHERE idMachine = $machine_id";
    $stateCheckResult = $machineconn->query($stateCheckSql);

    if ($stateCheckResult->num_rows > 0) {
        $machine = $stateCheckResult->fetch_assoc();

        if ($machine['state'] !== $expectedState) {
            logDB($machineconn, 'state', "error: machine state is not as expected. (current state: {$machine['state']}, expected: $expectedState). idMachine: $machine_id. devicetime: $timestamp");
            return false;
        }
    } else {
        logDB($machineconn, 'state', "error: machine not found. idMachine: $machine_id. devicetime: $timestamp");
        return false;
    }

    return true;
}


function getMachineData($machineconn, $machine_id) {
    $machineDataSql = "SELECT userid, state FROM machine WHERE idMachine = $machine_id";
    $machineDataResult = $machineconn->query($machineDataSql);
    
    if ($machineDataResult->num_rows > 0) {
        return $machineDataResult->fetch_assoc();
    } else {
        return false;
    }
}


function checkForActiveShift($machineconn, $machine_id, $timestamp) {
    $checkShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $shiftResult = $machineconn->query($checkShiftSql);

    if ($shiftResult->num_rows > 0) {
        logDB($machineconn, 'start', "error: an active shift already exists for this machine. devicetime: $timestamp");
        return false;
    }
    return true;
}


function startMachineAndShift($machineconn, $machine_id, $timestamp) {
    $updateMachineSql = "UPDATE machine SET state = 'start' WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $shiftSql = "INSERT INTO shift (startTime, machine_idMachine) VALUES ('$timestamp', $machine_id)";

        if ($machineconn->query($shiftSql) === TRUE) {
            logDB($machineconn, 'start', "success: machine and shift started. devicetime: $timestamp");
        } else {
            logDB($machineconn, 'start', "error: starting the shift: $machineconn->error. devicetime: $timestamp");
        }
    } else {
        logDB($machineconn, 'start', "error: starting the machine: $machineconn->error. devicetime: $timestamp");
    }
}


function stopMachineAndShift($machineconn, $machine_id, $timestamp) {
    $updateMachineSql = "UPDATE machine SET state = 'stop', userid = NULL, `order` = NULL WHERE idMachine = $machine_id";

    if ($machineconn->query($updateMachineSql) === TRUE) {
        $updateShiftSql = "UPDATE shift SET endTime = '$timestamp' WHERE machine_idMachine = $machine_id AND endTime IS NULL";
        return $machineconn->query($updateShiftSql) === TRUE;
    } else {
        return false;
    }
}


function getActiveShift($machineconn, $machine_id, $timestamp) {
    $currentShiftSql = "SELECT idshift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
    $currentShiftResult = $machineconn->query($currentShiftSql);

    if ($currentShiftResult->num_rows > 0) {
        $shift = $currentShiftResult->fetch_assoc();
        return $shift['idshift'];
    } else {
        logDB($machineconn, 'count', "error: no active shift found for this machine. devicetime: $timestamp");
        return false;
    }
}


function processMachineData($machineconn, $machine_id, $shift_id, $timestamp, $value, $d_entry_counter) {
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
}


function handleStartAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    // Hole die Machine-ID anhand der Terminal-ID und d_entry_startstop
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if ($machine_id === null) {
        logDB($machineconn, 'start', "error: machine not found. devicetime: $timestamp");
        return; 
    }

    // Überprüfe, ob die Maschine inaktiv ist (state muss 'stop' sein)
    if (!getMachineAndValidateState($machineconn, $machine_id, $timestamp, 'stop')) {
        return;
    }
    // Überprüfe ob eine aktive Schicht vorhanden ist
    if (!checkForActiveShift($machineconn, $machine_id, $timestamp)) {
        return;
    }
    // Maschine starten und Schicht starten
    startMachineAndShift($machineconn, $machine_id, $timestamp);
}


function handleMachineData($machineconn, $timestamp, $terminal_id, $value, $d_entry_counter) {
    // Hole die Machine-ID anhand der Terminal-ID und d_entry_counter
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_counter', $d_entry_counter);

    if ($machine_id === null) {
        logDB($machineconn, 'count', "error: machine not found. devicetime: $timestamp");
        return;
    }

    // Überprüfe, ob die Maschine aktiv ist (state muss 'start' sein)
    if (!getMachineAndValidateState($machineconn, $machine_id, $timestamp, 'start')) {
        return;
    }

    // Hole die aktive Schicht
    $shift_id = getActiveShift($machineconn, $machine_id, $timestamp);
    if (!$shift_id) {
        return;
    }

    // Verarbeite und speichern der Maschinendaten
    processMachineData($machineconn, $machine_id, $shift_id, $timestamp, $value, $d_entry_counter);
}


function handleStopAction($machineconn, $timestamp, $terminal_id, $d_entry_startstop) {
    // Hole die Machine-ID anhand der terminal_id und d_entry_startstop
    $machine_id = getMachineIdByAction($machineconn, $terminal_id, 'd_entry_startstop', $d_entry_startstop);

    if (!$machine_id) {
        logDB($machineconn, 'stop', "error: machine not found. devicetime: $timestamp");
        return; 
    }

    // Hole die Maschinendaten anhand der Machine-ID
    $machineData = getMachineData($machineconn, $machine_id);
    
    if (!$machineData) {
        logDB($machineconn, 'stop', "error: machine data not found. devicetime: $timestamp");
        return; 
    }

    // Stoppe die Maschine und die Schicht
    if (!stopMachineAndShift($machineconn, $machine_id, $timestamp)) {
        logDB($machineconn, 'stop', "error: stopping the machine: $machineconn->error. devicetime: $timestamp");
        return; 
    }

    logDB($machineconn, 'stop', "success: machine and shift stopped. devicetime: $timestamp");
}

function updateDisplayDesign($machineconn, $designName) {
    $designPath = __DIR__ . '/displaydesign/' . $designName;

    if (!file_exists($designPath)) {
        logDB($machineconn, 'Design', "Failed to load $designPath");
        return; 
    }

    $response = 'df_api=1&df_load_file=http://127.0.0.1' . dirname($_SERVER['PHP_SELF']) . '/displaydesign/' . rawurlencode($designName);

    echo $response;
    logDB($machineconn, 'Display', $response);
    exit();
}


function updateAliveStatus($machineconn, $timestamp, $terminal_id, $terminal_type) {
    $sqlUpdate = "UPDATE device SET last_alive = '$timestamp' WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";

    if (!$machineconn->query($sqlUpdate)) {
        logDB($machineconn, 'alive', "error updating last_alive for ($terminal_id, $terminal_type): " . $machineconn->error);
    }
}

function handleScannerAction($machineconn, $timestamp, $terminal_id, $terminal_type, $barcode, $badge) {
    $deviceExistsSql = "SELECT 1 FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    $deviceExistsResult = $machineconn->query($deviceExistsSql);

    if ($deviceExistsResult->num_rows > 0) {
        // Überprüfe, ob die Maschine mit dem Namen 'Barcode' existiert
        $machineIdSql = "SELECT idMachine FROM machine WHERE name = 'Barcode'";
        $machineIdResult = $machineconn->query($machineIdSql);

        if ($machineIdResult->num_rows > 0) {
            $machine = $machineIdResult->fetch_assoc();
            $machine_id = $machine['idMachine'];

            // Hole die aktive Order der Maschine
            $activeOrderSql = "SELECT `order` FROM machine WHERE idMachine = $machine_id";
            $activeOrderResult = $machineconn->query($activeOrderSql);
            $activeOrder = $activeOrderResult->fetch_assoc();
            $currentOrder = $activeOrder['order'];

            // Hole die aktuelle Schicht für die Maschine
            $activeShiftSql = "SELECT idShift FROM shift WHERE machine_idMachine = $machine_id AND endTime IS NULL";
            $activeShiftResult = $machineconn->query($activeShiftSql);

            if ($activeShiftResult->num_rows > 0) {
                $activeShift = $activeShiftResult->fetch_assoc();
                $currentShiftId = $activeShift['idShift'];

                // Setze die userid der Maschine
                $updateUserIdSql = "UPDATE machine SET userid = '$badge' WHERE idMachine = $machine_id";
                if ($machineconn->query($updateUserIdSql) === TRUE) {
                    // Schreibe in die machinedata
                    $scannerDataSql = "INSERT INTO machinedata (timestamp, userid, value, `order`, shift_idShift) 
                                       VALUES ('$timestamp', '$badge', '$barcode', '$currentOrder', $currentShiftId)";
                    
                    if ($machineconn->query($scannerDataSql) === TRUE) {
                        logDB($machineconn, 'scanner', "success: $barcode has been scanned by $badge. devicetime: $timestamp");
                    } else {
                        logDB($machineconn, 'scanner', "error: saving scan data: $machineconn->error. devicetime: $timestamp");
                    }
                } else {
                    logDB($machineconn, 'scanner', "error: updating machine userid: $machineconn->error. devicetime: $timestamp");
                }
            } else {
                logDB($machineconn, 'scanner', "warning: no active shift for machine_id: $machine_id. devicetime: $timestamp.");
            }
        } else {
            logDB($machineconn, 'scanner', "error: no machine found for barcode: $barcode. devicetime: $timestamp");
        }
    } else {
        logDB($machineconn, 'scanner', "error: device not found for terminal_id: $terminal_id and terminal_type: $terminal_type. devicetime: $timestamp");
    }
}

########################################################################################################################################
### Firmware Update 
########################################################################################################################################

function getFirmwareFromDevice($machineconn, $terminal_id, $terminal_type) {
    $query = "SELECT firmware_version FROM device WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    $result = mysqli_query($machineconn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['firmware_version'];
    }
    return null;
}

function getFirmwareFromFileserver($parameter) {
    $url = "http://127.0.0.1/api/firmware/query.php?fw=$parameter";    
    $response = file_get_contents($url);
    parse_str($response, $output); 
    return $output['detail1']; 
}

function isUpdateRequired($currentVersion, $latestVersion) {
    $currentVersion = preg_replace('/[^0-9.]/', '', $currentVersion);
    $latestVersion = preg_replace('/[^0-9.]/', '', $latestVersion);

    return version_compare($currentVersion, $latestVersion, '<');
}

