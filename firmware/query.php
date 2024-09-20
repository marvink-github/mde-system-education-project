<?php

  # Directory with extracted Firmware achives and their associated
  # MD5 Fingerprints
  $ex_fwdir = "C:/xampp/htdocs/api/firmware/files/";

  /*
   * Script for querying available firmware files and versions
   * 
   * Parameter:
   *   fw=latest
   *   fw=branch,04.03.19     or     fw=branch,04.03.18
   *   fw=version,04.03.19.18
   *   fw=list
   *
   * Answer of the script:
   * The query script produces system message alike answers with reasons 38xx:
   *
   * 3800<filename of dfz file with firmware>
   * 3801,unsupported query mode
   * 3802,no match
   * 3803,internal error
   * 
   * (c) Datafox GmbH
   * Author: S. Meyer
   * Date:   24.10.2022
   */

  $fwfiles=array();

  $mode = $_REQUEST["fw"];

  // Derived mode abbreviation:
  // 'l' = latest, 'b' = branch, 'v' = version, 'i' = list/info
  $m="";

  $branch_selector="";
  $version_selector="";

  if ( strcmp( $mode, "latest" ) == 0 )
  {
    $m="l";
  }
  elseif ( strcmp( substr( $mode, 0, 7 ), "branch," ) == 0 )
  {
    $m="b";
    $branch_selector=substr( $mode, 7 );
  }
  elseif ( strcmp( substr( $mode, 0, 8 ), "version," ) == 0 )
  {
    $m="v";
    $version_selector=substr( $mode, 8 );
  }
  elseif ( strcmp( $mode, "list" ) == 0 )
  {
    $m="i";
  }
  else
  {
      exit( "df_api=1&type=2&reason=3801&group=380&detail1=unsupported query mode" );
  }

  // Compute firmware containers available...
  if (is_dir($ex_fwdir))
  {
    if ($dh = opendir($ex_fwdir))
    {
      while (($file = readdir($dh)) !== false)
      {
        if ( strcmp( substr( $file, -4 ), ".dfz" ) == 0 )
        {
          // Firmware container
          array_push( $fwfiles, $file );
          // print( $file."<br>" );
        }
        else
        {
          // normal file, ignored
        }
      }
      closedir($dh);
    }
  }

  // Depending on the mode abbreviation, determine the correct firmware version
  switch ( $m )
  {
  case "l": // Find the latest version
    arsort( $fwfiles );
    $fw = array_shift( $fwfiles );
    if ( strlen( $fw ) > 0 )
    {
      exit( "df_api=1&type=1&reason=3800&group=380&detail1=$fw" );
    }
    exit( "df_api=1&type=2&reason=3802&group=380&detail1=no match" );
    break;

  case "b": // Match a certain prefix for the latest...
    $subarray=array();
    while ( $fwfile=array_shift( $fwfiles ) )
    {
      if ( strcmp( $branch_selector, 
                   substr( $fwfile, 0,strlen( $branch_selector ) ) ) == 0 )
      {
          array_push( $subarray, $fwfile );
      }
      else
      {
        // print( "NO MATCH OF $branch_selector and $fwfile <br>" );
      }
    }
    // Debugging: Dump available firmware versions...
    // print_r( $subarray );

    arsort( $subarray );
    $fw = array_shift( $subarray );
    if ( strlen( $fw ) > 0 )
    {
      exit( "df_api=1&type=1&reason=3800&group=380&detail1=$fw" );
    }
    exit( "df_api=1&type=2&reason=3802&group=380&detail1=no match" );
    break;

  case "v":
    // Match the exact version.
    while ( $fwfile=array_shift( $fwfiles ) )
    {
      $fileversion = substr( $fwfile, 0, strlen( $fwfile ) - 4 );
      if ( strcmp( $version_selector, $fileversion ) == 0 )
      {
        exit( "df_api=1&type=1&reason=3800&group=380&detail1=$fwfile" );
      }
    }
    exit( "df_api=1&type=2&reason=3802&group=380&detail1=no match" );
    break;

  case "i":
    arsort( $fwfiles );
    print( "df_api=1" );
    while ( $fwfile=array_shift( $fwfiles ) )
    {
      print( "&dfz=$fwfile" );
    }
    exit( "" );
    break;

  default:
    break;
  }

  exit( "df_api=1&type=2&reason=3803&group=380&detail1=internal error" );
?>
