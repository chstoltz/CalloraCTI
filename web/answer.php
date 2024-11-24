<?php
include('config.php');
include('funktionen.php');
$xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Headset"/></AastraIPPhoneExecute>';
push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
?>
