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


function registryBadge($machineconn, $userid, $badge, $employeeId) {      
    if ($employeeId === false) {
        $employeeId = insertEmployee($machineconn, $userid);
    }

    $existingEmployeeId = getBadgeId($machineconn, $badge);

    if ($existingEmployeeId === false) {
        insertBadge($machineconn, $badge, $employeeId);
    } else {
        logDB($machineconn, 'ERROR', 'Diese Badge ist bereits vergeben.');
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
        exit("Fehler beim Einfügen des Employees: " . $machineconn->error);
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
        exit("Fehler beim Einfügen des Badges: " . $machineconn->error);
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
    $employeeId = getBadgeId($machineconn, $badge);
    $machine_id = getMachineId($machineconn, $terminal_id, $terminal_type);

    if (!$machine_id) {
        return false;
    }

    // $startTime = date("Y-m-d H:i:s"); // vermutlich Zeitstempel aus dem Terminal sein!
    $sql = "INSERT INTO machine_employee (machine_id, employee_id, start_time, state) 
            VALUES ($machine_id, $employeeId, '$timestamp', 'start')";

    if (!$machineconn->query($sql)) {
        logDB($machineconn, 'ERROR', 'Fehler beim Starten der Sitzung.');
        return false;
    }
    
    updateMachineState($machineconn, $terminal_id, $terminal_type, 'active');

    return true;
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

function stopEmployeeOnMachine($machineconn, $terminal_id, $terminal_type, $badge) {
    $employeeId = getBadgeId($machineconn, $badge);

    if ($employeeId === false) {
        return false; 
    }
    
    $result = $machineconn->query("SELECT employee_idEmployee FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'");

    if ($result->num_rows == 0) {
        return false;
    }

    $row = $result->fetch_assoc();
    $currentEmployeeId = $row['employee_idEmployee'];

    if ($currentEmployeeId != $employeeId) {
        return false; 
    }

    $sql = "UPDATE machine SET employee_idEmployee = NULL WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    
    if (!$machineconn->query($sql)) {
        return false;
    }

    updateMachineState($machineconn, $terminal_id, $terminal_type, 'inactive');

    return true;
}


function insertMachineData($machineconn, $timestamp, $digital_entry, $impulse, $machineId, $employeeId) {
    $sql = "INSERT INTO machinedata (timestamp, digital, value, machine_idMachine, employee_idEmployee)
              VALUES ('$timestamp', '$digital_entry', '$impulse', $machineId, $employeeId)";    

    if (!$machineconn->query($sql)) {
        exit("Fehler beim Einfügen der Maschinendaten: " . $machineconn->error);
    }
}


function startOrder($machineconn, $badge, $timestamp, $barcode, ) {   
    $employeeId = getBadgeId($machineconn, $badge);

    if (!isEmployeeLoggedIn($machineconn, $employeeId)) {
        return "Der Mitarbeiter ist nicht an der Maschine angemeldet.";
    }

    if (isOrderNumberExists($machineconn, $barcode)) {
        return "Die Auftragsnummer ist bereits vorhanden.";
    }

    $sql = "INSERT INTO `order` (startTime, ordernumber, employee_idEmployee) 
            VALUES ('$timestamp', '$barcode', $employeeId)";

    if ($machineconn->query($sql) === TRUE) {
        return true; 
    } else {
        return $machineconn->error; 
    }
}


function finishOrder($machineconn, $badge, $timestamp) {
    $employeeId = getBadgeId($machineconn, $badge);      

    $sql = "UPDATE `order` 
            SET endTime = '$timestamp', state = 'finished' 
            WHERE employee_idEmployee = $employeeId 
            ORDER BY idOrder DESC LIMIT 1";
    
    if ($machineconn->query($sql) === TRUE) {
        return true; 
    } else {
        return false; 
    }
}


function isEmployeeLoggedIn($machineconn, $employeeId) {

    $employeeId = intval($employeeId); 
    
    $sql = "SELECT COUNT(*) FROM machine WHERE employee_idEmployee = $employeeId";
    
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


function getMachineId($machineconn, $terminal_id, $terminal_type) {
    $sql = "SELECT idMachine FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";
    $result = $machineconn->query($sql);
    if (!$result) {        
        return false;
    }

    if ($result->num_rows == 0) {        
        return false;
    }

    $machineRow = $result->fetch_assoc();
    $machineId = $machineRow['idMachine'];
    return $machineId;
}


function updateMachineState($machineconn, $terminal_id, $terminal_type, $action) {
    $sql = "UPDATE machine SET state = '$action' WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'";

    if (!$machineconn->query($sql)) {
        return false;
    }
    return true;
}


function getMachineAndEmployeeId($machineconn, $terminal_id, $terminal_type) {
    $result = $machineconn->query("SELECT idMachine, employee_idEmployee FROM machine WHERE terminal_id = '$terminal_id' AND terminal_type = '$terminal_type'");

    if ($result->num_rows == 0) {
        exit("Fehler: Maschine mit der angegebenen Seriennummer und Terminaltyp wurde nicht gefunden.");
    }

    $row = $result->fetch_assoc();
    return [
        'idMachine' => $row['idMachine'],
        'employee_idEmployee' => $row['employee_idEmployee']
    ];
}

?>