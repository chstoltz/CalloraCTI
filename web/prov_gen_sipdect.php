<?php
include('config.php');
include('funktionen.php');

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen");
$array = mysqli_fetch_array($query);

if($array['ntp_server1'] != '') { $ntp_server1 = 'ntpServerName1="'.$array['ntp_server1'].'"' ;} else { $ntp_server1 = 'ntpServerName1="ptbtime1.ptb.de"'; }
if($array['ntp_server2'] != '') { $ntp_server2 = 'ntpServerName2="'.$array['ntp_server2'].'"' ;} else { $ntp_server2 = 'ntpServerName1="ptbtime2.ptb.de"'; }
if($array['ntp_server3'] != '') { $ntp_server3 = 'ntpServerName3="'.$array['ntp_server3'].'"' ;} else { $ntp_server3 = ''; }

$file = $cfg['cnf']['path'].'/prov/ipdect.cfg';
$config = '';

$config .= '<SetSystemUpdate swActAllAtOnce="true" />';
$config .= '<SetSoftwareImageURL plainText="1" ><url enable="1" protocol="HTTPS" username="" password="" host="'.$cfg['cnf']['fqdn'].'" path="/firmware" port="0" useCommonCerts="0" /></SetSoftwareImageURL>';
$config .= '<SetSystemUpdate swActTrigger="Automatic"/>';
$config .= '<SetSystemUpdate timedUpdate="true" hour="23" minute="30" maxDelay="5" />';
$config .= '<SetSystemUpdateTrigger triggerType="Restart" cfgUpdate="true" swUpdate="true" sipCerts="true" errWaitTime="30" retryDelay="10" retryCount="1"><files rfpSW="Optional" ommCfg="Optional" userCommonCfg="Optional" sipCerts="Optional" /></SetSystemUpdateTrigger>';
$config .= '<SetSystemUpdateTrigger triggerType="SipNotify" cfgUpdate="true" swUpdate="true" sipCerts="true" errWaitTime="30" retryDelay="10" retryCount="1"><files rfpSW="Optional" ommCfg="Optional" userCommonCfg="Opional" sipCerts="Optional" /></SetSystemUpdateTrigger>';
$config .= '<SetSystemUpdateTrigger triggerType="ScheduledUpdate" cfgUpdate="true" swUpdate="false" sipCerts="true" errWaitTime="30" retryDelay="10" retryCount="1"><files ommCfg="Optional" userCommonCfg="Optional" sipCerts="Optional" /></SetSystemUpdateTrigger>';
$config .= '<SetEULAConfirm confirm="1" />';
$config .= '<SetAccount plainText="1" ><account id="1" password="'.$cfg['omm']['pass'].'" active="1" aging="none" /></SetAccount>';
$config .= '<SetAccount plainText="1" ><account id="2" password="'.$cfg['omm']['root'].'" active="1" aging="none" /></SetAccount>';
$config .= '<SetSysToneScheme toneScheme="DE" />';
$config .= '<SetSystemName name="'.$cfg['omm']['systemname'].'" />';
$config .= '<SetRegistrationTrafficShaping maxRegistrations="1" timeout="30" spreadRegRenew="1">';
$config .= '<SetSite><site id="1" name="'.$cfg['omm']['systemname'].'" wideband="1" dectSecurity="1" /></SetSite>';
$config .= '<SetDECTRegDomain regDomain="EMEA" />';
$config .= '<SetWLANRegDomain regDomain="DE" />';
$config .= '<SetPreserveUserDeviceRelation enable="1" />';
$config .= '<SetConfigURL><url enable="true" protocol="HTTPS" host="'.$cfg['cnf']['fqdn'].'" path="/prov" /></SetConfigURL>';
$config .= '<SetUserDataServer useCommonFileNameOnServer="1"><url enable="true" protocol="HTTPS" host="'.$cfg['cnf']['fqdn'].'" path="/prov" /></SetUserDataServer>';
$config .= '<SetTimeZone id="CET" />';
$config .= '<SetPPFirmwareUpdate enable="true" />';
$config .= '<SetBasicSIP transportProt="UDPandTCP" proxyServer="'.$cfg['omm']['proxy'].'" proxyPort="5060" regServer="'.$cfg['omm']['registrar'].'" regPort="5060" regPeriod="3600" gruu="false" />';
$config .= '<SetAdvancedSIP mwiSubscription="true" userAgentInfo="true" xAastraId="false" callRejectStateCodeUsr="603" callRejectStateCodeDev="499" referToWithReplaces="1" />';
$config .= '<SetSuplServ callForwDiv="false" locLineHndlg="true" releaseInfoTimerFailedCall="10" />';
$config .= '<SetDECTPpSettings dialByNumberOnly="true" />';
$config .= '<SetIntercomCallHandlingSIP microphoneMute="false" />';
$config .= '<SetIntercomCallHandlingSIP warningTone="false" />';
$config .= '<SetRTP><codec type="G.722" /><codec type="G.711-A-law" /></SetRTP>';
$config .= '<SetNTPServer '.$ntp_server1.' '.$ntp_server2.' '.$ntp_server3.' />';
$config .= '<SetRemoteAccess enable="1" />';

// Telefonbücher
$config .= '<SetCorporateDirectory plainText="1" replaceData="1">';
$config .= '<directory id="1" active="true" name="Telefonbuch" type="XML" searchType="SN" displayType="SN, GN" timeout="10" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" port="0" path="web/xml_mitel.php?action=decttelefonbuch&nummer={number}&sipdect=1" username="" password="" />';
$config .= '</directory>';
$config .= '<directory id="2" active="true" name="Suche" type="XML" searchType="SN" displayType="SN, GN" timeout="10" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" port="0" path="web/xml_mitel.php?action=decttelefonbuch&nummer={number}&suche=1&sipdect=1" username="" password="" />';
$config .= '</directory>';
$config .= '</SetCorporateDirectory>';
// XML APPS
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="0" name="callerList" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_mitel.php?&action=anrufliste&nummer={number}&sipdect=1" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="1" name="redialList" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_mitel.php?action=anrufliste&l=a&nummer={number}&sipdect=1" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="4" name="eventActions" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_sipdect.php?nummer={number}&na={subsc}&pp={ppn}&pa1={sichaon}&pa2={sichaoff}&pa3={boot}&pa4={reg}&pa5={dereg}&pa6={onho}&pa7={offho}&pa8={in}&pa9={out}&pa10={det}&pa11={sip}&pa12={con}&pa13={dis}&pa14={rege}" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="6" name="callCompletion" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_sipdect.php?nummer={number}&na={subsc}&pp={ppn}&src={sipsrc}&dest={sipdest}" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="9" name="pickup" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/pickup.php?nummer={number}" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<SetXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" id="14" name="voiceBox" type="BuiltIn" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_mitel.php?action=voicemail&nummer={number}&sipdect=1" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</SetXMLApplication>';
$config .= '<CreateXMLApplication plainText="1" replaceData="1">';
$config .= '<xmlAppl enable="1" name="Services" type="Dynamic" >';
$config .= '<url protocol="HTTP" host="'.$cfg['cnf']['fqdn'].'" path="web/xml_mitel.php?action=services&nummer={number}" username="" password="" />';
$config .= '</xmlAppl>';
$config .= '</CreateXMLApplication>';

file_put_contents($file, $config);

?>
