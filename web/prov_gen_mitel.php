<?php
include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);
$nst = $_GET['nst'];

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id = 0");
$array = mysqli_fetch_array($query);
extract($array, EXTR_OVERWRITE);

switch($codecs) {
  case 1:
    $codec = 'payload=9;ptime=20;silsupp=off,payload=8;ptime=20;silsupp=off';
    break;
  case 2:
    $codec = 'payload=8;ptime=20;silsupp=off,payload=9;ptime=20;silsupp=off';
    break;
  case 3:
    $codec = 'payload=8;ptime=20;silsupp=off';
    break;
  case 4:
    $codec = 'payload=9;ptime=20;silsupp=off';
    break;
}

$file = $cfg['cnf']['path'].'/prov/startup.cfg';

$config = '';
$config .= "dhcp: 1\n";
$config .= "ipv6: 1\n";
$config .= "dhcp6: 2\n";
$config .= "lldp: 0\n";
$config .= "time server disabled: 0\n";
$config .= "time server1: $ntp_server1\n";
$config .= "time server2: $ntp_server2\n";
$config .= "time server3: $ntp_server3\n";
$config .= "download protocol: HTTPS\n";
$config .= "http server: {$cfg['cnf']['fqdn']}\n";
$config .= "http path: prov\n";
$config .= "tone set: Germany\n";
$config .= "language 1: https://{$cfg['cnf']['fqdn']}/lang/lang_de.txt\n";
$config .= "language: 1\n";
$config .= "input language: German\n";
$config .= "web language: 1\n";
$config .= "tone set: Germany\n";
$config .= "time zone name: DE-Berlin\n";
$config .= "time zone code: CET\n";
$config .= "web interface enabled: $web_interface_enabled\n";
$config .= "admin password: $admin_password_phone\n";
$config .= "firmware server: https://{$cfg['cnf']['fqdn']}/firmware\n";
$config .= "options simple menu: 0\n";
$config .= "options password enabled: $options_password_enabled\n";
$config .= "softkey selection list: conf, directory, dnd, empty, line, list, none, phonelock, services, speeddial, speeddialxfer, xfer, xml, discreetringing\n";
$config .= "speeddial edit: 0\n";
$config .= "far end disconnect timer: 0\n";
$config .= "time format: 1\n";
$config .= "date format: 11\n";
$config .= "backlight mode: 1\n";
$config .= "alert external: 2\n";
$config .= "blf display label to max: 1\n";
$config .= "blf key mode: 1\n";
$config .= "far end disconnect timer: 2\n";
$config .= "sip no rtp timeout: 0\n";
$config .= "idle screen mode: 1\n";
$config .= "collapsed softkey screen: 0\n";
$config .= "collapsed more softkey screen: 1\n";
$config .= "collapsed context user softkey screen: 1\n";
$config .= "sip rtp port: 13000\n";
$config .= "sip out-of-band dtmf: 1\n";
$config .= "sip dtmf method: 1\n";
$config .= "sip registration retry timer: 600\n";
$config .= "sip explicit mwi subscription period: 3600\n";
$config .= "sip dial plan: \"x+^|xx+*\"\n";
$config .= "sip dial plan terminator: 1\n";
$config .= "sip accept out of order requests: 1\n";
$config .= "sip contact matching: 2\n";
$config .= "sip refer-to with replaces: 1\n";
$config .= "sip ignore status code: 603\n";
$config .= "sip srtp mode: 1\n";
$config .= "sip xml notify event: 1\n";
$config .= "sip whitelist: $sip_whitelist\n";
if($ip_whitelist != '') {
  $config .= "ip whiteliste: $ip_whitelist\n";
}
$config .= "sip ignore refer event id: 1\n";
$config .= "sip send mac: 1\n";
$config .= "tos sip: 40\n";
$config .= "tos rtp: 46\n";
$config .= "tos rtcp: 46\n";
if($codec != '') {
  $config .= "sip customized codec: $codec\n";
}
$config .= "ringback timeout: 300\n";
$config .= "sip transport protocol: 0\n";
$config .= "sip session timer: 3600\n";
$config .= "sip rport: 0\n";
$config .= "sip send line: 1\n";
$config .= "emergency dial plan: 112|110\n";
$config .= "tr69 check sync: 0\n";
$config .= "idle screen error messages suppression: 3\n";
$config .= "tagging enabled: 0\n";
$config .= "SIP priority: 3\n";
$config .= "RTP priority: 5\n";
$config .= "RTCP priority: 5\n";
$config .= "QoS eth port 1 priority: 0\n";
$config .= "auto resync mode: 1\n";
$config .= "mobile contacts enabled: 0\n";
$config .= "directory disabled: 0\n";
$config .= "callers list disabled: 0\n";
$config .= "xml application post list: $xml_application_post_list\n";

file_put_contents($file, $config);

