<?php
include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);

if(isset($_GET['nst'])) {

  $nst = $_GET['nst'];

  $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id = 0");
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);

  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);

  $settingsfile = $cfg['cnf']['path'].'/prov/snom-settings-'.$mac.'.xml';
  $phonebook_url = "https://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=$nst&action=telefonbuch";
  file_get_contents($phonebook_url);

  // Erstelle das root-Element <settings>
  $settings = new SimpleXMLElement('<settings/>');

  // Füge das <phone-settings> Element hinzu
  $phoneSettings = $settings->addChild('phone-settings', null);
  $phoneSettings->addAttribute('e', '2');

  // Füge die Kindelemente von <phone-settings> hinzu
  $phoneSettings->addChild('language', 'Deutsch')->addAttribute('perm', '');
  $phoneSettings->addChild("update_server", "https://{$cfg['cnf']['fqdn']}/prov/")->addAttribute('perm', 'RW');
  $phoneSettings->addChild('http_scheme','off')->addAttribute('perm', '');
  $phoneSettings->addChild('http_user', 'admin')->addAttribute('perm', '');
  $phoneSettings->addChild("http_pass", "$admin_password_phone")->addAttribute('perm', '');
  $phoneSettings->addChild('dst', '3600 03.05.07 02:00:00 10.05.07 03:00:00')->addAttribute('perm', '');
  $phoneSettings->addChild('timezone', 'GER+1')->addAttribute('perm', '');
  $phoneSettings->addChild('date_us_format', 'off')->addAttribute('perm', '');
  $phoneSettings->addChild("admin_mode_password", "$admin_password_phone")->addAttribute('perm', '');
  $phoneSettings->addChild('tone_scheme', 'GER')->addAttribute('perm', '');
  $phoneSettings->addChild('alert_internal_ring_sound', 'Ringer1')->addAttribute('perm', '');
  $phoneSettings->addChild('update_host_f', '')->addAttribute('perm', 'RW');
  $phoneSettings->addChild('web_language', 'Deutsch')->addAttribute('perm', '');
  $phoneSettings->addChild('use_hidden_tags', 'off')->addAttribute('perm', '');
  $phoneSettings->addChild('restrict_uri_queries', 'off')->addAttribute('perm', '');
  $phoneSettings->addChild('dialnumber_us_format', 'off')->addAttribute('perm', '');
  $phoneSettings->addChild('was_never_registered', 'off')->addAttribute('perm', '');
  $phoneSettings->addChild('replacement_plan_url', 'xml')->addAttribute('perm', '');
  $phoneSettings->addChild("action_setup_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_startup.php?vendor=snom")->addAttribute('perm', '');
  $phoneSettings->addChild("action_log_on_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xmlsnom.php?nst=\$user_uid[1]&amp;action=regevent&amp;regstate=REGISTERED&amp;regcode=200&amp;ip=\$local_ip")->addAttribute('perm', '');
  $phoneSettings->addChild("action_incoming_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_incoming.php?nst=\$user_uid[1]&amp;vendor=snom&amp;remotenumber=\$remote")->addAttribute('perm', '');
  $phoneSettings->addChild("action_outgoing_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_outgoing.php?nst=\$user_uid[1]&amp;vendor=snom&amp;remotenumber=\$remote")->addAttribute('perm', '');
  $phoneSettings->addChild("action_onhook_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_onhook.php?nst=\$user_uid[1]&amp;vendor=snom")->addAttribute('perm', '');
  $phoneSettings->addChild("action_offhook_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_offhook.php?nst=\$user_uid[1]&amp;vendor=snom")->addAttribute('perm', '');
  $phoneSettings->addChild("action_connected_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_connected.php?nst=\$user_uid[1]&amp;vendor=snom&amp;remotenumber=\$remote")->addAttribute('perm', '');
  $phoneSettings->addChild("action_disconnected_url", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_disconnected.php?nst=\$user_uid[1]&amp;vendor=snom")->addAttribute('perm', '');
  $phoneSettings->addChild("ntp_server", "$ntp_server1 $ntp_server2 $ntp_server3")->addAttribute('perm','');
  $phoneSettings->addChild('network_id_port', '5060')->addAttribute('perm','');
  $phoneSettings->addChild('filter_registrar', 'off')->addAttribute('perm','');
  $phoneSettings->addChild('retry_after_failed_register','30')->addAttribute('perm','');
  $phoneSettings->addChild('vol_ringer','1')->addAttribute('perm','');
  $phoneSettings->addChild('backlight_idle','0')->addAttribute('perm','');
  $phoneSettings->addChild('backlight','15')->addAttribute('perm','');
  $phoneSettings->addChild('allow_sip_settings','on')->addAttribute('perm','');
  $phoneSettings->addChild('setting_server', '')->addAttribute('perm','');
  $phoneSettings->addChild('ignore_security_warning', 'true')->addAttribute('perm','');
  $phoneSettings->addChild('mb_trusted_hosts', $xml_application_post_list.' '.$ws_fqdn)->addAttribute('perm','');
  $phoneSettings->addChild("dkey_retrieve", "$protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=voicemail")->addAttribute('perm', '');
  $phoneSettings->addChild('call_waiting', $call_waiting_tone)->addAttribute('perm', '');
  $phoneSettings->addChild('backlight', $brightness_level)->addAttribute('perm', '');
  $phoneSettings->addChild('dim_timer', $bl_on_time)->addAttribute('perm', '');
  $phoneSettings->addChild('backlight_idle', $inactivity_brightness_level)->addAttribute('perm', '');
  $phoneSettings->addChild('vol_handset', $handset_volume)->addAttribute('perm', '');
  $phoneSettings->addChild('vol_speaker', $speaker_volume)->addAttribute('perm', '');
  $phoneSettings->addChild('vol_headset', $headset_volume)->addAttribute('perm', '');
  $phoneSettings->addChild('vol_ringer', $ringer_volume)->addAttribute('perm', '');
  

  // User-spezifische Einträge
  $usernameSipusernameAsLine = $phoneSettings->addChild('user_sipusername_as_line', 'on');
  $usernameSipusernameAsLine->addAttribute('idx', '1');
  $usernameSipusernameAsLine->addAttribute('perm', '');

  $userRealname = $phoneSettings->addChild("user_realname", $screenname);
  $userRealname->addAttribute('idx', '1');
  $userRealname->addAttribute('perm', '');

  $userName = $phoneSettings->addChild("user_name", $username);
  $userName->addAttribute('idx', '1');
  $userName->addAttribute('perm', '');

  $userHost = $phoneSettings->addChild("user_host", $registrar);
  $userHost->addAttribute('idx', '1');
  $userHost->addAttribute('perm', '');

  $userPass = $phoneSettings->addChild("user_pass", $password);
  $userPass->addAttribute('idx', '1');
  $userPass->addAttribute('perm', '');

  $userUid = $phoneSettings->addChild('user_uid', $displayname);
  $userUid->addAttribute('idx', '1');
  $userUid->addAttribute('perm', '');

  $userOutbound = $phoneSettings->addChild("user_outbound", $proxy);
  $userOutbound->addAttribute('idx', '1');
  $userOutbound->addAttribute('perm', '');

  $userWasRegistered = $phoneSettings->addChild('user_was_registered', 'true');
  $userWasRegistered->addAttribute('idx', '1');
  $userWasRegistered->addAttribute('perm', '');

  $userPname = $phoneSettings->addChild('user_pname', $authname);
  $userPname->addAttribute('idx', '1');
  $userPname->addAttribute('perm', '');

  if($sip_explicit_mwi_subscription == 1) {
    $userMailbox = $phoneSettings->addChild('user_mailbox', $username);
    $userMailbox->addAttribute('idx', '1');
    $userMailbox->addAttribute('perm', '');
  }

  $hideIdentity = $phoneSettings->addChild('hide_identity', 'false');
  $hideIdentity->addAttribute('idx', '1');
  $hideIdentity->addAttribute('perm', '');

  $useContactInReferToHdr = $phoneSettings->addChild('use_contact_in_refer_to_hdr', 'off');
  $useContactInReferToHdr->addAttribute('idx', '1');
  $useContactInReferToHdr->addAttribute('perm', '');

  $userRinger = $phoneSettings->addChild('user_ringer', $line1_ring_tone);
  $userRinger->addAttribute('idx', '1');
  $userRinger->addAttribute('perm', '');

  $userCustom = $phoneSettings->addChild('user_custom', $custom_ringtone_1);
  $userCustom->addAttribute('idx', '1');
  $userCustom->addAttribute('perm', '');

  switch($codecs) {
    case 1:
      $codecPriorityList = $phoneSettings->addChild('codec_priority_list','g722,pcma,telephone-event');
      $codecPriorityList->addAttribute('idx', '1');
      $codecPriorityList->addAttribute('perm', '');
      break;
    case 2:
      $codecPriorityList = $phoneSettings->addChild('codec_priority_list','pcma,g722,telephone-event');
      $codecPriorityList->addAttribute('idx', '1');
      $codecPriorityList->addAttribute('perm', '');
      break;
    case 3:
      $codecPriorityList = $phoneSettings->addChild('codec_priority_list','pcma,telephone-event');
      $codecPriorityList->addAttribute('idx', '1');
      $codecPriorityList->addAttribute('perm', '');
      break;
    case 4:
      $codecPriorityList = $phoneSettings->addChild('codec_priority_list','g722,telephone-event');
      $codecPriorityList->addAttribute('idx', '1');
      $codecPriorityList->addAttribute('perm', '');
      break;
  }

  // Füge das <update_policy> Element hinzu
  $phoneSettings->addChild('update_policy', 'auto_update')->addAttribute('perm', '');

  //Kontexttasten
  $ckquery = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst' AND taste LIKE 'soft%'");
  if(mysqli_num_rows($ckquery) != 0) {
    while($row = mysqli_fetch_array($ckquery)) {
      $key = $row['taste'];
      $keyno = (int)substr($key, -1)-1;
      $value = $row['ziel'];
      $type = $row['type'];
      $label = $row['label'];
      switch($type) {
        case 'speeddial':
          $contextKey[$keyno] = $phoneSettings->addChild('context_key', 'speed '.$value);
          $contextKey[$keyno]->addAttribute('idx', $keyno);
          $contextKey[$keyno]->addAttribute('perm', '');
          $contextKeyLabel[$keyno] = $phoneSettings->addChild('context_key_label', $label);
          $contextKeyLabel[$keyno]->addAttribute('idx', $keyno);
          $contextKeyLabel[$keyno]->addAttribute('perm', '');
          break;
        case 'telefonbuch':
          $contextKey[$keyno] = $phoneSettings->addChild('context_key', 'keyevent F_ADR_BOOK');
          $contextKey[$keyno]->addAttribute('idx', $keyno);
          $contextKey[$keyno]->addAttribute('perm', '');
          $contextKeyLabel[$keyno] = $phoneSettings->addChild('context_key_label', $label);
          $contextKeyLabel[$keyno]->addAttribute('idx', $keyno);
          $contextKeyLabel[$keyno]->addAttribute('perm', '');
          break;
        case 'rvt':
          $contextKey[$keyno] = $phoneSettings->addChild('context_key', 'keyevent F_DND');
          $contextKey[$keyno]->addAttribute('idx', $keyno);
          $contextKey[$keyno]->addAttribute('perm', '');
          $contextKeyLabel[$keyno] = $phoneSettings->addChild('context_key_label', $label);
          $contextKeyLabel[$keyno]->addAttribute('idx', $keyno);
          $contextKeyLabel[$keyno]->addAttribute('perm', '');
          break;
      }
    }
  }
  // Ausgabe des erzeugten XML
  file_put_contents($settingsfile, $settings->asXML());


  // Tasten
  $keysfile = $cfg['cnf']['path'].'/prov/snom-keys-'.$mac.'.xml';

  $functionKeys = new SimpleXMLElement('<functionKeys/>');
  $functionKeys->addAttribute('e', '2');
  

  $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst' AND taste LIKE 'top%'");
  if(mysqli_num_rows($query) != 0) {
    while($row = mysqli_fetch_array($query)) {
      $key = $row['taste'];
      $keyno = (int)substr($key, -1)-1;
      $value = $row['ziel'];
      $type = $row['type'];
      $label = $row['label'];
      switch($type) {
        case 'blf':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=call&amp;remotenumber=**$value");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
          break;
        case 'speeddial':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "speed $value");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
          break;
        case 'line':
	        $fkey[$keyno] = $functionKeys->addChild("fkey", "line sip:$username@$registrar");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
	        break;
        case 'mobilelink':
	        break;
        case 'voicemail':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=voicemail");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
          break;
        case 'telefonbuch':
	        $fkey[$keyno] = $functionKeys->addChild("fkey", "keyevent F_ADR_BOOK");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
	        break;
        case 'anrufliste':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=anrufliste");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
          break;
        case 'rvt':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "keyevent F_DND");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
	        break;
        case 'kamera':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=webcam");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
	        break;
        case 'tueroeffner':
	        $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
          if(mysqli_num_rows($query) == 1) {
            $array = mysqli_fetch_array($query);
            $wc_url = $array['wc_url'];
	        } else {
            $wc_url = '';
	        }
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $wc_url");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
	        break;
        case 'services':
          $fkey[$keyno] = $functionKeys->addChild("fkey", "url $protocol://{$cfg['cnf']['fqdn']}/web/xml_snom.php?nst=\$user_uid[1]&amp;action=services");
          $fkey[$keyno]->addAttribute('idx', $keyno);
          $fkey[$keyno]->addAttribute('context','active');
          $fkey[$keyno]->addAttribute('short_label_name','icon_text');
          $fkey[$keyno]->addAttribute('short_label', $label);
          $fkey[$keyno]->addAttribute('short_default_text','!!$(::)!!$(generate_via_conditional_label_short)');
          $fkey[$keyno]->addAttribute('label_mode','icon_text');
          $fkey[$keyno]->addAttribute('icon_type','');
          $fkey[$keyno]->addAttribute('reg_label_mode','icon_text');
          $fkey[$keyno]->addAttribute('ringer','Silent');
          $fkey[$keyno]->addAttribute('park_retrieve','');
          $fkey[$keyno]->addAttribute('label', $label);
          $fkey[$keyno]->addAttribute('lp','pn');
          $fkey[$keyno]->addAttribute('default_text','!!$(::)!!$(generate_via_conditional_label_full)');
          break;
      }
    }
  }
 
  file_put_contents($keysfile, $functionKeys->asXML());

  $provfile = $cfg['cnf']['path'].'/prov/snom'.$model.'-'.$mac.'.htm';

  $settingFiles = new SimpleXMLElement('<setting-files/>');
  $settingFiles->addChild('file', null)->addAttribute("url", "https://{$cfg['cnf']['fqdn']}/prov/snom-settings-$mac.xml");
  $settingFiles->addChild('file', null)->addAttribute("url", "https://{$cfg['cnf']['fqdn']}/prov/snom-keys-$mac.xml");
  $settingFiles->addChild('file', null)->addAttribute("url", "https://{$cfg['cnf']['fqdn']}/prov/snom-tbook-$mac.xml");

  file_put_contents($provfile, $settingFiles->asXML());

}

?>