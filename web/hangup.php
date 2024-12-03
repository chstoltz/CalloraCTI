<?php
include('config.php');
include('funktionen.php');
$nst = $_GET['nst'];
$query = mysqli_query($db_conn,"SELECT model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model = model.model AND usr_telefon.displayname = '$nst'");
$array = mysqli_fetch_array($query);
$hersteller = $array['hersteller'];
switch($hersteller) {
  case 'mitel':
    $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></AastraIPPhoneExecute>';
    push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
    break;
  case 'yealink':
    $xml = '<YealinkIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></YealinkIPPhoneExecute>';
    push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
    break;
  case 'snom':
    $ip = phone_ip($nst);
    $password = cell('admin_password_phone','adm_einstellungen',$nst);
    $url = "http://admin:$password@$ip/command.htm?key=CANCEL";
    file_get_contents($url);
    break;
}

?>
