<?php
//---------------------------------------------------------------------------------------------------------------------------------
//
// die mit GET gesendeten Parameternamen und die zugehörigen Parameterwerte in das 
// Array "$valarray" kopieren
//
function ParseUrlVariable(&$valarray)
{
if(sizeof($_GET) != 0)
  {
   foreach($_GET as $getKey=>$getVal)
     {
      $valarray["$getKey"] = $getVal;
     }
  }
}
//---------------------------------------------------------------------------------------------------------------------------------

ParseUrlVariable($valarray);   // kopieren der gesendeten Werte nach $valarray
$pruefsum = 0;                 // Variable $pruefsum mit 0 initialisieren
$sendtext = "";                // Variable $sendtext mit leerem String initialisieren
$sendtextRaw = "";             // Variable $sendtext mit leerem String initialisieren
//$sendtextRaw2 = "";             // Variable $sendtext mit leerem String initialisieren
$returntext = "";              // Variable $returntext mit leerem String initialisieren
$to = "sd";                      // Variable $to mit leerem String initialisieren
//$returntextmail = "";          // Variable $returntextmail mit leerem String initialisieren
$mymonat = "";                 // Variable $mymonat mit leerem String initialisieren
$tmpTime = "";                 // Variable $tmpTime mit leerem String initialisieren
$tmpDU = "";                   // Variable $tmpDU mit leerem String initialisieren
$timestamp = "";               // 
$buf = "";
$DUinc10 = "";
$DUTime = "600";



// Datei im Exelformat  GPRSLogs
$handle = fopen ("./" . strftime ("%Y-%m-%d", time ()) . ".txt", "at");
//$handle = fopen ("./" . strftime ("%Y-%m-%d", time ()) . ".txt", "at");
fwrite($handle, strftime("%H:%M:%S", time ()) . "\t");

// Datei im Luxusformat
//$handle2 =  fopen ("./" . strftime ("%Y-%m-%d", time ()) . "Ext.txt", "at");
//fwrite($handle2, strftime("%H:%M:%S: ", time ()));

//---------------------------------------------------------------------------------------------------------------------------------
if(count($valarray) > 0)       // Überprüfung ob im Array Elemente vorhanden sind
  {  
   foreach($valarray as $key=>$val) 
     {
     	// Fuer Exel 
     	if(($key != "checksum"))
     	{ 
     	$sendtextRaw = $sendtextRaw . "$key=$val\t";
	}
     	// Standard
     	//$sendtextRaw2 = $sendtextRaw2 . "$key=$val; ";
     	
      $sendtext = $sendtext . "$key =\t";
      if(strlen($key)<8) $sendtext = $sendtext . "\t";
      if(strlen($key)<16) $sendtext = $sendtext . "\t";
      $sendtext = $sendtext . "$val\n";  
      
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  
      // wenn $Key gleich "DU" dann Wert nach $tmpDU kopieren strftime ("%Y-%m-%d_%H:%M:%S", time ());
      if($key == "DU")
        {
         $tmpDU = $val;
         $tmpDU = strtotime( str_replace("_"," ",$val));
         $DUinc10 =$tmpDU;
         
         $tmpDU = strtotime(strftime ("%Y-%m-%d %H:%M:%S", time ())) - $tmpDU;
         $DUinc10 = $DUinc10 + 600; //strtotime("%S",600);   
         //echo "Der String ($tmpDU) ";     
        }
      
      
      
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~  
      // wenn $Key gleich "email" dann Adresse nach $to kopieren 
      if($key == "email")
        {
         $to= $val;   
        }
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      // wenn $Key gleich "monat" dann Wert nach $mymonat kopieren 
      if($key == "monat")
        {
         $mymonat= $val;   
        }
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      
      
      
      
       
      
      
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~	
      // überprüfen ob der Key nicht die checksum ist! 
      if(($key != "checksum")) 
        {         
         // Addition jedes einzelnen ASCII-Werts zur Prüfsumme
    	 for($i=0; $i<strlen($val); $i++) 
    	   {
    	    // neuen Wert der Prüfsumme durch Addition des 
    	    // aktuellen Zeichenwerts berechnen
    	    $pruefsum=$pruefsum+ord($val[$i]);
  	   }			
  	}	
      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~	
  	
  	
  	
  	
     }
  }
//---------------------------------------------------------------------------------------------------------------------------------
//$akttime = strftime ("%Y-%m-%d_%H:%M:%S", time ());
$akttime = strftime ("%Y-%m-%d_%H:%M:%S", $DUinc10);

//---------------------------------------------------------------------------------------------------------------------------------
if( ($valarray["checksum"]==$pruefsum))
  { 
   
   //-------------------------------------------------------------------------------------------------
   if( ($mymonat!= ""))
     {
      $tmpTime = $mymonat . "_23:58:00"; 
      $returntext = "status=ok&checksum=" . $pruefsum . "&time=" . $tmpTime;
      
      print $returntext;
     }
     else
      {
       $returntext = "status=ok&checksum=" . $pruefsum . "&time=" . $akttime;
        
       print $returntext;
      }
   //--------------------------------------------------------------------------------------------------
   
  }
  else
  { 
   $returntext = "status=error&checksum=$pruefsum";
    
   print $returntext;
  }
// --------------------------------------------------------------------------------------------------------------------------------  

  
// Fuer Exel
fwrite($handle, $tmpDU . "\t" . $sendtextRaw .  "\n");

// Standard
//fwrite($handle2, $subject . "\n\t[" . $sendtextRaw2 . "]\n\t[" . $returntext . "]\n\n");

  
  fclose($handle );
 // fclose($handle2);
//------------------------------------------------------------------------------------------------
?>

