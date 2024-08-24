<?php

function postMachine($terminal_id, $terminal_type){
    $url = "http://localhost/api/machine/post.php";
  
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "Content-Type: application/json"
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  
    $data = json_encode(array(
      "terminal_id" => $terminal_id,
      "terminal_type" => $terminal_type,
    ));
  
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 

    $resp = curl_exec($curl);
  
    if(curl_errno($curl)) {
      $error_msg = curl_error($curl);
      curl_close($curl);
      return json_encode(["error" => $error_msg]);
    }
    curl_close($curl);
  
    return JSON_decode($resp, true);
  } 
  

  // <?php

  // $url = "http://localhost/api/machine/post.php";
  
  // $data = [
  //     "t_id" => "3533",
  //     "t_type" => "Test"
  // ];
  
  // $ch = curl_init($url);
  
  // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
  // curl_setopt($ch, CURLOPT_POST, true); 
  // curl_setopt($ch, CURLOPT_HTTPHEADER, [
  //     'Content-Type: application/json' 
  // ]);
  // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
  
  // $response = curl_exec($ch);
  
  // if (curl_errno($ch)) {
  //     echo 'cURL Error: ' . curl_error($ch);
  // } else {
  //     echo $response;
  // }
  
  // curl_close($ch);
  
  // 
?> 
