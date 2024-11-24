<?php
include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);
$nst = $_GET['nst'];
$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname='$nst'");
$array = mysqli_fetch_array($query);
$username = $array['username'];

if(!isset($_GET['reboot'])) {
    $reboot = 'false';
} else {
    $reboot = $_GET['reboot'];
}

$ip = phone_ip($nst);

$notify  = "NOTIFY sip:$username@$ip:5060 SIP/2.0\r\n";
$notify .= "Via: SIP/2.0/UDP {$cfg['cnf']['ip']}:5060;branch=47110815;rport;alias\r\n";
$notify .= "From: sip:callora@{$cfg['fb']['ip']}:5060;tag=89c92b72-c066-41b4-ace9-a310b6df79bb\r\n";
$notify .= "To: sip:$username@$ip:5060\r\n";
//$notify .= "Call-ID: ea2226a5-eb44-41b9-82a7-25d28f8e783@192.168.23.1\r\n";
$notify .= "Call-ID: 1234@{$cfg['fb']['ip']}\r\n";
$notify .= "CSeq: 10 NOTIFY\r\n";
$notify .= "Contact: sip:callora@{$cfg['fb']['ip']}:5060\r\n";
$notify .= "Content-Length: 0\r\n";
$notify .= "Max-Forwards: 70\r\n";
$notify .= "User-Agent: CalloraCTI 1.0\r\n";
$notify .= "Event: check-sync;reboot=$reboot\r\n\r\n";

$len = strlen($notify);
$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_bind($s, $cfg['cnf']['ip'], 5060);
socket_connect($s, $ip, 5060);
socket_sendto($s, $notify, $len, 0, $ip, 5060);
socket_close($s);

?>
