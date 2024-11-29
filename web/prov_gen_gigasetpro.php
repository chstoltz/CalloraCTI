<?php

include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);
$nst = $_GET['nst'];

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id = 0");
$array = mysqli_fetch_array($query);
extract($array, EXTR_OVERWRITE);

$provfile = $cfg['cnf']['path'].'/prov/$mac.cfg';

$provisioning = new SimpleXMLElement('<provisioning/>');
$provisioning->addAttribute('version', '1.1');
$provisioning->addAttribute('productID', 'e2');

$firmware = $provisioning->addChild('firmware', NULL);
$file = $firmware->addChild('file', NULL);
$file->addAttribute('version', 'x.xx');
$file->addAttribute('url', 'https......');

$nvm = $provisioning->addChild('nvm', NULL);

// Param-Elemente für Telephony und Provisioning hinzufügen
$param1 = $nvm->addChild('param');
$param1->addAttribute('name', 'Telephony.0.ToneScheme');
$param1->addAttribute('value', 'Germany');

$param2 = $nvm->addChild('param');
$param2->addAttribute('name', 'Provisioning.global.ProvisioningServer');
$param2->addAttribute('value', 'http://profile.gigaset.net/device');

$param3 = $nvm->addChild('param');
$param3->addAttribute('name', 'Telephony.0.CT_ViaRKey');
$param3->addAttribute('value', '1');

$param4 = $nvm->addChild('param');
$param4->addAttribute('name', 'Telephony.0.CT_ByOnHook');
$param4->addAttribute('value', '1');

$param5 = $nvm->addChild('param');
$param5->addAttribute('name', 'DmGlobal.0.HSIdleDisplay');
$param5->addAttribute('value', '1');

// NTP-Server und TimeZone hinzufügen
$param6 = $nvm->addChild('param');
$param6->addAttribute('name', 'DmGlobal.0.NtpServer');
$param6->addAttribute("value", "$ntp_server1,$ntp_server2,$ntp_server3");

$param7 = $nvm->addChild('param');
$param7->addAttribute('name', 'DmGlobal.0.TimeZone');
$param7->addAttribute('value', 'Europe/Berlin');

// Handset 1 (029e74a599)
$oper1 = $nvm->addChild('oper');
$oper1->addAttribute('value', '029e74a599');
$oper1->addAttribute('name', 'add_hs');
$param8 = $oper1->addChild('param');
$param8->addAttribute('name', 'hs.RegStatus');
$param8->addAttribute('value', 'ToReg');

// SipAccount für Handset 1
$param9 = $nvm->addChild('param');
$param9->addAttribute('name', 'SipAccount.029e74a599.AuthName');
$param9->addAttribute('value', '1013');

$param10 = $nvm->addChild('param');
$param10->addAttribute('name', 'SipAccount.029e74a599.AuthPassword');
$param10->addAttribute('value', '1234567');

$param11 = $nvm->addChild('param');
$param11->addAttribute('name', 'SipAccount.029e74a599.UserName');
$param11->addAttribute('value', '1013');

$param12 = $nvm->addChild('param');
$param12->addAttribute('name', 'SipAccount.029e74a599.DisplayName');
$param12->addAttribute('value', '1013');

$param13 = $nvm->addChild('param');
$param13->addAttribute('name', 'SipAccount.029e74a599.ProviderId');
$param13->addAttribute('value', '0');

$param14 = $nvm->addChild('param');
$param14->addAttribute('name', 'hs.029e74a599.DirectAccessDir');
$param14->addAttribute('value', '0');

$param15 = $nvm->addChild('param');
$param15->addAttribute('name', 'hs.029e74a599.DECT_AC');
$param15->addAttribute('value', '0000');

// Handset 2 (02b6ec8aa5)
$oper2 = $nvm->addChild('oper');
$oper2->addAttribute('value', '02b6ec8aa5');
$oper2->addAttribute('name', 'add_hs');
$param16 = $oper2->addChild('param');
$param16->addAttribute('name', 'hs.RegStatus');
$param16->addAttribute('value', 'ToReg');

// SipAccount für Handset 2
$param17 = $nvm->addChild('param');
$param17->addAttribute('name', 'SipAccount.02b6ec8aa5.AuthName');
$param17->addAttribute('value', '1014');

$param18 = $nvm->addChild('param');
$param18->addAttribute('name', 'SipAccount.02b6ec8aa5.AuthPassword');
$param18->addAttribute('value', '7654321');

$param19 = $nvm->addChild('param');
$param19->addAttribute('name', 'SipAccount.02b6ec8aa5.UserName');
$param19->addAttribute('value', '1014');

$param20 = $nvm->addChild('param');
$param20->addAttribute('name', 'SipAccount.02b6ec8aa5.DisplayName');
$param20->addAttribute('value', '1014');

$param21 = $nvm->addChild('param');
$param21->addAttribute('name', 'SipAccount.02b6ec8aa5.ProviderId');
$param21->addAttribute('value', '0');

$param22 = $nvm->addChild('param');
$param22->addAttribute('name', 'hs.02b6ec8aa5.DirectAccessDir');
$param22->addAttribute('value', '0');

$param23 = $nvm->addChild('param');
$param23->addAttribute('name', 'hs.02b6ec8aa5.DECT_AC');
$param23->addAttribute('value', '0000');

// VoIP Provider Einstellungen
$param24 = $nvm->addChild('param');
$param24->addAttribute('name', 'SipProvider.0.Name');
$param24->addAttribute('value', 'Fritz!Box');

$param25 = $nvm->addChild('param');
$param25->addAttribute('name', 'SipProvider.0.Domain');
$param25->addAttribute('value', 'fritz.box');

$param26 = $nvm->addChild('param');
$param26->addAttribute('name', 'SipProvider.0.ProxyServerAddress');
$param26->addAttribute('value', $proxy_ip);

$param27 = $nvm->addChild('param');
$param27->addAttribute('name', 'SipProvider.0.ProxyServerPort');
$param27->addAttribute('value', '5060');

$param28 = $nvm->addChild('param');
$param28->addAttribute('name', 'SipProvider.0.RegServerAddress');
$param28->addAttribute('value', $registrar_ip);

$param29 = $nvm->addChild('param');
$param29->addAttribute('name', 'SipProvider.0.RegServerPort');
$param29->addAttribute('value', '5060');

// Weitere Einstellungen hinzufügen (z. B. DNS, Failover, Network)
$param30 = $nvm->addChild('param');
$param30->addAttribute('name', 'SipProvider.0.DnsQuery');
$param30->addAttribute('value', '0');

$param31 = $nvm->addChild('param');
$param31->addAttribute('name', 'SipProvider.0.FailoverServerEnabled');
$param31->addAttribute('value', '0');

$param32 = $nvm->addChild('param');
$param32->addAttribute('name', 'SipProvider.0.OutboundProxyMode');
$param32->addAttribute('value', '2');

$param33 = $nvm->addChild('param');
$param33->addAttribute('name', 'SipProvider.0.OutboundProxyAddress');
$param33->addAttribute('value', '');

$param34 = $nvm->addChild('param');
$param34->addAttribute('name', 'SipProvider.0.OutboundProxyPort');
$param34->addAttribute('value', '5060');

$param35 = $nvm->addChild('param');
$param35->addAttribute('name', 'MWISubscription');
$param35->addAttribute('value', '1');





file_put_contents($provfile, $provisioning->asXML());

?>