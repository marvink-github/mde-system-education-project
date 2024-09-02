<?php

$machinehost = "localhost"; 
$machineusername = "root"; 
$machinepassword = "root"; 
$machinedb = "machinedb_v3";

$machineconn = mysqli_connect($machinehost, $machineusername, $machinepassword, $machinedb);

if(mysqli_connect_errno()) {
    die("Connection failed: " . mysqli_connect_error());
}