$mac = db_cell('mac','usr_telefon',$nst);
$file = $cfg['cnf']['path'].'/prov/'.$mac.'.cfg';
$config = '';
$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
if(mysqli_num_rows($query) != 0) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
  $config .= "sip line1 screen name: $screenname\n";
  $config .= "sip line1 screen name 2: $screenname2\n";
  $config .= "sip line1 user name: $username\n";
  $config .= "sip line1 display name: $displayname\n";
  $config .= "sip line1 auth name: $authname\n";
  $config .= "sip line1 password: $password\n";
  $config .= "sip line1 registration period: $registrationperiod\n";
  $config .= "sip line1 proxy ip: $proxy\n";
  $config .= "sip line1 registrar ip: $registrar\n";
  $config .= "call forward disabled: $call_forward_disabled\n";
  $config .= "idle screen font color: $idle_screen_font_color\n";
  $config .= "dst config: $dst_config\n";
  $config .= "call waiting tone: $call_waiting_tone\n";
  $config .= "mwi led line: $mwi_led_line\n";
  $config .= "missed calls indicator disabled: $missed_calls_indicator_disabled\n";
  $config .= "brightness level: $brightness_level\n";
  $config .= "bl on time: $bl_on_time\n";
  $config .= "inactivity brightness level: $inactivity_brightness_level\n";
  $config .= "screen save time: $screen_save_time\n";
  $config .= "switch focus to ringing line: $switch_focus_to_ringing_line\n";
  $config .= "handset volume: $handset_volume\n";
  $config .= "speaker volume: $speaker_volume\n";
  $config .= "headset volume: $headset_volume\n";
  $config .= "ringer volume: $ringer_volume\n";
  $config .= "audio mode: $audio_mode\n";
  $config .= "line1 ring tone: $line1_ring_tone\n";
  $config .= "contact rcs: 0\n";
  $config .= "sip explicit mwi subscription: $sip_explicit_mwi_subscription\n";
  $config .= "custom ringtone 1: $custom_ringtone_1\n";
  $config .= "callers list script: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=anrufliste\n";
  $config .= "redial script: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=anrufliste&l=a\n";
  $config .= "voicemail script: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=voicemail\n";
  $config .= "directory script: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=telefonbuch\n";
  $config .= "action uri startup: $protocol://{$cfg['cnf']['fqdn']}/web/xml_startup.php?vendor=mitel\n";
  $config .= "action uri incoming: $protocol://{$cfg['cnf']['fqdn']}/web/xml_incoming.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel&remotenumber=\$\$REMOTENUMBER\$\$\n";
  $config .= "action uri connected: $protocol://{$cfg['cnf']['fqdn']}/web/xml_connected.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel&remotenumber=\$\$REMOTENUMBER\$\$\n";
  $config .= "action uri disconnected: $protocol://{$cfg['cnf']['fqdn']}/web/xml_disconnected.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel\n";
  //$config .= "action uri registered: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=registered&ip=\$\$LOCALIP\$\$&linestate=\$\$LINESTATE\$\$\n";
  $config .= "action uri outgoing: $protocol://{$cfg['cnf']['fqdn']}/web/xml_outgoing.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel&remotenumber=\$\$REMOTENUMBER\$\$\n";
  $config .= "action uri registration event: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=regevent&regstate=\$\$REGISTRATIONSTATE\$\$&regcode=\$\$REGISTRATIONCODE\$\$&ip=\$\$LOCALIP\$\$\n";
  $config .= "action uri offhook: $protocol://{$cfg['cnf']['fqdn']}/web/xml_offhook.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel\n";
  $config .= "action uri onhook: $protocol://{$cfg['cnf']['fqdn']}/web/xml_onhook.php?nst=\$\$DISPLAYNAME\$\$&vendor=mitel\n";
  if($cfg['cnf']['polling']==TRUE) {
  $config .= "action uri poll: $protocol://{$cfg['cnf']['fqdn']}/web/xml.php?nst=\$\$DISPLAYNAME\$\$&action=poll&duration=\$\$CALLDURATION\$\$&linestate=\$\$LINESTATE\$\$\n";
  $config .= "action uri poll interval: 1\n";
  }
}
$query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst'");
if(mysqli_num_rows($query) != 0) {
  while($row = mysqli_fetch_array($query)) {
    $key = $row['taste'];
    $value = $row['ziel'];
    $type = $row['type'];
    $label = $row['label'];
    switch($type) {
      case 'blf':
        $config .= "$key type: xml\n";
	      $config .= "$key label: $label\n";
	      $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=call&remotenumber=**$value\n";
        break;
      case 'speeddial':
	      $config .= "$key type: speeddial\n";
	      $config .= "$key label: $label\n";
	      $config .= "$key value: $value\n";
	      break;
      case 'line':
	      $config .= "$key type: line\n";
	      $config .= "$key line: $value\n";
	      $config .= "$key label: $label\n";
	      break;
      case 'mobilelink':
	      $config .= "$key type: mobile\n";
	      break;
      case 'voicemail':
        $config .= "$key type: xml\n";
        $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=voicemail\n";
        $config .= "$key label: $label\n";
        break;
      case 'telefonbuch':
	      $config .= "$key type: xml\n";
	      $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=telefonbuch\n";
	      $config .= "$key label: $label\n";
	      break;
      case 'anrufliste':
        $config .= "$key type: xml\n";
        $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=anrufliste\n";
        $config .= "$key label: $label\n";
        break;
      case 'rvt':
	      $config .= "$key type: dnd\n";
	      $config .= "$key label: $label\n";
	      break;
      case 'kamera':
	      $config .= "$key type: xml\n";
	      $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=webcam\n";
	      $config .= "$key label: $label\n";
	      break;
      case 'tueroeffner':
	      $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
        if(mysqli_num_rows($query) == 1) {
          $array = mysqli_fetch_array($query);
          $wc_url = $array['wc_url'];
	      } else {
          $wc_url = '';
	      }
	      $config .= "$key type: xml\n";
	      $config .= "$key value: $wc_url\n";
	      $config .= "$key label: $label\n";
	      break;
      case 'services':
        $config .= "$key type: xml\n";
        $config .= "$key value: $protocol://{$cfg['cnf']['fqdn']}/web/xml_mitel.php?nst=\$\$DISPLAYNAME\$\$&action=services\n";
        $config .= "$key label: $label\n";
        break;
    }
  }
}
file_put_contents($file, $config);
?>
