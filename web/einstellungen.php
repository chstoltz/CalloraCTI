<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');
$nst = $_SESSION['nst'];
$user_id = $_SESSION['id'];
$sip_query = mysqli_query($db_conn,"SELECT model.sip AS type FROM model JOIN usr_telefon ON model.model=usr_telefon.model WHERE usr_telefon.displayname = '$nst'");
$sip_array = mysqli_fetch_array($sip_query);
$dect_query = mysqli_query($db_conn,"SELECT model.dect AS type FROM model JOIN usr_mobilteil ON model.model=usr_mobilteil.model WHERE usr_mobilteil.nst = '$nst'");
$dect_array = mysqli_fetch_array($dect_query);
if(@$dect_array['type'] == TRUE) {
  $tab_topsoftkeys = 'style="display:none"';
  $link_topsoftkeys = '';
  $tab_dect = '';
  $link_dect = 'w3-light-grey';
} else {
  $tab_topsoftkeys = '';
  $link_topsoftkeys = 'w3-light-grey';
  $tab_dect = 'style="display:none"';
  $link_dect = '';
}
$tab_softkeys = 'style="display:none"';
$link_softkeys = '';
$tab_telefon = 'style="display:none"';
$link_telefon = '';
$tab_expkeys = 'style="display:none"';
$link_exp = '';
echo '<script>
        function openTab(evt, tabName) {
          var i, x, tablinks;
          x = document.getElementsByClassName("tab");
          for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
	        }
	        tablinks = document.getElementsByClassName("tablink");
	        for (i = 0; i < x.length; i++) {
		        tablinks[i].className = tablinks[i].className.replace(" w3-light-grey", "");
		      }
		      document.getElementById(tabName).style.display = "block";
          evt.currentTarget.className += " w3-light-grey";
	      }
      </script>';
