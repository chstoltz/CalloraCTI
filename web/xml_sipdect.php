<?php
include('config.php');
include('funktionen.php');
extract($_GET, EXTR_OVERWRITE);
$query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil WHERE sip_user = '$nummer'");
$array = mysqli_fetch_array($query);
$nst = $array['nst'];
$server = $_SERVER['SERVER_ADDR'];
/*
na   = SIP User, entspricht Benutzername IP Telefon Fritz!Box
ppn  = interne ID, 1 erstes MT, 2 zweites MT ...
pa1  = Silent Charge on
pa2  = Silent Charge off
pa3  = Boot beendet
pa4  = (Registrierung erfolgt)
pa5  = (De-Registrierung erfolgt)
pa6  = (Hörer aufgelegt)
pa7  = (Hörer abgenommen)
pa8  = (eingehendes Gespräch)
pa9  = (ausgehendes Gespräch)
pa10 = detach
pa11 = SIP Notify
pa12 = (Verbindung hergestellt (aktives Gespräch))
pa13 = (Gespräch beendet)
pa14 = (Registrierungsereignis)
*/
// Registrierung
if($pa4 == 1) {
  $omm_query = mysqli_query($db_conn,"SELECT * FROM adm_sipdect");
  $omm_array = mysqli_fetch_array($omm_query);
  $omm_ip = $omm_array['omm_ip'];
  $regevent = mysqli_query($db_conn,"SELECT * FROM regevent WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($regevent) == 0) {
    mysqli_query($db_conn,"INSERT INTO regevent (nst, regstate, regcode, ip) VALUES ('$nst', 'REGISTERED', '200', '$omm_ip')");
  } else {
    mysqli_query($db_conn,"UPDATE regevent SET regstate='REGISTERED',regcode='200',ip='$omm_ip' WHERE nst = '$nst'");
  }
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'IDLE', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='IDLE',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// De-Registrierung
if($pa5 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'IDLE', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='IDLE',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:inactive"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// on hook
if($pa6 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'onhook', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='onhook',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    $label = $row['label'];
    push2phone($server,phone_ip($ziel),$xml);
    $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$label.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
    push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
    if(mysqli_num_rows($query)==1) {
      $array = mysqli_fetch_array($query);
      $ip = $array['ip'];
      $command = $nst. ' color green';
      cticlient($ip,$command);
      $command = $nst. ' number '.$label;
      cticlient($ip,$command);
    }
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// off hook
if($pa7 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'offhook', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='offhook',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);

    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
    if(mysqli_num_rows($query)==1) {
      $array = mysqli_fetch_array($query);
      $ip = $array['ip'];
      $command = $nst. ' color red';
      cticlient($ip,$command);
    }
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// eingehend
if($pa8 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'incoming', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='incoming',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=fastflash:yellow"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);

    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
    if(mysqli_num_rows($query)==1) {
      $array = mysqli_fetch_array($query);
      $ip = $array['ip'];
      $command = $nst. ' color yellow';
      cticlient($ip,$command);
  }
  }
}
// ausgehend
if($pa9 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'outgoing', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='outgoing',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);

    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
    if(mysqli_num_rows($query)==1) {
      $array = mysqli_fetch_array($query);
      $ip = $array['ip'];
      $command = $nst. ' color red';
      cticlient($ip,$command);
    }
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// hergestellt
if($pa12 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'connected', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='connected',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// aufgelegt
if($pa13 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'disconnected', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='disconnected',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);

    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
    if(mysqli_num_rows($query)==1) {
      $array = mysqli_fetch_array($query);
      $ip = $array['ip'];
      $command = $nst. ' color red';
      cticlient($ip,$command);
    }
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}
// regevent
if($pa14 == 1) {
  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  if($rowcount = mysqli_num_rows($callstate) == 0) {
    mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'IDLE', '0')");
  } else {
    mysqli_query($db_conn,"UPDATE callstate SET state='IDLE',remotenumber='0' WHERE nst = '$nst'");
  }
  $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
  while($row = mysqli_fetch_array($search_keys)) {
    $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
    $ziel = $row['nst'];
    push2phone($server,phone_ip($ziel),$xml);
  }
  echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
}

?>
