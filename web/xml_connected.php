<?php
include('config.php');
include('funktionen.php');

if(isset($_GET['nummer'])) {
  $nummer = $_GET['nummer'];
  $query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil WHERE sip_user = '$nummer'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];
} elseif(isset($_GET['mac'])) {
  $mac = $_GET['mac'];
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE mac = '$mac'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];
} else {
  $nst = $_GET['nst'];
}
if(isset($_GET['vendor'])) {
  $vendor = $_GET['vendor'];
}
switch($vendor) {
  case 'mitel':
    $remotenumber = $_GET['remotenumber']; 
    $endecho = '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
    break;
  case 'yealink':
    $remotenumber = $_GET['remotenumber']; 
    $remotenumber = explode('@', substr($remotenumber,4));
    $remotenumber = $remotenumber[0];
    $endecho = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI=""/></YealinkIPPhoneExecute>';
    break;
  case 'snom':
    $remotenumber = $_GET['remotenumber']; 
    $remotenumber = explode('@', $remotenumber);
    $remotenumber = $remotenumber[0];
    $endecho = '';
    break;
}
$intern = substr($remotenumber, 2);
    
$callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
if($rowcount = mysqli_num_rows($callstate) == 0) {
  mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'connected', '$remotenumber')");
} else {
  mysqli_query($db_conn,"UPDATE callstate SET state='connected',remotenumber='$remotenumber' WHERE nst = '$nst'");
}
$query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$nst'");
if(mysqli_num_rows($query)==1) {
  $array = mysqli_fetch_array($query);
  $ip = $array['ip'];
  $command = 'first color red';
  cticlient($ip,$command);
  $command = 'first number '.$remotenumber;
  cticlient($ip,$command);
}
$search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
while($row = mysqli_fetch_array($search_keys)) {
  $ziel = $row['nst'];
  $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];
  switch($hersteller) {
    case 'mitel':
      $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      break;
    case 'yealink':
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_RED=on"/></YealinkIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      break;
    case 'snom':
      $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
      $array = mysqli_fetch_array($query);
      $admin_password_phone = $array['admin_password_phone'];
      $protocol = $array['protocol'];
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOn%26color%3DRED';
      file_get_contents($url);
      break;
  }
  $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$ziel'");
  if(mysqli_num_rows($query)==1) {
    $array = mysqli_fetch_array($query);
    $ip = $array['ip'];
    $command = $nst. ' color red';
    cticlient($ip,$command);
  }
}
echo $endecho;
?>
