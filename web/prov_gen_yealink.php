<?php
include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);
$nst = $_GET['nst'];

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id = 0");
$array = mysqli_fetch_array($query);
extract($array, EXTR_OVERWRITE);

$mac = db_cell('mac','usr_telefon',$nst);
$file = $cfg['cnf']['path'].'/prov/'.strtolower($mac).'.cfg';

//Allgemein
$config = "#!version:1.0.0.1\n\n";
$config .= "static.security.user_name.admin = admin\n";
$config .= "static.security.user_password = admin:$admin_password_phone\n";
$config .= "static.firmware.url = https://{$cfg['cnf']['fqdn']}/firmware\n";
$config .= "lang.wui = German\n";
$config .= "lang.gui = German\n";
$config .= "local_time.date_format = 5\n";
$config .= "local_time.ntp_server1 = $ntp_server1\n";
$config .= "local_time.ntp_server2 = $ntp_server2\n";
$config .= "local_time.time_zone = +1\n";
$config .= "local_time.time_zone_name = Germany(Berlin)\n";
$config .= "local_time.summer_time = $dst_config\n";
$config .= "push_xml.server = $xml_application_post_list\n";
$config .= "push_xml.sip_notify = 1\n";
$config .= "voice.tone.country = Germany\n";
$config .= "features.config_dsskey_length = 1\n";
$config .= "features.shorten_linekey_label.enable = 1\n"; 
if($call_forward_disabled == 1) {
  $config .= "features.fwd.allow = 0\n";
} else {
  $config .= "features.fwd.allow = 1\n";
}
if($missed_calls_indicator_disabled == 1) {
  $config .= "features.missed_call_popup.enable = 0\n";
} else {
  $config .= "features.missed_call_popup.enable = 1\n";
}
$config .= "call_waiting.enable = $call_waiting_tone\n";
$config .= "phone_setting.active_backlight_level = $brightness_level\n";
$config .= "phone_setting.backlight_time = $bl_on_time\n";
$config .= "phone_setting.inactive_backlight_level = $inactivity_brightness_level\n";
if(str_ends_with($line1_ring_tone, '.wav')) {
  $config .= "phone_setting.ring_type = $line1_ring_tone\n";
} else {
  $config .= "phone_setting.ring_type = $line1_ring_tone.wav\n";
}
$config .= "voice.ring_vol = $ringer_volume\n";
$config .= "screensaver.wait_time = $screen_save_time\n";
$config .= "ringtone_url = $custom_ringtone1\n";

$config .= "action_url.call_established = $protocol://{$cfg['cnf']['fqdn']}/web/xml_connected.php?mac=\$mac&vendor=yealink&remotenumber=\$display_remote\n";
$config .= "action_url.call_terminated = $protocol://{$cfg['cnf']['fqdn']}/web/xml_disconnected.php?mac=\$mac&vendor=yealink\n";
$config .= "action_url.call_remote_canceled = $protocol://{$cfg['cnf']['fqdn']}/web/xml_disconnected.php?mac=\$mac&vendor=yealink\n";
$config .= "action_url.cancel_callout = $protocol://{$cfg['cnf']['fqdn']}/web/xml_disconnected.php?mac=\$mac&vendor=yealink\n";
$config .= "action_url.incoming_call = $protocol://{$cfg['cnf']['fqdn']}/web/xml_incoming.php?mac=\$mac&vendor=yealink&remotenumber=\$remote\n";
$config .= "action_url.off_hook = $protocol://{$cfg['cnf']['fqdn']}/web/xml_offhook.php?mac=\$mac&vendor=yealink\n";
$config .= "action_url.on_hook = $protocol://{$cfg['cnf']['fqdn']}/web/xml_onhook.php?mac=\$mac&vendor=yealink\n";
$config .= "action_url.outgoing_call = $protocol://{$cfg['cnf']['fqdn']}/web/xml_outgoing.php?mac=\$mac&vendor=yealink&remotenumber=\$calledNumber\n";
$config .= "action_url.registered = $protocol://{$cfg['cnf']['fqdn']}/web/xmlyealink.php?mac=\$mac&action=regevent&regstate=REGISTERED&regcode=200&ip=\$ip\n";
$config .= "action_url.setup_completed = $protocol://{$cfg['cnf']['fqdn']}/web/xml_startup.php?vendor=yealink\n";

