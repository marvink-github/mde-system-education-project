<?php

// $apiKey = '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c';

// // file_get_contents("http://127.0.0.1/conni/users/add8761238976");

// $output1 = [];
// exec("start /B C:\\xampp\\php\\php.exe start_machine.php 1 dj198ma 189023710 $apiKey " . rand(5, 15) . " 2 4 3 3533 2>&1", $output1);
// echo "Output für Maschine 1:\n" . implode("\n", $output1) . "\n";

// $output2 = [];
// exec("start /B C:\\xampp\\php\\php.exe start_machine.php 2 kj245nd 132178412 $apiKey " . rand(5, 15) . " 2 2 1 3533 2>&1", $output2);
// echo "Output für Maschine 2:\n" . implode("\n", $output2) . "\n";

// $output3 = [];
// exec("start /B C:\\xampp\\php\\php.exe start_machine.php 1 lk098qw 823136427 $apiKey " . rand(5, 15) . " 2 4 3 3533 2>&1", $output3);
// echo "Output für Maschine 1:\n" . implode("\n", $output3) . "\n";

// $output4 = [];
// exec("start /B C:\\xampp\\php\\php.exe start_machine.php 2 mn123op 237861471 $apiKey " . rand(5, 15) . " 2 2 1 3533 2>&1", $output4);
// echo "Output für Maschine 2:\n" . implode("\n", $output4) . "\n";

// echo "Produktionssimulation abgeschlossen.\n";


// include '../connection.php';

// $sql = "SELECT idMachinedata, timestamp FROM machinedata WHERE idMachinedata BETWEEN 1 AND 2003 ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $startDateTime = new DateTime('2024-09-30 08:00:00'); 
//     $endDateTime = new DateTime('2024-10-11 17:56:00'); 

//     // Berechne die Gesamtanzahl der Sekunden zwischen Start- und Endzeit
//     $totalSeconds = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
    
//     // Berechne den Abstand zwischen den Zeitstempeln in Sekunden
//     $intervalSeconds = (int)($totalSeconds / 2003);

//     while ($row = $result->fetch_assoc()) {
//         // Berechne den neuen Zeitstempel
//         $newDateTime = clone $startDateTime;
//         $startDateTime->modify("+{$intervalSeconds} seconds"); // Nächsten Zeitstempel setzen

//         // Update den Zeitstempel in der Datenbank
//         $updateSql = "UPDATE machinedata SET timestamp = ? WHERE idMachinedata = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $formattedDateTime = $startDateTime->format('Y-m-d H:i:s');
//         $stmt->bind_param("si", $formattedDateTime, $row['idMachinedata']); 
//         $stmt->execute();
//     }
//     echo "Zeitstempel wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();



// include '../connection.php';

// $sql = "SELECT idMachinedata FROM machinedata WHERE idMachinedata BETWEEN 1 AND 2003 ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $randomValue = rand(1, 10);

//         // Update den Wert in der Datenbank
//         $updateSql = "UPDATE machinedata SET value = ? WHERE idMachinedata = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $stmt->bind_param("ii", $randomValue, $row['idMachinedata']); 
//         $stmt->execute();
//     }
//     echo "Werte wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }
// $machineconn->close();



// include '../connection.php';

// $sql = "SELECT idshift FROM shift ORDER BY idshift ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $id = 1; // Start-ID

//     while ($row = $result->fetch_assoc()) {
//         $updateSql = "UPDATE shift SET idshift = ? WHERE idshift = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $stmt->bind_param("ii", $id, $row['idshift']);
//         $stmt->execute();
//         $id++; // Erhöhe die ID für den nächsten Datensatz
//     }
//     echo "IDs wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();

// include '../connection.php';

// $sql = "SELECT idshift FROM shift WHERE idshift BETWEEN 1 AND 261 ORDER BY idshift ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $startDateTime = new DateTime('2024-09-30 08:00:00'); 
//     $endDateTime = new DateTime('2024-10-11 17:56:00'); 

//     // Berechne die Anzahl der Schichten
//     $numShifts = $result->num_rows;

//     // Dauer jeder Schicht in Sekunden (z.B. 8 Stunden = 28800 Sekunden)
//     $shiftDuration = 3778; 

//     // Berechne die maximale Anzahl der Schichten im gegebenen Zeitraum
//     $totalSeconds = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
//     $maxShifts = floor($totalSeconds / $shiftDuration);
    
//     // Wenn die Anzahl der Schichten größer ist als die maximalen Schichten, dann anpassen
//     if ($numShifts > $maxShifts) {
//         echo "Die Anzahl der Schichten überschreitet die maximale Anzahl im gegebenen Zeitraum: $numShifts";
//         exit;
//     }

//     while ($row = $result->fetch_assoc()) {
//         // Berechne die Start- und Endzeiten für jede Schicht
//         $newStartTime = clone $startDateTime;
//         $newEndTime = clone $newStartTime;
//         $newEndTime->modify("+{$shiftDuration} seconds");

