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
$xml  = "<AastraIPPhoneExecute>\n";
$xml .= "<ExecuteItem URI=\"Dial:$nummer\" interruptCall=\"no\"/>\n";
$xml .= "</AastraIPPhoneExecute>";
push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
?>
