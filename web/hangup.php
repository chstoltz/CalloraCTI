<?php
include('config.php');
include('funktionen.php');
$nst = $_GET['nst'];
$xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></AastraIPPhoneExecute>';
push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
?>