//         // Update den Zeitstempel in der Datenbank
//         $updateSql = "UPDATE shift SET startTime = ?, endTime = ? WHERE idshift = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $formattedStartTime = $newStartTime->format('Y-m-d H:i:s');
//         $formattedEndTime = $newEndTime->format('Y-m-d H:i:s');
//         $stmt->bind_param("ssi", $formattedStartTime, $formattedEndTime, $row['idshift']); 
//         $stmt->execute();

//         // Setze die Startzeit für die nächste Schicht auf die Endzeit der aktuellen Schicht
//         $startDateTime = $newEndTime;
//     }
//     echo "Schichtzeiten wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();



// include '../connection.php';

// // Zähler für die gewünschten Maschinen
// $machineIds = [1, 2]; // Maschinen 1 und 2
// $totalRecords = 261; // Gesamte Datensätze
// $recordsPerMachine = $totalRecords / count($machineIds); // Anzahl der Einträge pro Maschine

// // Array für die Verteilung der Maschinen
// $machineDistribution = [];

// // Maschinen zufällig verteilen
// for ($i = 0; $i < $totalRecords; $i++) {
//     $randomMachineId = $machineIds[array_rand($machineIds)]; // Zufällige Maschine wählen
//     $machineDistribution[] = $randomMachineId; // Maschine zum Array hinzufügen
// }

// // Hier kannst du dann die $machineDistribution verwenden, um die `shift`-Tabelle zu aktualisieren
// foreach ($machineDistribution as $index => $machineId) {
//     $shiftId = $index + 1; // Beispielweise: die ID für die `shift`-Tabelle
//     // Aktualisiere die shift-Tabelle mit der entsprechenden machine_id
//     $updateQuery = "UPDATE shift SET machine_idMachine = $machineId WHERE idshift = $shiftId";
//     $machineconn->query($updateQuery);
// }

// echo "Die Maschinen-IDs wurden erfolgreich verteilt!";



// include '../connection.php';

// $sql = "SELECT idshift FROM shift ORDER BY idshift ASC LIMIT 261";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     // Gesamtanzahl der Schichten
//     $totalShifts = 261;
    
//     // Berechne die Anzahl der Schichten für jede Maschine
//     $machine1Count = floor($totalShifts * 0.55); // 55% für Maschine 1
//     $machine2Count = floor($totalShifts * 0.40); // 40% für Maschine 2
//     $machine11Count = $totalShifts - $machine1Count - $machine2Count; // Rest für Maschine 11 (5%)
    
//     // Setze den Index für die Maschinen
//     $currentMachine1 = 0;
//     $currentMachine2 = 0;
//     $currentMachine11 = 0;

//     // Update die Maschine für jeden Datensatz
//     while ($row = $result->fetch_assoc()) {
//         $idshift = $row['idshift'];
        
//         // Logik zur Verteilung der Maschinen
//         if ($currentMachine1 < $machine1Count) {
//             $machineId = 1; // Maschine 1
//             $currentMachine1++;
//         } elseif ($currentMachine2 < $machine2Count) {
//             $machineId = 2; // Maschine 2
//             $currentMachine2++;
//         } else {
//             $machineId = 11; // Maschine 11
//             $currentMachine11++;
//         }

//         // Update den Datensatz in der Datenbank
//         $updateSql = "UPDATE shift SET machine_idMachine = ? WHERE idshift = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $stmt->bind_param("ii", $machineId, $idshift); 
//         $stmt->execute();
//     }
//     echo "Die Maschinen wurden erfolgreich verteilt.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();



// include '../connection.php';

// // Hole alle Machinedata-Einträge
// $sql = "SELECT idMachinedata FROM machinedata ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $newId = 1; // Beginne mit der neuen ID 1

//     while ($row = $result->fetch_assoc()) {
//         $idMachinedata = $row['idMachinedata'];

//         // Update den Datensatz mit der neuen ID
//         $updateSql = "UPDATE machinedata SET idMachinedata = ? WHERE idMachinedata = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $stmt->bind_param("ii", $newId, $idMachinedata); 
//         $stmt->execute();

//         // Erhöhe die neue ID
//         $newId++;
//     }
//     echo "Die IDs in der machinedata Tabelle wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();



// include '../connection.php';

// // Hole alle Machinedata-Einträge
// $sql = "SELECT idMachinedata FROM machinedata ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $numEntries = $result->num_rows;
//     $numUserIds = 12; // Anzahl der User-IDs
//     $usedUserIds = []; // Array, um bereits verwendete User-IDs zu speichern
//     $newUserIds = []; // Array für die neuen User-IDs

