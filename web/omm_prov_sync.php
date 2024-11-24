<?php
include('config.php');
include('funktionen.php');

$sipdectnotify  = "NOTIFY sip:{$cfg['omm']['ip']}:5060 SIP/2.0\r\n";
$sipdectnotify .= "Via: SIP/2.0/UDP {$cfg['cnf']['ip']}:5060;branch=47110815;rport;alias\r\n";
$sipdectnotify .= "From: sip:calloracti@{$cfg['omm']['registrar']}:5060;tag=89c92b72-c066-41b4-ace9-a310b6df79bb\r\n";
$sipdectnotify .= "To: sip:phone@{$cfg['omm']['ip']}:5060\r\n";
$sipdectnotify .= "Call-ID: ea2226a5-eb44-41b9-82a7-25d28f8e783@192.168.23.1\r\n";
$sipdectnotify .= "CSeq: 1 NOTIFY\r\n";
$sipdectnotify .= "Contact: sip:calloracti@{$cfg['omm']['registrar']}:5060\r\n";
$sipdectnotify .= "Content-Length: 0\r\n";
$sipdectnotify .= "Max-Forwards: 70\r\n";
$sipdectnotify .= "User-Agent: CalloraCTI 1.0\r\n";
$sipdectnotify .= "Event:prov-sync\r\n\r\n";

$len = strlen($sipdectnotify);
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_bind($sock, $cfg['cnf']['ip'], 5060);
socket_connect($sock, $cfg['omm']['ip'], 5060);
socket_sendto($sock, $sipdectnotify, $len, 0, $cfg['omm']['ip'], 5060);
socket_close($sock);

?>