// Voicemail und Wahlwiederholung auf Programmkeys legen:

$config .= "programablekey.17.line = 0\n";
$config .= "programablekey.17.type = 27\n";
$config .= "programablekey.17.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=$mac&action=anrufliste&l=a\n";
$config .= "programablekey.18.line = 0\n";
$config .= "programablekey.18.type = 27\n";
$config .= "programablekey.18.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=$mac&action=voicemail\n";

// Nst spezifisch
$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
if(mysqli_num_rows($query) != 0) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);

  $config .= "account.1.auth_name = $authname\n";
  $config .= "account.1.display_name = $displayname\n";
  $config .= "account.1.enable = 1\n";
  $config .= "account.1.label = $screenname\n";
  $config .= "account.1.outbound_proxy.1.address = $proxy\n";
  $config .= "account.1.outbound_proxy_enable = 1\n";
  $config .= "account.1.sip_server.1.address = $registrar\n";
  $config .= "account.1.subscribe_mwi_to_vm = $sip_explicit_mwi_subscription\n";
  $config .= "account.1.user_name = $username\n";
  $config .= "account.1.password = $password\n";
  $config .= "account.1.display_mwi.enable = $mwi_led_line\n";
  $config .= "account.1.dtmf.type = 3\n";

  switch($codecs) {
    case 1:
      $config .= "account.1.codec.g722.enable = 1\n";
      $config .= "account.1.codec.pcma.enable = 1\n";
      $config .= "account.1.codec.g729.enable = 0\n";
      $config .= "account.1.codec.pcmu.enable = 0\n";
      $config .= "account.1.codec.g722.priority = 0\n";
      $config .= "account.1.codec.pcma.priority = 1\n";
      break;
    case 2:
      $config .= "account.1.codec.g722.enable = 1\n";
      $config .= "account.1.codec.pcma.enable = 1\n";
      $config .= "account.1.codec.g729.enable = 0\n";
      $config .= "account.1.codec.pcmu.enable = 0\n";
      $config .= "account.1.codec.g722.priority = 1\n";
      $config .= "account.1.codec.pcma.priority = 0\n";
      break;
    case 3:
      $config .= "account.1.codec.pcma.enable = 1\n";
      $config .= "account.1.codec.g729.enable = 0\n";
      $config .= "account.1.codec.pcmu.enable = 0\n";
      $config .= "account.1.codec.pcma.priority = 0\n";
      break;
    case 4:
      $config .= "account.1.codec.g722.enable = 1\n";
      $config .= "account.1.codec.g729.enable = 0\n";
      $config .= "account.1.codec.pcmu.enable = 0\n";
      $config .= "account.1.codec.g722.priority = 0\n";
      break;
  }

  //Tasten TOP
  $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst' AND taste LIKE 'top%'");
  if(mysqli_num_rows($query) != 0) {
    while($row = mysqli_fetch_array($query)) {
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $value = $row['ziel'];
      $type = $row['type'];
      $label = $row['label'];
      switch($type) {
        case 'blf':
          $config .= "linekey.$keyno.type = 27\n";
  	      $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=call&remotenumber=**$value\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
          break;
        case 'speeddial':
	        $config .= "linekey.$keyno.type = 13\n";
          $config .= "linekey.$keyno.value = $value";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 1\n";
	        break;
        case 'line':
	        $config .= "linekey.$keyno.line = $value\n";
	        $config .= "linekey.$keyno.value = 0\n";
  	      break;
        case 'mobilelink':
          $config .= "linekey.$keyno.type = 77\n";
	        $config .= "linekey.$keyno.label = $label\n";
	        break;
        case 'voicemail':
          $config .= "linekey.$keyno.type = 27\n";
          $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=voicemail\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
          break;
        case 'telefonbuch':
	        $config .= "linekey.$keyno.type = 27\n";
          $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=telefonbuch\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
          break;
        case 'anrufliste':
          $config .= "linekey.$keyno.type = 27\n";
          $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=anrufliste\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
  	      break;
        case 'rvt':
	        $config .= "linekey.$keyno.type = 5\n";
          $config .= "linekey.$keyno.line = 0\n";
          $config .= "linekey.$keyno.label = $label\n";
  	      break;
        case 'kamera':
	        $config .= "linekey.$keyno.type = 27\n";
          $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=webcam\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
	        break;
        case 'tueroeffner':
	        $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
          if(mysqli_num_rows($query) == 1) {
            $array = mysqli_fetch_array($query);
            $wc_url = $array['wc_url'];
	        } else {
            $wc_url = '';
	        }
	        $config .= "linekey.$keyno.type = 17l\n";
  	      $config .= "linekey.$keyno.value = $wc_url\n";
	        $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
	        break;
        case 'services':
          $config .= "linekey.$keyno.type = 27\n";
          $config .= "linekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=services\n";
          $config .= "linekey.$keyno.label = $label\n";
          $config .= "linekey.$keyno.line = 0\n";
          break;
      }
    }
  }
  //Tasten SOFT
  $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst' AND taste LIKE 'soft%'");
  if(mysqli_num_rows($query) != 0) {
    while($row = mysqli_fetch_array($query)) {
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $value = $row['ziel'];
      $type = $row['type'];
      $label = $row['label'];
      switch($type) {
        case 'blf':
          $config .= "programablekey.$keyno.type = 27\n";
  	      $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=call&remotenumber=**$value\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
          break;
        case 'speeddial':
	        $config .= "programablekey.$keyno.type = 13\n";
          $config .= "programablekey.$keyno.value = $value";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 1\n";
	        break;
        case 'line':
	        $config .= "programablekey.$keyno.line = $value\n";
	        $config .= "programablekey.$keyno.value = 0\n";
  	      break;
        case 'mobilelink':
          $config .= "programablekey.$keyno.type = 77\n";
	        $config .= "programablekey.$keyno.label = $label\n";
	        break;
        case 'voicemail':
          $config .= "programablekey.$keyno.type = 27\n";
          $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=voicemail\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
          break;
        case 'telefonbuch':
	        $config .= "programablekey.$keyno.type = 27\n";
          $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=telefonbuch\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
          break;
        case 'anrufliste':
          $config .= "programablekey.$keyno.type = 27\n";
          $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=anrufliste\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
  	      break;
        case 'rvt':
	        $config .= "programablekey.$keyno.type = 5\n";
          $config .= "programablekey.$keyno.line = 0\n";
          $config .= "programablekey.$keyno.label = $label\n";
  	      break;
        case 'kamera':
	        $config .= "programablekey.$keyno.type = 27\n";
          $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=webcam\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
	        break;
        case 'tueroeffner':
	        $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
          if(mysqli_num_rows($query) == 1) {
            $array = mysqli_fetch_array($query);
            $wc_url = $array['wc_url'];
	        } else {
            $wc_url = '';
	        }
	        $config .= "programablekey.$keyno.type = 17l\n";
  	      $config .= "programablekey.$keyno.value = $wc_url\n";
	        $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
	        break;
        case 'services':
          $config .= "programablekey.$keyno.type = 27\n";
          $config .= "programablekey.$keyno.value = $protocol://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=\$mac&action=services\n";
          $config .= "programablekey.$keyno.label = $label\n";
          $config .= "programablekey.$keyno.line = 0\n";
          break;
      }
    }
  }
}
file_put_contents($file, $config);
?>
