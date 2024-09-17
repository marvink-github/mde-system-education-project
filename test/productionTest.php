<?php

$apiKey = '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c';

// file_get_contents("http://127.0.0.1/conni/users/add8761238976");

$output1 = [];
exec("start /B C:\\xampp\\php\\php.exe start_machine.php 1 dj198ma 189023710 $apiKey " . rand(5, 15) . " 2 4 3 3533 2>&1", $output1);
echo "Output für Maschine 1:\n" . implode("\n", $output1) . "\n";

$output2 = [];
exec("start /B C:\\xampp\\php\\php.exe start_machine.php 2 kj245nd 132178412 $apiKey " . rand(5, 15) . " 2 2 1 3533 2>&1", $output2);
echo "Output für Maschine 2:\n" . implode("\n", $output2) . "\n";

$output3 = [];
exec("start /B C:\\xampp\\php\\php.exe start_machine.php 1 lk098qw 823136427 $apiKey " . rand(5, 15) . " 2 4 3 3533 2>&1", $output3);
echo "Output für Maschine 1:\n" . implode("\n", $output3) . "\n";

$output4 = [];
exec("start /B C:\\xampp\\php\\php.exe start_machine.php 2 mn123op 237861471 $apiKey " . rand(5, 15) . " 2 2 1 3533 2>&1", $output4);
echo "Output für Maschine 2:\n" . implode("\n", $output4) . "\n";

echo "Produktionssimulation abgeschlossen.\n";
