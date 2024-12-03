<?php
include('config.php');
include('funktionen.php');
$nst = $_GET['nst'];
if(isset($_GET['command'])) {
  $nummer = $_GET['command'];
  if(!is_numeric($nummer)) {
    $nummer = preg_replace("/[^0-9]/", "", $nummer);
    $nummer = '+'.$nummer;
  }
}
if(isset($_POST['number'])) {
  $nummer = $_POST['number'];
  if(!is_numeric($nummer)) {
    $nummer = preg_replace("/[^0-9]/", "", $nummer);
    $nummer = '+'.$nummer;
  }
}
$query = mysqli_query($db_conn,"SELECT model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model = model.model AND usr_telefon.displayname = '$nst'");
$array = mysqli_fetch_array($query);
$hersteller = $array['hersteller'];
switch($hersteller) {
  case 'mitel':
    $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:$nummer" interruptCall="no"/></AastraIPPhoneExecute>';
    push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
    break;
  case 'yealink':
    $xml = '<YealinkIPPhoneExecute><ExecuteItem URI="Dial:$nummer"/></YealinkIPPhoneExecute>';
    push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
    break;
  case 'snom':
    $ip = phone_ip($nst);
    $password = cell('admin_password_phone','adm_einstellungen',$nst);
    $url = "http://admin:$password@$ip/command.htm?number=$nummer";
    file_get_contents($url);
    break;
}

?>
