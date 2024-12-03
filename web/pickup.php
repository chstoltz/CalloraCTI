<?php
  include('config.php');
  include('funktionen.php');
  if(isset($_GET['nummer'])) {
    $query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil WHERE sip_user = '$nummer'");
    $array = mysqli_fetch_array($query);
    $nst = $array['nst'];
  }
  if(isset($_GET['nst'])) {
    $nst = $_GET['nst'];
  }
  $query = mysqli_query($db_conn,"SELECT model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model = model.model AND usr_telefon.displayname = '$nst'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];
  switch($hersteller) {
    case 'mitel':
      $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:*09"/></AastraIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      break;
    case 'yealink':
      $xml = '<YealinkIPPhoneExecute><ExecuteItem URI="Dial:*09"/></YealinkIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      break;
    case 'snom':
      $ip = phone_ip($nst);
      $password = cell('admin_password_phone','adm_einstellungen',$nst);
      $url = "http://admin:$password@$ip/command.htm?number=*09";
      file_get_contents($url);
      break;
  }

?>