if(isset($_POST['settings'])) { 
  $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$nst'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];
  switch($_POST['settings']) {
    case 'keys':
      $type = $_POST['type'];
      $key = $_POST['key'];
      $label = $_POST['label']; 
      $value = $_POST['value'];
      $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND taste = '$key'");
      if(mysqli_num_rows($query) == 0) {
	      if($type != 'leer') { mysqli_query($db_conn,"INSERT INTO tasten (id, nst, taste, ziel, type, label) VALUES (NULL, '$nst', '$key', '$value', '$type', '$label')"); }
      } else {
	      if($type != 'leer') { 
          mysqli_query($db_conn,"UPDATE tasten SET ziel='$value',type='$type',label='$label' WHERE taste = '$key' AND nst = '$nst'");
	      } else {
	        mysqli_query($db_conn,"DELETE FROM tasten WHERE taste = '$key' AND nst = '$nst'");
	      }
      }
      provision($nst);
      exec("php {$cfg['cnf']['path']}/web/sipnotify.php nst=$nst & > /dev/null 2>&1");
      if($_POST['keytype'] != 'topsoftkeys') {
        $keytype = $_POST['keytype'];
        $tab_topsoftkeys = 'style="display:none"';
        $link_topsoftkeys = '';
        $tab = 'tab_'.$keytype;
        $$tab = '';
        $link = 'link_'.$keytype;
        $$link = 'w3-light-grey';
      }
      $feedback = 'Gespeichert!';
      break;
    case 'telefon':
      $tab_topsoftkeys = 'style="display:none"';
      $link_topsoftkeys = '';
      $tab_telefon = '';
      $link_telefon = 'w3-light-grey';
      extract($_POST, EXTR_OVERWRITE);
      if(isset($call_forward_disabled)) { $call_forward_disabled = 1; } else { $call_forward_disabled = 0; }
      if(isset($call_waiting_tone)) { $call_waiting_tone = 1; } else { $call_waiting_tone = 0; }
      if(isset($sip_explicit_mwi_subscription)) { $sip_explicit_mwi_subscription = 1; } else { $sip_explicit_mwi_subscription = 0; }
      if(isset($missed_calls_indicator_disabled)) { $missed_calls_indicator_disabled = 1; } else { $missed_calls_indicator_disabled = 0; }
      if(isset($switch_focus_to_ringing_line)) { $switch_focus_to_ringing_line = 1; } else { $switch_focus_to_ringing_line = 0; }
      if(!isset($bl_on_time)) { $bl_on_time = 180; }
      if(!isset($screen_save_time)) { $screen_save_time = 1800; }
      mysqli_query($db_conn,"UPDATE usr_telefon SET call_forward_disabled='$call_forward_disabled', idle_screen_font_color='$idle_screen_font_color', dst_config='$dst_config', call_waiting_tone='$call_waiting_tone', mwi_led_line='$mwi_led_line', missed_calls_indicator_disabled='$missed_calls_indicator_disabled', brightness_level='$brightness_level', bl_on_time='$bl_on_time', inactivity_brightness_level='$inactivity_brightness_level', screen_save_time='$screen_save_time', switch_focus_to_ringing_line='$switch_focus_to_ringing_line', handset_volume='$handset_volume', speaker_volume='$speaker_volume', headset_volume='$headset_volume', ringer_volume='$ringer_volume', audio_mode='$audio_mode', line1_ring_tone='$line1_ring_tone', sip_explicit_mwi_subscription='$sip_explicit_mwi_subscription', custom_ringtone_1='$custom_ringtone_1', exp='$exp' WHERE displayname='$nst'");
      provision($nst);
      sipnotify($nst);
      if(isset($restart_after_save)) {
        $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Command: FastReboot"/></AastraIPPhoneExecute>';
        push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      }
      $feedback = 'Gespeichert! Bitte Telefon ggf. neu starten.';
      break;
    case 'dect':
      $tab_fritzbox = 'style="display:none"';
      $link_fritzbox = '';
      $tab_dect = '';
      $link_dect = 'w3-light-grey';
      extract($_POST, EXTR_OVERWRITE);
      if(isset($key_lock_enable)) { $key_lock_enable = 1; $kle_axi = 'TRUE'; } else { $key_lock_enable = 0; $kle_axi = 'FALSE'; }
      if(isset($LedInfo)) { $LedInfo = 1; $LI_axi = 'TRUE'; } else { $LedInfo = 0; $LI_axi = 'FALSE'; }
      $command = "<SetPpProfile><ppProfile id=\"$dect_id\" name=\"$nst\" ppData=\"UD_RingerMelodyIntern=$RingerMelodyIntern&#10;UD_RingerMelodyExtern=$RingerMelodyExtern&#10;UD_DialCodeInt=**&#10;UD_LedInfo=$LI_axi&#10;UD_DispLang=$DispLang&#10;UD_DispColor=$DispColor&#10;UD_KeyAssignmentIdle=side1 $sidekey1&#10;UD_KeyAssignmentIdle=side2 $sidekey2&#10;UD_KeyAssignmentIdle=side3 $sidekey3&#10;\"/></SetPpProfile>";
      axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
      mysqli_query($db_conn,"UPDATE usr_mobilteil SET sidekey1='$sidekey1', sidekey2='$sidekey2',sidekey3='$sidekey3',LedInfo='$LedInfo',DispLang='$DispLang',DispColor='$DispColor',RingerMelodyIntern='$RingerMelodyIntern',RingerMelodyExtern='$RingerMelodyExtern' WHERE nst='$nst'");
      $feedback = 'Gespeichert!';
      break;
  }
}
// POST ENDE
// MENUE
$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE nst = '$nst'");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
}
$query_model = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE nst = '$nst'");
$array_model = mysqli_fetch_array($query_model);
$model = $array_model['model'];
$query = mysqli_query($db_conn,"SELECT * FROM model WHERE model='$model'");
$tasten = mysqli_fetch_array($query);
// Für Inculde settings
$hersteller = $tasten['hersteller'];
$topsoftkeys = @$tasten['topsoftkey'];
$softkeys = @$tasten['softkey'];
echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Einstellungen</div>';
if(@$sip_array['type'] == true) {
  if($topsoftkeys != 0) {
    echo '<button class="w3-bar-item w3-button w3-padding-large tablink '.$link_topsoftkeys.'" onclick="openTab(event,\'TopSoftKeys\')">obere Tasten</button>';
  }
  if($softkeys != 0) {
    echo '<button class="w3-bar-item w3-button w3-padding-large tablink '.$link_softkeys.'" onclick="openTab(event,\'SoftKeys\')">untere Tasten</button>';
  }
}
if($array['exp'] != 0) { 
  $exp_m = $array['exp'];
  $exp_query = mysqli_query($db_conn,"SELECT * FROM model WHERE model='$exp_m'");
  $exp_array = mysqli_fetch_array($exp_query);
  $expkeys = $exp_array['expkey'];  
  $exp_module = $array['exp'];
  echo '<button class="w3-bar-item w3-button w3-padding-large tablink '.$link_exp.'" onclick="openTab(event,\'Tastenmodul\')">Tastenmodul</button>'; 
}
if(@$sip_array['type'] == true) {
  echo '  <button class="w3-bar-item w3-button w3-padding-large tablink '.$link_telefon.'" onclick="openTab(event,\'Telefon\')">Telefon</button>';
}
if(@$dect_array['type'] == true) {
  echo '  <button class="w3-bar-item w3-button w3-padding-large tablink '.$link_dect.'" onclick="openTab(event,\'Dect\')">Dect</button>';
}
echo '</div>';
// TOPSOFTKEYS
if(@$sip_array['type'] == TRUE) {
  if($topsoftkeys != 0) {
    echo '<div id="TopSoftKeys" class="w3-container tab w3-animate-opacity" '.$tab_topsoftkeys.'><p>&nbsp;</p>
          <script>function myFunction(id) {
             var x = document.getElementById(id);
             if (x.className.indexOf("w3-show") == -1) {
	             x.className += " w3-show";
	           } else {
	             x.className = x.className.replace(" w3-show", "");
	           }
       }</script>';
    for ($c=1; $c<=$topsoftkeys; $c++) {
      $tquery = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND taste='topsoftkey$c'");
      if(mysqli_num_rows($tquery) == 1) {
        $tarray = mysqli_fetch_array($tquery);
        $type = $tarray['type'];
        $type = 'tkey_'.$type;
        $$type[$c] = 'selected';
        $label = $tarray['label'];
        $value = $tarray['ziel'];
      }
echo '<button onclick="myFunction(\'topsoftkey'.$c.'\')" class="w3-button w3-block w3-left-align w3-light-grey w3-border" style="width:400px">TopSoftKey '.$c.'</button>
      <div id="topsoftkey'.$c.'" class="w3-container w3-hide">
        <form action="einstellungen.php" method="post" name="topsoftkey'.$c.'">
	        <label>Tastentyp:</label><br />
	        <select class="w3-select w3-padding" name="type" style="width:300px">
            <option value="leer">leer</option>
            <option value="blf" '.@$tkey_blf[$c].'>BLF</option>
            <option value="speeddial" '.@$tkey_speeddial[$c].'>Kurzwahl</option>
            <option value="line" '.@$tkey_line[$c].'>Leitung</option>
	          <option value="mobilelink" '.@$tkey_mobilelink[$c].'>MobileLink</option>
	          <option value="telefonbuch" '.@$tkey_telefonbuch[$c].'>Telefonbuch</option>
            <option value="anrufliste" '.@$tkey_anrufliste[$c].'>Anrufliste</option>
            <option value="voicemail" '.@$tkey_voicemail[$c].'>Anrufbeantworter</option>
	          <option value="rvt" '.@$tkey_rvt[$c].'>Ruhe vor dem Telefon</option>
	          <option value="kamera" '.@$tkey_kamera[$c].'>Kamera</option>
	          <option value="tueroeffner" '.@$tkey_tueroeffner[$c].'>Türöffner</option>
            <option value="services" '.@$tkey_services[$c].'>Services</option>
          </select><br />
	        <label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px">
	        <label>Inhalt:</label>
          <input class="w3-input w3-border" type="text" name="value" value="'.@$value.'" style="width:300px">
	        <input type="hidden" name="key" value="topsoftkey'.$c.'">
          <input type="hidden" name="keytype" value="topsoftkeys">
          <input type="hidden" name="settings" value="keys">
	        <button class="w3-btn w3-blue" type="submit">Speichern</button>
	      </form><p></p>
      </div>';
      unset($label); unset($value);
    }
  
echo '</div>';
  }
  // SOFTKEYS
  if($softkeys != 0) {
    echo '<div id="SoftKeys" class="w3-container tab w3-animate-opacity" '.$tab_softkeys.'><p>&nbsp;</p>';
    for ($s=1; $s<=$softkeys; $s++) {
      $squery = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND taste='softkey$s'");
      if(mysqli_num_rows($squery) == 1) {
        $sarray = mysqli_fetch_array($squery);
        $type = $sarray['type'];
        $type = 'skey_'.$type;
        $$type[$s] = 'selected';
        $label = $sarray['label'];
        $value = $sarray['ziel'];
      }
echo '<button onclick="myFunction(\'softkey'.$s.'\')" class="w3-button w3-block w3-left-align w3-light-grey w3-border" style="width:400px">SoftKey '.$s.'</button>
      <div id="softkey'.$s.'" class="w3-container w3-hide">
        <form action="einstellungen.php" method="post" name="softkey'.$s.'">
	        <label>Tastentyp:</label><br />
	        <select class="w3-select w3-padding" name="type" style="width:300px">
            <option value="leer">leer</option>
            <option value="blf" '.@$skey_blf[$s].'>BLF</option>
            <option value="speeddial" '.@$skey_speeddial[$s].'>Kurzwahl</option>
            <option value="line" '.@$skey_line[$s].'>Leitung</option>
	          <option value="mobilelink" '.@$skey_mobilelink[$s].'>MobilLink</option>
            <option value="voicemail" '.@$skey_voicemail[$s].'>Anrufbeantworter</option>
	          <option value="telefonbuch" '.@$skey_telefonbuch[$s].'>Telefonbuch</option>
            <option value="anrufliste" '.@$skey_anrufliste[$s].'>Anrufliste</option>
	          <option value="rvt" '.@$skey_rvt[$s].'>Ruhe vor dem Telefon</option>
	          <option value="kamera" '.@$skey_kamera[$s].'>Kamera</option>
	          <option value="tueroeffner" '.@$skey_tueroeffner[$s].'>Türöffner</option>
            <option value="services" '.@$skey_services[$s].'>Services</option>
          </select><br />
	        <label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px">
	        <label>Inhalt:</label>
          <input class="w3-input w3-border" type="text" name="value" value="'.@$value.'" style="width:300px">
	        <input type="hidden" name="key" value="softkey'.$s.'">
          <input type="hidden" name="keytype" value="softkeys">
	        <input type="hidden" name="settings" value="keys">
	        <button class="w3-btn w3-blue" type="submit">Speichern</button>
	      </form><p></p>
      </div>';
      unset($label); unset($value);
    }
echo '</div>';
  }
}
if((isset($exp_module)) AND ($exp_module != 0)) {
  echo '<div id="Tastenmodul" class="w3-container tab w3-animate-opacity" '.$tab_expkeys.'><p>&nbsp;</p>';
  if($expkeys == 28) {
    echo '<div class="w3-container" style="width:75%">
            <div class="w3-row w3-third">';
  }
  for ($e=1; $e<=$expkeys; $e++) {
    $equery = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND taste='expmod1 key$e'");
    if(mysqli_num_rows($equery) == 1) {
      $earray = mysqli_fetch_array($equery);
      $type = $earray['type'];
      $type = 'ekey_'.$type;
      $$type[$e] = 'selected';
      $label = $earray['label'];
      $value = $earray['ziel'];
    }
  echo '<button onclick="myFunction(\'expkey'.$e.'\')" class="w3-button w3-block w3-left-align w3-light-grey w3-border" style="width:400px">ExpKey '.$e.'</button>
        <div id="expkey'.$e.'" class="w3-container w3-hide">
          <form action="einstellungen.php" method="post" name="expkey'.$e.'">
            <label>Tastentyp:</label><br />
	          <select class="w3-select w3-padding" name="type" style="width:300px">
              <option value="leer">leer</option>
              <option value="blf" '.@$ekey_blf[$e].'>BLF</option>
              <option value="speeddial" '.@$ekey_speeddial[$e].'>Kurzwahl</option>
              <option value="line" '.@$ekey_line[$e].'>Leitung</option>
	            <option value="mobilelink" '.@$ekey_mobilelink[$e].'>MobileLink</option>
              <option value="voicemail" '.@$ekey_voicemail[$e].'>Anrufbeantworter</option>
	            <option value="telefonbuch" '.@$ekey_telefonbuch[$e].'>Telefonbuch</option>
              <option value="anrufliste" '.@$ekey_anrufliste[$e].'>Anrufliste</option>
	            <option value="rvt" '.@$ekey_rvt[$e].'>Ruhe vor dem Telefon</option>
	            <option value="kamera" '.@$ekey_kamera[$e].'>Kamera</option>
	            <option value="tueroeffner" '.@$ekey_tueroeffner[$e].'>Türöffner</option>
              <option value="services" '.@$ekey_services[$e].'>Services</option>
            </select><br />
	          <label>Beschriftung:</label>
            <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px">
	          <label>Inhalt:</label>
            <input class="w3-input w3-border" type="text" name="value" value="'.@$value.'" style="width:300px">
  	        <input type="hidden" name="key" value="expmod1 key'.$e.'">
            <input type="hidden" name="keytype" value="expkeys">
	          <input type="hidden" name="settings" value="keys">
	          <button class="w3-btn w3-blue" type="submit">Speichern</button>
	        </form><p></p>
        </div>';
    if(($expkeys == 28) AND ($e == 14)) { echo '</div><div class="w3-row w3-third">'; }
    unset($label); unset($value);
  }
  if ($expkeys == 28) { echo '</div></div>';}
  echo '</div>';
}
if(@$sip_array['type'] == TRUE) {
echo '<div id="Telefon" class="w3-container tab w3-animate-opacity" '.$tab_telefon.'>';

switch($hersteller) {
  case 'mitel':
    include('settings_mitel.php');
    break;
  case 'yealink':
    include('settings_yealink.php');
    break;
  case 'snom':
    include('settings_snom.php');
    break;
}
}
if(@$dect_array['type'] == TRUE) {
  $query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil JOIN model where usr_mobilteil.model = model.model and usr_mobilteil.nst = '$nst'");
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
  if($LedInfo == 1) { $LI_checked = 'checked'; }
  $RingerMelodyIntern = $RingerMelodyIntern.'_sel_int';
  $$RingerMelodyIntern = 'selected';
  $RingerMelodyExtern = $RingerMelodyExtern.'_sel_ext';
  $$RingerMelodyExtern = 'selected';
  $DispColor = $DispColor.'_sel_DC';
  $$DispColor = 'selected';
  echo '<div id="Dect" class="w3-container tab w3-animate-opacity" '.$tab_dect.'>
          <form class="w3-container w3-padding-large" action="einstellungen.php" method="post" name="dect">
            <div class="w3-container" style="width:75%">
              <div class="w3-row w3-quarter">
                <h5>Benutzereinstellungen:</h5>
                <p><label><b>Displaysprache:</b></label><br />
                <select class="w3-select w3-padding" name="DispLang" style="width:200px">
                  <option value="de">Deutsch</option>
                  <option value="en">Englisch</option>
                </select></p>
                <p><input type="checkbox" class="w3-check w3-border" name="LedInfo" '.@$LI_checked.'>&nbsp;&nbsp;
                <label><b>Info LED aktiv</b></label></p>  
              </div>
              <div class="w3-row w3-quarter">
	              <h5>Telefoneinstellungen:</h5>
                <p><label><b>PIN:</b></label>
                <input type="text" class="w3-input w3-border" value="'.@$pin.'" style="width:200px" readonly="readonly"></p>
                <p><label><b>PIN Tastensperre:</b></label>
                <input type="text" class="w3-input w3-border" value="'.@$pin_lock.'" style="width:200px" readonly="readonly"></p>
                <p><label><b>Displaythema:</b></label><br />
                <select class="w3-select w3-padding" name="DispColor" style="width:200px">
                  <option value="gray" '.@$gray_sel_DC.'>Gray</option>
                  <option value="black" '.@$black_sel_DC.'>Black</option>
                  <option value="business" '.@$business_sel_DC.'>Business</option>
                  <option value="blue" '.@$blue_sel_DC.'>Blue</option>
                  <option value="future" '.@$future_sel_DC.'>Future</option>
                  <option value="plain" '.@$plan_sel_DC.'>Plain</option>
                  <option value="sweet" '.@$sweet_sel_DC.'>Sweet</option>
                </select></p>
                <p><label><b>interner Klingelton:</b></label><br />
                <select class="w3-select w3-padding" name="RingerMelodyIntern" style="width:200px">
                  <option value="butterfly" '.@$butterfly_sel_int.'>Butterfly</option>
                  <option value="weekend" '.@$weekend_sel_int.'>Weekend</option>
                  <option value="barock" '.@$barock_sel_int.'>Barock</option>
                  <option value="ballade" '.@$ballade_sel_int.'>Ballade</option>
                  <option value="fancy" '.@$fancy_sel_int.'>Fancy</option>
                  <option value="comelody" '.@$comelody_sel_int.'>Comelody</option>
                  <option value="easy_groove" '.@$easy_groove_sel_int.'>Easy Groove</option>
                  <option value="happy_fair" '.@$happy_fair_sel_int.'>Happy Fair</option>
                  <option value="kitafun" '.@$kitafun_sel_int.'>Kitafun</option>
                  <option value="latin_dance" '.@$latin_dance_sel_int.'>Latin Dance</option>
                  <option value="little_asia" '.@$little_asia_sel_int.'>Little Asia</option>
                  <option value="mango_selassi" '.@$mango_selassi_sel_int.'>Mango Selassi</option>
                  <option value="parka" '.@$parka_sel_int.'>Parka</option>
                  <option value="remember" '.@$remember_sel_int.'>Remember</option>
                  <option value="rocky_lane" '.@$rocky_lane_sel_int.'>Rocky Lane</option>
                  <option value="ringing_1" '.@$ringing_1_sel_int.'>Ringing 1</option>
                  <option value="ringing_2" '.@$ringing_2_sel_int.'>Ringing 2</option>
                  <option value="ringing_3" '.@$ringing_3_sel_int.'>Ringing 3</option>
                  <option value="ringing_4" '.@$ringing_4_sel_int.'>Ringing 4</option>
                  <option value="ringing_5" '.@$ringing_5_sel_int.'>Ringing 5</option>
                  <option value="ringing_6" '.@$ringing_6_sel_int.'>Ringing 6</option>
                  <option value="ringing_7" '.@$ringing_7_sel_int.'>Ringing 7</option>
                  <option value="ring_vintage" '.@$ring_vintage_sel_int.'>Ring Vintage</option>
                  <option value="vibes" '.@$vibes_sel_int.'>Vibes</option>
                  <option value="attack" '.@$attack_sel_int.'>Attack</option>
                  <option value="doorbell" '.@$doorbell_sel_int.'>Doorbell</option>
                  <option value="boogie" '.@$boogie_sel_int.'>Boogie</option>
                  <option value="polka" '.@$polka_sel_int.'>Polka</option>
                  <option value="classical_1" '.@$classical_1_sel_int.'>Classical 1</option>
                  <option value="classical_2" '.@$classical_2_sel_int.'>Classical 2</option>
                  <option value="classical_3" '.@$classical_3_sel_int.'>Classical 3</option>
                  <option value="classical_4" '.@$classical_4_sel_int.'>Classical 4</option>
                  <option value="alla_turca" '.@$alla_turca_sel_int.'>Alla Turca</option>
                  <option value="entertainer" '.@$entertainer_sel_int.'>Entertainer</option>
                  <option value="jollygood" '.@$jollygood_sel_int.'>Jollygood</option>
                  <option value="in_the_saints" '.@$in_the_saints_sel_int.'>In the Saints</option>
                  <option value="drunken_sailor" '.@$drunken_sailor_sel_int.'>Drunken Sailor</option>
                  <option value="mary_had" '.@$mary_had_sel_int.'>Mary Had</option>
                  <option value="shell_be_walking" '.@$shell_be_walking_sel_int.'>She\'ll Be Walking</option>
                  <option value="pippi_longstocking" '.@$pippi_longstocking_sel_int.'>Pippi Longstocking</option>
                  <option value="policehorn" '.@$policehorn_sel_int.'>Policehorn</option>
                  <option value="synthesizer" '.@$synthesizer_sel_int.'>Synthesizer</option>
                  <option value="after_work" '.@$after_work_sel_int.'>After Work</option>
                  <option value="beep" '.@$beep_sel_int.'>Beep</option>
                </select></p>
                <p><label><b>externer Klingelton:</b></label><br />
                <select class="w3-select w3-padding" name="RingerMelodyExtern" style="width:200px">
                  <option value="butterfly" '.@$butterfly_sel_ext.'>Butterfly</option>
                   <option value="weekend" '.@$weekend_sel_ext.'>Weekend</option>
                  <option value="barock" '.@$barock_sel_ext.'>Barock</option>
                  <option value="ballade" '.@$ballade_sel_ext.'>Ballade</option>
                  <option value="fancy" '.@$fancy_sel_ext.'>Fancy</option>
                  <option value="comelody" '.@$comelody_sel_ext.'>Comelody</option>
                  <option value="easy_groove" '.@$easy_groove_sel_ext.'>Easy Groove</option>
                  <option value="happy_fair" '.@$happy_fair_sel_ext.'>Happy Fair</option>
                  <option value="kitafun" '.@$kitafun_sel_ext.'>Kitafun</option>
                  <option value="latin_dance" '.@$latin_dance_sel_ext.'>Latin Dance</option>
                  <option value="little_asia" '.@$little_asia_sel_ext.'>Little Asia</option>
                  <option value="mango_selassi" '.@$mango_selassi_sel_ext.'>Mango Selassi</option>
                  <option value="parka" '.@$parka_sel_ext.'>Parka</option>
                  <option value="remember" '.@$remember_sel_ext.'>Remember</option>
                  <option value="rocky_lane" '.@$rocky_lane_sel_ext.'>Rocky Lane</option>
                  <option value="ringing_1" '.@$ringing_1_sel_ext.'>Ringing 1</option>
                  <option value="ringing_2" '.@$ringing_2_sel_ext.'>Ringing 2</option>
                  <option value="ringing_3" '.@$ringing_3_sel_ext.'>Ringing 3</option>
                  <option value="ringing_4" '.@$ringing_4_sel_ext.'>Ringing 4</option>
                  <option value="ringing_5" '.@$ringing_5_sel_ext.'>Ringing 5</option>
                  <option value="ringing_6" '.@$ringing_6_sel_ext.'>Ringing 6</option>
                  <option value="ringing_7" '.@$ringing_7_sel_ext.'>Ringing 7</option>
                  <option value="ring_vintage" '.@$ring_vintage_sel_ext.'>Ring Vintage</option>
                  <option value="vibes" '.@$vibes_sel_ext.'>Vibes</option>
                  <option value="attack" '.@$attack_sel_ext.'>Attack</option>
                  <option value="doorbell" '.@$doorbell_sel_ext.'>Doorbell</option>
                  <option value="boogie" '.@$boogie_sel_ext.'>Boogie</option>
                  <option value="polka" '.@$polka_sel_ext.'>Polka</option>
                  <option value="classical_1" '.@$classical_1_sel_ext.'>Classical 1</option>
                  <option value="classical_2" '.@$classical_2_sel_ext.'>Classical 2</option>
                  <option value="classical_3" '.@$classical_3_sel_ext.'>Classical 3</option>
                  <option value="classical_4" '.@$classical_4_sel_ext.'>Classical 4</option>
                  <option value="alla_turca" '.@$alla_turca_sel_ext.'>Alla Turca</option>
                  <option value="entertainer" '.@$entertainer_sel_ext.'>Entertainer</option>
                  <option value="jollygood" '.@$jollygood_sel_ext.'>Jollygood</option>
                  <option value="in_the_saints" '.@$in_the_saints_sel_ext.'>In the Saints</option>
                  <option value="drunken_sailor" '.@$drunken_sailor_sel_ext.'>Drunken Sailor</option>
                  <option value="mary_had" '.@$mary_had_sel_ext.'>Mary Had</option>
                  <option value="shell_be_walking" '.@$shell_be_walking_sel_ext.'>She\'ll Be Walking</option>
                  <option value="pippi_longstocking" '.@$pippi_longstocking_sel_ext.'>Pippi Longstocking</option>
                  <option value="policehorn" '.@$policehorn_sel_ext.'>Policehorn</option>
                  <option value="synthesizer" '.@$synthesizer_sel_ext.'>Synthesizer</option>
                  <option value="after_work" '.@$after_work_sel_ext.'>After Work</option>
                  <option value="beep" '.@$beep_sel_ext.'>Beep</option>
                </select></p>
              </div>';
              if($sidekeys != 0) {
                echo '<div class="w3-row w3-quarter">
                <h5>Tasten:</h5>';
                for ($sk=1; $sk<=$sidekeys; $sk++) {
                  switch(${"sidekey".$sk}) {
                    case 'voice_box':
                      $voice_box = 'selected';
                      break;
                    case 'pbx_directory':
                      $pbx_directory = 'selected';
                      break;
                    case 'gappp_pickup':
                      $gappp_pickup = 'selected';
                      break;
                    case 'f_1':
                      $f_1 = 'selected';
                      break;
                    case 'gappp_door':
                      $gappp_door = 'selected';
                      break;
                  }
              echo '<label><b>SideKey '.$sk.'</b></label><br />
                    <select class="w3-select w3-padding" name="sidekey'.$sk.'" style="width:300px">
                      <option value="leer">leer</option>
                      <option value="voice_box" '.@$voice_box.'>Voicemail</option>
                      <option value="pbx_directory" '.@$pbx_directory.'>Telefonbuch</option>
                      <option value="gappp_pickup" '.@$gappp_pickup.'>PickUp</option>
                      <option value="f_1" '.@$f_1.'>Services</option>
                    </select><br />
                    <p> </p>';
                }                 
              echo '</div>';
              }
echo '      </div>
            <p><input type="hidden" name="settings" value="dect"><input type="hidden" name="dect_id" value="'.$id.'"><button class="w3-btn w3-blue" type="submit">Speichern</button></p> 
          </form> 
          </div>';
}
include('footer.php');
?>
