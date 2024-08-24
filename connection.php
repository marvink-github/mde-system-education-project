<?php

$machinehost = "localhost"; 
$machineusername = "root"; 
$machinepassword = "root"; 
$machinedb = "machinedb";

$machineconn = mysqli_connect($machinehost, $machineusername, $machinepassword, $machinedb);

if(mysqli_connect_errno()) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

?>

