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



include '../connection.php';

$sql = "SELECT idMachinedata FROM machinedata WHERE idMachinedata BETWEEN 1 AND 2003 ORDER BY idMachinedata ASC";
$result = $machineconn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $randomValue = rand(1, 10);

        // Update den Wert in der Datenbank
        $updateSql = "UPDATE machinedata SET value = ? WHERE idMachinedata = ?";
        $stmt = $machineconn->prepare($updateSql);
        $stmt->bind_param("ii", $randomValue, $row['idMachinedata']); 
        $stmt->execute();
    }
    echo "Werte wurden erfolgreich aktualisiert.";
} else {
    echo "Keine Datensätze gefunden.";
}

$machineconn->close();
?>
