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
  $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:*09"/></AastraIPPhoneExecute>';
  push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
?>
