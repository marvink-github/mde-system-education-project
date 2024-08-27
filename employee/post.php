<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../connection.php'; 

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userid'] ?? null;
$badge = $input['badge'] ?? null; 

if (!$userId) {
    http_response_code(400);
    echo json_encode(["message" => "Fehlende Parameter: userid."]);
    exit();
}

$userId = $machineconn->real_escape_string($userId);
$badge = $badge ? $machineconn->real_escape_string($badge) : null; 

$sql = "SELECT idEmployee FROM employee WHERE userid = '$userId'";
$result = $machineconn->query($sql);

if ($result->num_rows > 0) {      
    $row = $result->fetch_assoc();
    $employeeId = $row['idEmployee'];
    
    if ($badge) {    
        $checkBadgeSql = "SELECT * FROM authentication WHERE badge = '$badge'";
        $badgeResult = $machineconn->query($checkBadgeSql);

        if ($badgeResult->num_rows > 0) {        
            http_response_code(409); 
            echo json_encode(["message" => "Fehler: Badge bereits vergeben."]);
            $machineconn->close();
            exit();
        } else {            
            $authSql = "INSERT INTO authentication (employee_idEmployee, badge) VALUES ($employeeId, '$badge')";

            if ($machineconn->query($authSql)) {
                http_response_code(201);
                echo json_encode(["message" => "Badge erfolgreich hinzugefügt", "idEmployee" => $employeeId, "badge" => $badge]);
            } else {        
                http_response_code(400);
                echo json_encode(["message" => "Fehler beim Hinzufügen des Badges: " . $machineconn->error]);
            }
        }
    } else {
        http_response_code(200);
        echo json_encode(["message" => "Benutzer bereits vorhanden, kein Badge angegeben.", "idEmployee" => $employeeId]);
    }

} else {      
    if ($badge) {
        $checkBadgeSql = "SELECT * FROM authentication WHERE badge = '$badge'";
        $badgeResult = $machineconn->query($checkBadgeSql);

        if ($badgeResult->num_rows > 0) {
            http_response_code(409); 
            echo json_encode(["message" => "Fehler: Badge bereits vorhanden für einen anderen Benutzer, Benutzer wurde nicht hinzugefügt."]);
            $machineconn->close();
            exit();
        } else {            
            $sql = "INSERT INTO employee (userid) VALUES ('$userId')";

            if ($machineconn->query($sql)) {
                $employeeId = $machineconn->insert_id;                 
                
                $authSql = "INSERT INTO authentication (employee_idEmployee, badge) VALUES ($employeeId, '$badge')";

                if ($machineconn->query($authSql)) {
                    http_response_code(201);
                    echo json_encode(["message" => "Benutzer und Badge erfolgreich hinzugefügt.", "idEmployee" => $employeeId, "userid" => $userId, "badge" => $badge]);
                } else {                  
                    $machineconn->query("DELETE FROM employee WHERE idEmployee = $employeeId");
                    http_response_code(400);
                    echo json_encode(["message" => "Fehler beim Hinzufügen des Badges: " . $machineconn->error]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Fehler beim Hinzufügen des Mitarbeiters: " . $machineconn->error]);
            }
        }
    } else {        
        $sql = "INSERT INTO employee (userid) VALUES ('$userId')";

        if ($machineconn->query($sql)) {
            $employeeId = $machineconn->insert_id; 
            http_response_code(201);
            echo json_encode(["message" => "Benutzer erfolgreich hinzugefügt, kein Badge angegeben.", "idEmployee" => $employeeId]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Fehler beim Hinzufügen des Mitarbeiters: " . $machineconn->error]);
        }
    }
}

$machineconn->close();
?>

