<?php

/*
   Inspired by Badmoon labs back in 1998 thanks to David Raufeisen( https://twitter.com/fortyoz ) aka fortyoz aka r0rschach
   Adopted in 1998 by Gjermund G Thorsen, beware of nested tables and inline phtml
   Released in 2017 due to a conversation with Dennis Efremov for inspiration only
   This peace of software is for inspiration / learning only, and comes with no warranty what so ever.
*/

error_reporting( E_ERROR );
$SYS_ADMIN = "alert@domain.tld";        // Notifying sender.
$TIMEOUT = 1;                          // Delay time in seconds for cancelling connection.

$allServicesOK = 1;
$downListHeader = "URI\tservice";
$downList[] = $downListHeader;

$hosts[] = 'servicename IP port alert@domain.tld';

// You can add as many as you wish to $hosts[] -list...

function showHosts() {

  global $SYS_ADMIN, $TIMEOUT, $hosts, $downList, $allServicesOK;

  $hostHeader  = "\t\t\t\t<TR>\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" width="3" class="darkgreen">I/O</td>'."\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" class="darkgreen">URI</td>'."\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" class="darkgreen">service</td>'."\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" width="3" class="darkgreen">I/O</td>'."\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" class="darkgreen">URI</td>'."\n"
               . "\t\t\t\t\t".'<td bgcolor="white" align="left" class="darkgreen">service</td>'."\n"
               . '</TR>';
   
  echo $hostHeader;
  
  $counter = 1;
  foreach( $hosts as $key => $value ) {
    list( $service, $hostname, $port, $notify ) = split( ' ', $value, 4 );

  $OK = showHost( $service, $hostname, $port, $counter, $notify );

  if( $OK != 1 ) {
    $downList[] = $hostname.':'.$port."\t".$service;
    $allServicesOK = 0;
  }
  $counter++;
}

echo $hostHeader;

if( $notify and $allServicesOK == 0 ) {
  $date = getdate();

  $thedate =       'Time of report: '
                 . $date['weekday'] . ' '
                 . $date['mday']    . '. '
                 . $date['month']   . ', '
                 . $date['hours']   . ':'
                 . $date['minutes'] . ':'
                 . $date['seconds'];

  $mailheaders =   'From: '
                  . $SYS_ADMIN . "\n"
                  . 'Reply-To: '
                  . $SYS_ADMIN . "\n"
                  . 'X-Mailer: PHP/' . phpversion();

  $subject = 'One or more services on your system has been down.';

  $body    = '';

  foreach ( $downList as $key => $value ) {
    $body .= $value."\n";
  }

  $body .= '---' . "\n"
         . 'Reported by a page on your php powered Apache server' . "\n"
         . 'This is an automated message, do NOT reply, unless you whip up some intentional code.';
  mail( $notify, $subject, $body, $mailheaders );
  }
}

function showHost( $service, $hostname, $port, $counter, $notify = 0 ) {

  global $SYS_ADMIN, $TIMEOUT;
  $fp = fsockopen( $hostname, $port, $errno, $errstr, $TIMEOUT );

  $text=$hostname.':'.$port;

  if( $fp ) {
    $OK=1;
    $color="darkgreen";
    $ioColor="white";
    $ioText='1';
    fclose($fp);
  } else {
    $OK=0;
    $color="maroon";
    $ioColor="maroon";
    $ioText='O';
  }

  $pos = strpos( $_SERVER["REMOTE_ADDR"], '10.0.0.' ); // this is the part of your LAN that is entitled to view the actual IPS and services

  if( $pos === false ) {
    if( $_SERVER["REMOTE_ADDR"] == "182.0.66.42" ) { // this is for additional IPs being allowed to watch the actual services being monitored.
      $text = $hostname.':'.$port;
    } else {
      $text = 'service info - access denied( '.$_SERVER["REMOTE_ADDR"].' )';
    }
  } else {
    $text = $hostname.':'.$port;
  }

  $hostOutput .= '<TD bgcolor="'.$color.'" align="center" width="3" class="'.$ioColor.'">'.$ioText.'</TD>'."\n\t\t\t\t\t";
  $hostOutput .= '<TD bgcolor="white" align="left" class="'.$color.'">'.$text.'</TD>'."\n\t\t\t\t\t";
  $hostOutput .= '<TD bgcolor="white" align="left" class="'.$color.'">'.$service.'</TD>'."\n\t\t\t\t";

  if( $counter % 2 == 0 ) {
    $hostOutput .= '</TR><TR>';
  }

  echo $hostOutput;

  return $OK;
}?>
<HTML>
  <TITLE>System Status</TITLE>
  <link rel=StyleSheet href="/css/index.css" type="text/css">
<HEAD>
</HEAD>
<BODY bgcolor="white">
<?php require_once( "../../header/index.php" );?>
<TABLE ALIGN="center" CELLSPACING=1 CELLPADDING=3 BORDER=0>
  <TR>
    <TD BGCOLOR="navy">
      <TABLE CELLSPACING=1 CELLPADDING=3 BORDER=0>
        <TR>
          <TD bgcolor="white" align="center" COLSPAN=6 class="headline2">System Status</TD>
        </TR>
        <TR>
          <TD bgcolor="white" align="center" COLSPAN=6 class="headline2"><?echo date("H:i @ d. F Y");;?></TD>
        </TR>
        <TR><?php showHosts();?></TR>
        <TR>
          <TD bgcolor="white" align="center" COLSPAN=6 class="headline2"><?echo date("H:i @ d. F Y");;?></TD>
        </TR>
        <TR>
          <TD bgcolor="white" align="center" COLSPAN=6 class="headline2">System Status</TD>
        </TR>
      </TABLE>
    </TD>
  </TR>
</TABLE>
<?php require_once( "../../footer/index.php" );?>
</BODY>
</HTML>