//     // Generiere einzigartige User-IDs
//     while (count($usedUserIds) < $numUserIds) {
//         $randomUserId = rand(1000, 9999);
//         if (!in_array($randomUserId, $usedUserIds)) {
//             $usedUserIds[] = $randomUserId;
//         }
//     }

//     // Verteile die User-IDs auf die Machinedata-Einträge
//     $index = 0;
//     while ($index < $numEntries) {
//         // Wähle eine zufällige User-ID
//         $userId = $usedUserIds[array_rand($usedUserIds)];
        
//         // Bestimme die Anzahl der Einträge für diesen Benutzer (mindestens 5)
//         $entriesForUser = rand(5, 10);

//         // Stelle sicher, dass wir die Gesamtanzahl der Einträge nicht überschreiten
//         if ($index + $entriesForUser > $numEntries) {
//             $entriesForUser = $numEntries - $index; // Maximal bis zur Gesamtanzahl
//         }

//         // Aktualisiere die User-IDs in der Datenbank für die nächste Gruppe
//         for ($i = 0; $i < $entriesForUser; $i++) {
//             if ($index >= $numEntries) break;

//             $idMachinedata = $result->fetch_assoc()['idMachinedata'];
//             $updateSql = "UPDATE machinedata SET userid = ? WHERE idMachinedata = ?";
//             $stmt = $machineconn->prepare($updateSql);
//             $stmt->bind_param("ii", $userId, $idMachinedata);
//             $stmt->execute();

//             $index++;
//         }
//     }

//     echo "Die User-IDs in der machinedata Tabelle wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// // Definiere die Zeitspanne
// $startDate = new DateTime('2024-09-30');
// $endDate = new DateTime('2024-10-11');
// $interval = new DateInterval('P1D'); // tägliches Intervall
// $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

// // Erstelle ein Array für die Order-IDs
// $orderIds = [];

// // Generiere 24 bis 36 eindeutige achtstellige Order-IDs
// while (count($orderIds) < rand(24, 36)) {
//     $randomOrderId = rand(10000000, 99999999); // 8-stellige Order-IDs
//     if (!in_array($randomOrderId, $orderIds)) {
//         $orderIds[] = $randomOrderId;
//     }
// }

// // Hole alle Machinedata-Einträge
// $sql = "SELECT idMachinedata FROM machinedata ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     $numEntries = $result->num_rows;
//     $ordersPerDay = []; // Um die Verteilung der Bestellungen zu verfolgen

//     // Verteile die Bestellungen über die Tage
//     foreach ($period as $date) {
//         $ordersForDay = rand(2, 3); // 2 bis 3 Bestellungen pro Tag
//         $ordersForDayArray = [];

//         for ($i = 0; $i < $ordersForDay; $i++) {
//             $ordersForDayArray[] = $orderIds[array_rand($orderIds)];
//         }
//         $ordersPerDay[$date->format("Y-m-d")] = $ordersForDayArray;
//     }

//     // Aktualisiere die Order-IDs in der Datenbank
//     $index = 0;
//     $currentOrderId = null;

//     while ($index < $numEntries) {
//         // Wähle eine neue Order-ID aus der Verteilung
//         $dateKey = array_rand($ordersPerDay); // Zufälliges Datum auswählen
//         $currentOrderId = $ordersPerDay[$dateKey][array_rand($ordersPerDay[$dateKey])];

//         // Aktualisiere die Order-ID in der Datenbank
//         $idMachinedata = $result->fetch_assoc()['idMachinedata'];
//         $updateSql = "UPDATE machinedata SET `order` = ? WHERE idMachinedata = ?";
//         $stmt = $machineconn->prepare($updateSql);
//         $stmt->bind_param("ii", $currentOrderId, $idMachinedata);
//         $stmt->execute();

//         $index++;
//     }

//     echo "Die Order-IDs in der machinedata Tabelle wurden erfolgreich aktualisiert.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();



// include '../connection.php';

// // Hole alle Machinedata-Einträge
// $sql = "SELECT idMachinedata FROM machinedata ORDER BY idMachinedata ASC";
// $result = $machineconn->query($sql);

// if ($result->num_rows > 0) {
//     // Aktualisiere die value auf einen zufälligen Wert zwischen 1 und 20 für jeden Eintrag
//     $updateSql = "UPDATE machinedata SET `value` = ? WHERE idMachinedata = ?";
//     $stmt = $machineconn->prepare($updateSql);

//     while ($row = $result->fetch_assoc()) {
//         $randomValue = rand(1, 12); // Zufälliger Wert zwischen 1 und 20
//         $stmt->bind_param("ii", $randomValue, $row['idMachinedata']);
//         $stmt->execute();
//     }

//     echo "Die value in der machinedata Tabelle wurde erfolgreich auf zufällige Werte zwischen 1 und 20 gesetzt.";
// } else {
//     echo "Keine Datensätze gefunden.";
// }

// $machineconn->close();
?>
