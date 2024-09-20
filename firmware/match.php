<?php

  # Directory with extracted Firmware achives and their associated
  # MD5 Fingerprints
  $ex_fwdir = "C:/xampp/htdocs/api/firmware/files/";
  $g_verbose = false;
  $g_ign_md5 = false;

  // Define global variable, so they do produce warnings
  $g_par_fw = "";
  $g_par_devtype = "";
  $g_par_serno = "";
  $g_par_board = "";
  $g_par_md5 = "";
  $g_par_hwmodules = "";
  $g_par_current_fw = "";

  $g_devType2CpuId = array(
    "0" => "49002",
    "5" => "49001",
    "8" => "49002",
    "10" => "49001",
    "11" => "49001",
    "14" => "49001",
    "15" => "49001",
    "16" => "49001",
    "17" => "49001",
    "18" => "49001",
    "19" => "49001",
    "20" => "49002",
    "21" => "49002",
    "23" => "49001",
    "24" => "49002",
    "25" => "49003",
    "34" => "49004",
    "35" => "49001",
    "36" => "49001" );

  /*
   * Script for matching a firmware version against a set of hardware elements 
   * provided by a device.
   * 
   * Sample Usage:
   *
   * /match.php?
   * kv=serialnumber,8675&kv=device,23&
   * kv=setup EVO 2.8.aes,0x12345678&
   * kv=cert test.cert,0x087654321&
   * kv=firmwareversion,04.03.15.05.EVO35&
   * kv=board,50006,4.7a&
   * kv=module,29,1.5c,14&
   * kv=module,37,1.4c,6&
   * kv=module,102018,1.2b,6.1&
   * kv=module,11,1.5a,8&
   * kv=module,30,1.0b,11&
   * kv=module,110003,1.1c,11.1&
   * kv=fw,04.03.19.19.dfz,<md5>
   * 
   * Answer of the script:
   * The match script produces system message alike answers with reasons 39xx:
   *
   * 3900,<Name of DFZ file>,<Name of IFF file>
   * 3901,unhandled parameter,<Parameter>
   * 3902,device type missing
   *
   * If not run from a web server, the script may be invoked from the commandline, e.g. by
   *   php -f match.php <args> <query-string>
   * e.g.
   *   php -f match.php "kv=firmwareversion,04.03.20.03.Evo43&kv=board,50007,4.4a& \
   *        kv=module,102026,1.0a,0.11&kv=module,104003,1.0a,0.12&kv=module,12,1.2a,1&\
   *        kv=module,35,1.3a,2&kv=module,1,1.3j,6&kv=module,11,1.6b,7&kv=module,10,1.1c,8& \
   *        kv=module,106002,1.0a,8.1&kv=module,106001,1.0a,8.2&kv=module,106001,1.0a,8.3& \
   *        kv=module,106004,1.0a,8.4&kv=module,19,1.3a,9&kv=module,110004,1.0a,9.1& \
   *        kv=module,110101,1.0c,9.2&kv=module,20,1.3a,18&kv=device,11&kv=serialnumber,1234&
   *        kv=setup EVO 4.3 F1 Datensatz F2 SysMsg.aes,0AF95295& \
   *        kv=fw,04.03.20.03.dfz,8d3343de9a00cb36e7617e66ace126d8"
   * will result in
   *   df_api=1&type=1&reason=3900&group=390&detail1=04.03.20.03.dfz&detail2=evo4.3_04.03.20.03.iff&detail3=
   * 
   * Arguments may be
   * --verbose
   * --ignore-md5
   * --fwdir <directory>
   *
   * (c) Datafox GmbH
   * Author: S. Meyer
   * Date:   04.01.2023
   */

  
  function genSysMsg( $p_type, $p_reason, $p_reasonGroup, $p_detail1, $p_detail2, $p_detail3 )
  {
    return "df_api=1&type=".$p_type."&reason=".$p_reason."&group=".$p_reasonGroup."&detail1=".$p_detail1."&detail2=".$p_detail2."&detail3=".$p_detail3;
  }

  function handleError( $fn, $errorCode, $errorGroup, $detail1, $detail2, $detail3, $fatal )
  {
    global $g_verbose;
    $sysMsg = genSysMsg( 2, $errorCode, $errorGroup, $detail1, $detail2, $detail3 );
    if ( $fatal )
    {
      exit( $sysMsg );
    }

    if ( $g_verbose )
    {
      print $fn.": ".$sysMsg."\n";
    }
    return false;
  }

  function handleInfo( $fn, $reasonCode, $reasonGroup, $detail1, $detail2, $detail3, $exit_ )
  {
    global $g_verbose;
    $sysMsg = genSysMsg( 1, $reasonCode, $reasonGroup, $detail1, $detail2, $detail3 );

    if ( $exit_ )
    {
      exit( $sysMsg );
    }

    if ( $g_verbose )
    {
      print $fn.": ".$sysMsg."\n";
    }
  }

  // Helper Function: Dump an array hexadecimally
  function printHex( $data )
  {
    print( "<pre>" );
    $idx = 0;
    $len = strlen( $data );
    while ( $idx < $len )
    {
       print( sprintf( "0x%08X: ", $idx ) );
       for ( $off = 0; $off < 16; $off += 1 )
       {
          if ( $idx + $off < $len )
          {
            $num = ord( $data[ $idx + $off ] );
            print( sprintf( "%02X ", $num ) );
          }
          else
          {
            print( "   " );
          }
       }
       print( " | " );
       for ( $off = 0; $off < 16; $off += 1 )
       {
          if ( $idx + $off < $len )
          {
            $num = ord( $data[ $idx + $off ] );
            if ( $num < 32 )
            {
              print( " " );
            }
            else
            {
              print( $data[ $idx + $off ] );
            }
          }
       }
       $idx += 16;
       print( "<br>" );
    }
    print( "</pre>" );
  }

  // Parser for IFF files: Extract a 32bit integer
  function iffGet32bitInt( $data, $offset )
  {
      return ( ( ord( $data[ $offset + 0 ] ) * 256 + ord( $data[ $offset + 1 ] ) ) * 256 + 
               ord( $data[ $offset + 2 ] ) ) * 256 + ord( $data[ $offset + 3 ] );
  }

  // Parser for IFF files: Extract a 16bit integer
  function iffGet16bitInt( $data, $offset )
  {
      return ord( $data[ $offset + 0 ] ) * 256 + ord( $data[ $offset + 1 ] );
  }

  // Reads a file analysing the IFF structure. Returns a chunk's content
  // Supply "-print" as $path_to_extract if you want to get a structure dump
  function parseIffFile( $file, $cont_pos, $path_to_extract, $iffpath )
  {
    while ( true )
    {
      $chunkid = fread( $file, 4 );
      $chunklen = fread( $file, 4 );
      if ( ( strlen( $chunkid ) != 4 ) || ( strlen( $chunklen ) != 4 ) )
      {
        break;
      }

      $chklen_int = iffGet32bitInt( $chunklen, 0 );

      $pos_after_chunk = ftell( $file ) + $chklen_int;

      // print( "CHUNK ID ".$chunkid.", len=".strlen($chunkid)."<br>" );
      // print( "CHUNK SIZE ".$chunklen.", len=".strlen($chunklen)." - ".$chklen_int."<br>" );
      // print( "POS AFTER THIS CHUNK: ".$pos_after_chunk."<br>" );
      if ( strcmp( $chunkid, "FORM" ) == 0 )
      {
        // Chunk is a form... get the FORM ID and scan for other IFF segments
        $formid = fread( $file, 4 );

        if ( strcmp( $path_to_extract, "-print" ) == 0 )
        {
            print( "- FORM ".$iffpath.".FORM ".$formid.", size ".$chklen_int."<br>" );
        }

        // print( $iffpath.".FORM ".$formid.", SIZE ".$chklen_int."<br>" );
        return parseIffFile( $file, $pos_after_chunk, $path_to_extract, $iffpath.".FORM ".$formid );
      }
      else
      {
        $chunkdata = fread( $file, $chklen_int & 1 ? $chklen_int + 1 : $chklen_int );
        if ( strcmp( $path_to_extract, "-print" ) == 0 )
        {
            print( "- CHUNK ".$iffpath.".CHUNK ".$chunkid.", size ".$chklen_int."<br>" );
        }
        elseif ( strcmp( $path_to_extract, $iffpath.".CHUNK ".$chunkid ) == 0 )
        {
          fseek( $file, $cont_pos );
          return $chunkdata;
        }
        // print( $iffpath.".CHUNK ".$chunkid.", SIZE ".$chklen_int."<br>" );
      }
    }
    fseek( $file, $cont_pos );
    // print( "CONT POS $cont_pos, pos now ".ftell( $file )."<br>" );
    return "";
  }

  // Translates a version information string (1.2b) into a two number array (12 2)
  function versionString2versionArray( $verString )
  {
    if ( strlen( $verString ) != 4 )
    {
      return handleError( "", 3914, 390, "invalid module version string", $verString, "", true );
    }
    $ver = $verString[ 0 ] * 10 + $verString[ 2 ];
    $sli = ord( $verString[ 3 ] );
    if ( $sli >= 65 && $sli <= 90 )
    {
       $sli = $sli - 64;
    }
    elseif ( $sli >= 97 && $sli <= 122 )
    {
       $sli = $sli - 96;
    }
    else
    {
      return handleError( "", 3914, 390, "invalid module version string", $verString, "", true );
    }
   
    $arr = array();
    array_push( $arr, $ver );
    array_push( $arr, $sli );
    return $arr;
  }

  // Check if a firmware is compatible with the hardware of a device
  function checkIffFileCompatibility( $iffFileName, $arrHw )
  {
    global $g_par_fw, $g_par_devtype, $g_par_serno, $g_par_board, $g_par_md5, $g_par_hwmodules;
    global $g_verbose;

    # print( $iffFieName."<br>" );
    # print_r( $arrHw );
    # print( "<br>" );

    // Has a firmware file been located?
    if ( 0 == strlen( $iffFileName ) )
    {
      return handleError( $g_par_fw, 3910, 390, "no iff file found", "", "", false );
    }

    // Read IFF file...
    $file = fopen( $iffFileName, "r" );
    // parseIffFile( $file, filesize( $iffFileName ), "-print", "" );

    fseek( $file, 0 );
    $iffdata_comp = parseIffFile( $file, filesize( $iffFileName ), ".FORM DFIF.FORM DFF0.CHUNK COMP", "" );
    fseek( $file, 0 );
    $iffdata_faux = parseIffFile( $file, filesize( $iffFileName ), ".FORM DFIF.FORM DFF0.CHUNK FAUX", "" );
    fclose( $file );

    // print "IFF DATA COMP SEGMENT: ".strlen( $iffdata_comp )."<br>";
    // print "IFF DATA FAUX SEGMENT: ".strlen( $iffdata_faux )."<br>";
    // printHex( $iffdata_faux );

    // Check Version of FAUX Chunk
    if ( strlen( $iffdata_faux ) < 2 )
    {
       return handleError( $g_par_fw, 3912, 390, "no acceptable aux info", "chunk not found", "", false );
    }
    if ( iffGet16bitInt( $iffdata_faux, 0 ) != 1 ) 
    {
      return handleError( $g_par_fw, 3912, 390, "no acceptable aux info", "", "", false );
    }

    // FAUX: Check if list of compatible device type ids contains the device type id passed as parameter
    $numDeviceIds = iffGet16bitInt( $iffdata_faux, 2 );
    $idx = 0;
    while ( $idx < $numDeviceIds )
    {
      $nDevId = iffGet16bitInt( $iffdata_faux, 4 + 2 * $idx );
      if ( $nDevId == $g_par_devtype )
      {
        break;
      }
      // print( "Incompatible Dev. ".$nDevId."<br>" );
      $idx++;
    }
    if ( $idx == $numDeviceIds )
    {
      return handleError( $g_par_fw, 3913, 390, "device type mismatch", "expected ".$g_par_devtype, "", false );
    }

    // Check Version of COMP Chunk
    if ( iffGet16bitInt( $iffdata_comp, 0 ) != 2 ) 
    {
      return handleError( $g_par_fw, 3911, 390, "no acceptable compatibility info", "", "", false );
    }

    $cntHardware = count( $arrHw );
    $arrResult = array();
    for ( $idx = 0; $idx < $cntHardware; ++$idx )
    {
      array_push( $arrResult, 0 );
    }

    // Analyse COMP Chunk
    $offs = 2;
    $compat_data_len = strlen( $iffdata_comp );
    while ( $offs < $compat_data_len )
    {
      $fw_idx = iffGet32bitInt( $iffdata_comp, $offs );
      $cnt_entries = iffGet16bitInt( $iffdata_comp, $offs + 4 );
      $offs += 6;
    
      $entryidx = 0;
      while ( $entryidx < $cnt_entries )
      {
        $pfrom = ord( $iffdata_comp[ $offs + 0 ] );
        $sfrom = ord( $iffdata_comp[ $offs + 1 ] );
        $puntil = ord( $iffdata_comp[ $offs + 2 ] );
        $suntil = ord( $iffdata_comp[ $offs + 3 ] );
  
        $verfrom = sprintf( "%u.%u%c", ( $pfrom / 10 ), ( $pfrom % 10 ), $sfrom + 96 );
        $verto = sprintf( "%u.%u%c", ( $puntil / 10 ), ( $puntil % 10 ), $suntil + 96 );
        $offs += 4;
        $entryidx += 1;
        // print( "Supporting FW IDX $fw_idx : $verfrom - $verto ($pfrom $sfrom $puntil $suntil, chunk offset $offs)<br>" );
  
        for ( $idxHardware = 0; $idxHardware < $cntHardware; ++$idxHardware )
        {
          $arr = $arrHw[ $idxHardware ];
          if ( $arr[ 0 ] == $fw_idx ) 
          {
            if ( $g_verbose )
            {
              print( "checking FW IDX $fw_idx : $verfrom - $verto ($pfrom $sfrom $puntil $suntil, chunk offset $offs), DEVICE " );
              print_r( $arr );
              print( "<br>" );
            }
            $mod_ver = $arr[ 1 ] * 100 + $arr[ 2 ];
            $min_ver = $pfrom * 100 + $sfrom;
            $max_ver = $puntil * 100 + $suntil;
            if ( $g_verbose )
            {
              print( $min_ver." <= ".$mod_ver." <= ".$max_ver."<br>" );
            }
            if ( ( $min_ver <= $mod_ver ) && ( $mod_ver <= $max_ver ) )
            {
              $arrResult[ $idxHardware ] = 1;
              if ( $g_verbose )
              {  
                print( "MATCH!<br>" );
              }
            }
          }
        }
      }
    } 

    // Check if all modules are supported...
    for ( $idxHardware = 0; $idxHardware < $cntHardware; ++$idxHardware )
    {
      if ( $arrResult[ $idxHardware ] == 0 )
      {
        //print( "Module ".$arrHw[ $idxHardware ][ 3 ]." is not supported.<br>" );
        return handleError( "", 3914, 390, "unsupported hardware", $arrHw[ $idxHardware ][ 3 ], "", false );
      }
    }

    // All HW modules are supported by the IFF, so let's deliver it...

    // print( "FIRMWARE FILE: $iffFileName<br>" );
    // print( "DEVTYPE: $g_par_devtype<br>" );
    // print( "SERNO: $g_par_serno<br>" );
    // print( "BOARD: $g_par_board<br>" );
    // print( "FW: $g_par_fw / ".basename( $iffFileName )." / $g_par_md5<br>" );
    // print_r( $g_par_hwmodules ); 
  
    // print( "<br><br>" );
    // print_r( $arrResult );
    // print( "<br><br>" );
    return true;
  }


  // Start of main()
  $req="";
  if ( array_key_exists( "QUERY_STRING", $_SERVER ) )
  {
    $req=$_SERVER["QUERY_STRING"];
  }
  if ( 0 == strlen( $req ) )
  {
    for ( $idx = 1; $idx < $argc; ++$idx )
    {
      if ( strcmp( $argv[ $idx ], "--verbose" ) == 0 )
      {
        $g_verbose = true;
      }
      elseif ( strcmp( $argv[ $idx ], "--ignore-md5" ) == 0 )
      {
        $g_ign_md5 = true;
      }
      elseif ( ( strcmp( $argv[ $idx ], "--fwdir" ) == 0 ) && ( $idx + 1 < $argc ) )
      {
        ++$idx;
        $ex_fwdir=$argv[ $idx ];
      }
      else
      {
        $req = $argv[$idx];
      }
    }
  }

  if ( $g_verbose )
  {
    print "ANALYSING REQUEST:\n$req\n\n";
  }

  // The built-in URL parser does not handle identical url parameter names well.
  // So, the request string is parsed manually from the server's context:
  $token = strtok($req, "&");
  $g_par_hwmodules=array();

  while ($token !== false)
  {
    $val = urldecode( $token );
    if ( $g_verbose )
    {
      print "... processing ".$val."\n";
    }

    // Strip leading "kv=" from URL tokens
    if ( strcmp( substr( $val, 0, 3 ), "kv=" ) == 0 )
    {
        $val = substr( $val, 3 );
    }

    // Analyse data passed along with the request
    if ( 0 == strcmp( "board,", substr( $val, 0, 6 ) ) )
    {
      // Hardware ID of Board
      $g_par_board = substr( $val, 6 );
      if ( $g_verbose )
      {
        print "...    board parameter: $g_par_board\n";
      }
    }
    elseif ( 0 == strcmp( "cert ", substr( $val, 0, 5 ) ) )
    {
      // Certificate file

      // ... is ignored
      if ( $g_verbose )
      {
        print "...    ignored\n";
      }
    }
    elseif ( 0 == strcmp( "device,", substr( $val, 0, 7 ) ) )
    {
      // Device Type Id
      $g_par_devtype = substr( $val, 7 );
      if ( $g_verbose )
      {
        print "...    device type id parameter: $g_par_devtype\n";
      }
    }
    elseif ( 0 == strcmp( "firmwareversion,", substr( $val, 0, 16 ) ) )
    {
      // Firmware Version currently running on the device
      $g_par_current_fw = substr( $val, 16 );
      if ( $g_verbose )
      {
        print "...    current firmware: $g_par_current_fw\n";
      }
    }
    elseif ( 0 == strcmp( "fw,", substr( $val, 0, 3 ) ) )
    {
      // Desired firmware version incl. DFZ's MD5
      $npos = strpos( $val, ",", 3 );
      if ( $npos != false )
      {
        $g_par_fw = substr( $val, 3, $npos - 3 );
        $g_par_md5 = substr( $val, $npos + 1 );
      }
      if ( $g_verbose )
      {
        print "...    firmware parameter: $g_par_fw with MD5 $g_par_md5\n";
      }
    }
    elseif ( 0 == strcmp( "module,", substr( $val, 0, 7 ) ) )
    {
      // Hardware Module data built into the device
      $hw_module = substr( $val, 7 );
      array_push( $g_par_hwmodules, $hw_module );
      if ( $g_verbose )
      {
        print "...    hw module parameter: $hw_module\n";
      }
    }
    elseif ( 0 == strcmp( "serialnumber,", substr( $val, 0, 13 ) ) )
    {
      // Serial Number of device
      $g_par_serno = substr( $val, 13 );
      if ( $g_verbose )
      {
        print "...    serial no parameter: $g_par_serno\n";
      }
    }
    elseif ( 0 == strcmp( "setup ", substr( $val, 0, 6 ) ) )
    {
      // Setup currently running on the device

      // ... is ignored
      if ( $g_verbose )
      {
        print "...    ignored\n";
      }
    }
    elseif ( 0 == strcmp( "verbose", $val ) )
    {
      $g_verbose = true;
    }
    else
    {
      if ( $g_verbose )
      {
        print "...    unhandled parameter: $val. aborting.\n";
      }
      return handleError( "", 3901, 390, "unhandled parameter", $val, "", true );
    }

    $token = strtok("&");
  }

  if ( is_dir( $ex_fwdir ) == false )
  { 
    return handleError( "", 3915, 390, "firmware directory not existing", $ex_fwdir, "", true );
  }

  // Check that all relevant parameters have been supplied!
  if ( strlen( $g_par_devtype ) == 0 )
  {
    return handleError( "", 3902, 390, "device type missing", "", "", true );
  }
  if ( strlen( $g_par_serno ) == 0 )
  {
    return handleError( "", 3903, 390, "serial number missing", "", "", true );
  }
  if ( strlen( $g_par_board ) == 0 )
  {
    return handleError( "", 3904, 390, "board missing", "", "", true );
  }
  if ( strlen( $g_par_fw ) == 0 )
  {
    return handleError( "", 3905, 390, "no firmware version specified", "", "", true );
  }
  if ( count( $g_par_hwmodules ) == 0 )
  {
    return handleError( "", 3906, 390, "no hardware modules specified", "", "", true );
  }

  if ( strcmp( "04.03.20.", substr( $g_par_current_fw, 0, 9 ) ) == 0 )
  {
    if ( $g_verbose )
    {
      print "04.03.20.xx firmware on device detected. Deriving MPU from device type.\n";
    }

    if ( $g_devType2CpuId[ $g_par_devtype ] )
    {
      $cpu = $g_devType2CpuId[ $g_par_devtype ].",1.0a,0";
      if ( $g_verbose )
      {
        print "...    Adding MPU module " . $cpu . " as HW requirement\n";
      }
      array_push( $g_par_hwmodules, $cpu );
    }
    else
    {
      return handleError( "", 3916, 390, "failed to derive MPU", $g_par_devtype, "", true );
    }
  }

  if ( $g_ign_md5 == false )
  {
    // Read precomputed MD5 Hash of firmware archive
    $fn = $ex_fwdir.$g_par_fw.".md5";
    if ( is_readable( $fn ) == false )
    {
      return handleError( $g_par_fw, 3907, 390, "md5 fingerprint missing", "", "", true );
    }

    // Open Pre-Computed MD5 Fingerprint
    $file = fopen( $fn, "r" );
    $md5_prec = fread( $file, "32" );
    fclose( $file );

    // Check precomputed MD5 fingerprint
    if ( strcasecmp( $md5_prec, $g_par_md5 ) )
    {
      return handleError( $g_par_fw, 3908, 390, "md5 fingerprint wrong", "", "", true );
    }
  }

  // Assemble array of arrays of ( fw-idx, pcb-version, assembly-idx, orig. text )
  // example:
  // 
  // https://datafox.de/mus/match.php?...&kv=board,50006,4.7a&kv=module,29,1.5c,14&kv=module,37,1.4c,6&kv=module,102018,1.2b,6.1&kv=module,11,1.5a,8&kv=module,30,1.0b,11&kv=module,110003,1.1c,11.1&...
  // 
  // leads to
  //
  // Array (
  //       [0] => Array ( [0] => 50006 [1] => 47 [2] => 1 [3] => 50006,4.7a )
  //       [1] => Array ( [0] => 29 [1] => 15 [2] => 3 [3] => 29,1.5c,14 )
  //       [2] => Array ( [0] => 37 [1] => 14 [2] => 3 [3] => 37,1.4c,6 )
  //       [3] => Array ( [0] => 102018 [1] => 12 [2] => 2 [3] => 102018,1.2b,6.1 )
  //       [4] => Array ( [0] => 11 [1] => 15 [2] => 1 [3] => 11,1.5a,8 )
  //       [5] => Array ( [0] => 30 [1] => 10 [2] => 2 [3] => 30,1.0b,11 )
  //       [6] => Array ( [0] => 110003 [1] => 11 [2] => 3 [3] => 110003,1.1c,11.1 )
  //       )
  $arrHw = array();

  // Array as long as the $arrHw containing 0 for unsupported module or 1 for supported module

  $pos1 = strpos( $g_par_board, "," );
  $arr = versionString2versionArray( substr( $g_par_board, $pos1 + 1 ) );
  array_unshift( $arr, substr( $g_par_board, 0, $pos1 ) );
  array_push( $arr, $g_par_board );
  array_push( $arrHw, $arr );

  $cntHwModules = count( $g_par_hwmodules ); 
  for ( $idx = 0; $idx < $cntHwModules; ++$idx )
  {
    $mod = $g_par_hwmodules[ $idx ];
    $pos1 = strpos( $mod, "," );
    $pos2 = strpos( $mod, ",", $pos1 + 1 );

    $fwidx = substr( $mod, 0, $pos1 );
    $modvers = substr( $mod, $pos1+1, $pos2 - $pos1 - 1 );

    $arr = versionString2versionArray( $modvers );
    array_unshift( $arr, $fwidx );
    array_push( $arr, $mod );
    array_push( $arrHw, $arr );
  }
  // print( "device hardware:" );
  // print_r( $arrHw );
  // print( "<br><br>" );

  // Find firmware IFF file...
  $reqFwDir = $ex_fwdir.$g_par_fw;
  $checkIffFile = "";
  $arrFiles = array();
  if ( $dh = opendir( $reqFwDir ) )
  {
    while (($file = readdir($dh)) !== false)
    {
      if ( strcmp( substr( $file, -4 ), ".iff" ) == 0 )
      {
        // Firmware container
        if ( $g_verbose )
        {
          print( "considering file ".$file.": " );
        }
        $checkIffFile = $reqFwDir."/".$file;
        $res = checkIffFileCompatibility( $checkIffFile, $arrHw );
        if ( $g_verbose )
        {
          print " ::> ".( $res ? "FOUND" : "---" )."<br>\n";
        }
        if ( $res )
        {
          if ( $g_verbose )
          {
            print "MATCH ".$checkIffFile."<br><br>";
          }
          array_push( $arrFiles, $file );
        }
      }
      else
      {
        // normal file, ignored
      }
    }
    closedir( $dh );
  }

  if ( count( $arrFiles ) == 1 )
  {
    handleInfo( "", 3900, 390, $g_par_fw, $arrFiles[ 0 ], "", true );
  }
  
  // print_r( $arrFiles );
  // print( "<br><br>" );
  handleError( "", 3910, 390, "no iff file found", "", "", true );

