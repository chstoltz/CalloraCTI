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
    $endecho = '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
    break;
  case 'yealink':
    $endecho = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI=""/></YealinkIPPhoneExecute>';
    break;
  case 'snom':
    $endecho = '';
    break;
}

$callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
if($rowcount = mysqli_num_rows($callstate) == 0) {
  mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'disconnected', '0')");
} else {
  mysqli_query($db_conn,"UPDATE callstate SET state='disconnected',remotenumber='0' WHERE nst = '$nst'");
}
$query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$nst'");
if(mysqli_num_rows($query)==1) {
  $array = mysqli_fetch_array($query);
  $ip = $array['ip'];
  $command = 'first color green';
  cticlient($ip,$command);
  $command = 'first number L1';
  cticlient($ip,$command);
}
$search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
while($row = mysqli_fetch_array($search_keys)) {
  $ziel = $row['nst'];
  $label = $row['label'];
  $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];
  switch($hersteller) {
    case 'mitel':
      $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$label.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      break;
    case 'yealink':
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
	    $xml='<YealinkIPPhoneConfiguration Beep="no"><Item>linekey.'.$keyno.'.label = '.$label.'</Item></YealinkIPPhoneConfiguration>';
	    push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      break;
    case 'snom':
      $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
      $array = mysqli_fetch_array($query);
      $admin_password_phone = $array['admin_password_phone'];
      $protocol = $array['protocol'];
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOff';
      file_get_contents($url);
      break;
  }
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
echo $endecho;

?>
