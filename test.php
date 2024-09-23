<?php

// include 'connection.php';
// include 'functions.php';

// $latestFirmware = getLatestFirmwareVersion();

// $terminal_id = '3533';
// $terminal_type = 'EVO 4.6 FlexKey';
// $currentFirmware = getFirmwareFromDevice($machineconn, $terminal_id, $terminal_type);

// $result = isUpdateRequired($currentFirmware, $latestFirmware);
// echo $result;

// if (!empty($currentFirmware)) {
//     $latestFirmware = getLatestFirmwareVersion();            
//     if (isUpdateRequired($currentFirmware, $latestFirmware)) {
//         $download_url = "http://127.0.0.1/api/firmware/files/$latestFirmware";         
//         echo "df_api=1&df_load_firmware=" . rawurlencode($download_url);
//         logDB($machineconn, 'firmware', 'Firmware updated to latest.');
//         exit;
//     } else {
//         logDB($machineconn, 'firmware', 'Latest firmware installed, no update required');
//     }
// } else {
//     echo 'df_api=1&df_kvp=firmwareversion';
//     logDB($machineconn, 'firmware', 'No firmware version found.');
// }