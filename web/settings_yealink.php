<?php

$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
  if($call_forward_disabled == 1) { $cfd_checked = 'checked'; } else { $cfd_checked = ''; }
  $dst_config = 'dst_'.$dst_config;
  $$dst_config = 'selected';
  if($call_waiting_tone == 1) { $cwt_checked = 'checked'; } else { $cwt_checked = ''; }
  if($sip_explicit_mwi_subscription == 1) { $mwi_checked = 'checked'; } else { $mwi_checked = ''; }
  if($mwi_led_line == 1) { $mwi_ll_checked = 'checked'; } else { $mwi_ll_checked = ''; }
  if($missed_calls_indicator_disabled == 1) { $mci_checked = 'checked'; } else { $mci_checked = ''; }
  if($inactivity_brightness_level == 1) { $ibl_checked = 'checked'; } else { $ibl_checked = ''; }
  $brightness_level = 'bl_'.$brightness_level;
  $$brightness_level = 'selected';
  if($switch_focus_to_ringing_line == 1) { $sf_checked = 'checked'; } else { $sf_checked = ''; }
  $bl_on_time = 'blot_'.$bl_on_time;
  $$bl_on_time = 'selected';
  $screen_save_time = 'ssw_'.$screen_save_time;
  $$screen_save_time = 'selected';
  $ringer_volume = 'rv_'.$ringer_volume;
  $$ringer_volume = 'selected';
  if($line1_ring_tone == 'Splash') {
    $rt_Splash = 'selected';
  } elseif($line1_ring_tone == 'Silent') {
    $rt_Silent = 'selected';
  } elseif($line1_ring_tone == 'Custom') {
    $rt_Custom = 'selected';
  } else {
    $line1_ring_tone = 'rt_'.$line1_ring_tone;
    $$line1_ring_tone = 'selected';
  }
}

echo '<form class="w3-container w3-padding-large" action="einstellungen.php" method="post" name="telefon">
        <div class="w3-container" style="width:75%">
          <div class="w3-row w3-quarter">
	    <h5>Audioeinstellungen:</h5>
            <p><label><b>Klingeltonlautstärke*:</b></label><br />
            <select class="w3-select w3-padding" name="ringer_volume" style="width:200px">
              <option value="0" '.@$rv_0.'>0</option>
              <option value="1" '.@$rv_1.'>1</option>
              <option value="2" '.@$rv_2.'>2</option>
              <option value="3" '.@$rv_3.'>3</option>
              <option value="4" '.@$rv_4.'>4</option>
              <option value="5" '.@$rv_5.'>5</option>
              <option value="6" '.@$rv_6.'>6</option>
              <option value="7" '.@$rv_7.'>7</option>
              <option value="8" '.@$rv_8.'>8</option>
              <option value="9" '.@$rv_9.'>9</option>
              <option value="10" '.@$rv_10.'>10</option>
              <option value="11" '.@$rv_11.'>11</option>
              <option value="12" '.@$rv_12.'>12</option>
              <option value="13" '.@$rv_13.'>13</option>
              <option value="14" '.@$rv_14.'>14</option>
              <option value="15" '.@$rv_15.'>15</option>
            </select></p>
            <p><label><b>Leitung 1 Klingelton:</b></label><br />
            <select class="w3-select w3-padding" name="line1_ring_tone" style="width:200px">
              <option value="Ring1" '.@$rt_Ring1.'>Klingelton 1</option>
              <option value="Ring2" '.@$rt_Ring2.'>Klingelton 2</option>
              <option value="Ring3" '.@$rt_Ring3.'>Klingelton 3</option>
              <option value="Ring4" '.@$rt_Ring4.'>Klingelton 4</option>
              <option value="Ring5" '.@$rt_Ring5.'>Klingelton 5</option>
              <option value="Ring6" '.@$rt_Ring6.'>Klingelton 6</option>
              <option value="Ring7" '.@$rt_Ring7.'>Klingelton 7</option>
              <option value="Ring8" '.@$rt_Ring8.'>Klingelton 8</option>
              <option value="Silent" '.@$rt_Silent.'>Klingelton 9</option>
              <option value="Splash" '.@$rt_Splash.'>Klingelton 10</option>
              <option value="Custom" '.@$rt_Custom.'>Klingelton 11</option>
            </select></p>
            <p><label>URL Eigener Klingelton 1*:</label>
            <input type="text" class="w3-input w3-border" name="custom_ringtone_1" value="'.@$custom_ringtone_1.'" style="width:200px"></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Displayeinstellungen:</h5>
            <p><label><b>Bildschirmhelligkeit:</b></label><br />
            <select class="w3-select w3-padding" name="bl_on_time" style="width:200px">
              <option value="1" '.@$bl_1.'>1</option>
              <option value="2" '.@$bl_2.'>2</option>
              <option value="3" '.@$bl_3.'>3</option>
              <option value="4" '.@$bl_4.'>4</option>
              <option value="5" '.@$bl_5.'>5</option>
              <option value="6" '.@$bl_6.'>6</option>
              <option value="7" '.@$bl_7.'>7</option>
              <option value="8" '.@$bl_8.'>8</option>
              <option value="9" '.@$bl_9.'>9</option>
              <option value="10" '.@$bl_10.'>10</option>
            </select></p>
            <p><label><b>Hintergrundbeleuchtung (1-36000s):</b></label><br />
            <select class="w3-select w3-padding" name="bl_on_time" style="width:200px">
              <option value="0" '.@$blot_0.'>Immer an</option>
              <option value="1800" '.@$blot_1800.'>30 Minuten</option>
              <option value="3600" '.@$blot_3600.'>1 Stunde</option>
              <option value="7200" '.@$blot_7200.'>2 Stunden</option>
              <option value="14400" '.@$blot_14400.'>4 Stunden</option>
              <option value="21600" '.@$blot_21600.'>6 Stunden</option>
              <option value="28800" '.@$blot_28800.'>8 Stunden</option>
              <option value="43200" '.@$blot_43200.'>12 Stunden</option>
            </select></p>
            <p><input type="checkbox" class="w3-check w3-border" name="inactivity_brightness_level" '.@$ibl_checked.'>&nbsp;&nbsp;
            <label><b>Hintergrundbeleuchtung bei Inaktivität</b></label></p>
            <p><label><b>Bildschirmschoner Zeit (1-14400s):</b></label><br />
            <select class="w3-select w3-padding" name="screen_save_time" style="width:200px">
              <option value="0" '.@$ssw_0.'>Aus</option>
              <option value="1800" '.@$ssw_1800.'>30 Minuten</option>
              <option value="3600" '.@$ssw_3600.'>1 Stunde</option>
              <option value="7200" '.@$ssw_7200.'>2 Stunden</option>
              <option value="14400" '.@$ssw_14400.'>4 Stunden</option>
              <option value="21600" '.@$ssw_21600.'>6 Stunden</option>
              <option value="28800" '.@$ssw_28800.'>8 Stunden</option>
              <option value="43200" '.@$ssw_43200.'>12 Stunden</option>
            </select></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Telefonieeinstellungen:</h5>
            <p><input type="checkbox" class="w3-check w3-border" name="call_forward_disabled" '.@$cfd_checked.'>&nbsp;&nbsp;
            <label><b>Umleitung am Telefon deaktiviert</b></label></p>
            <p><input type="checkbox" class="w3-check w3-border" name="missed_calls_indicator_disabled" '.@$mci_checked.'>&nbsp;&nbsp;
            <label><b>LED bei verpassten Anrufe deaktivieren</b></label></p>
            <p><input type="checkbox" class="w3-check w3-border" name="call_waiting_tone" '.@$cwt_checked.'>&nbsp;&nbsp;
            <label><b>Anklopfen</b></label></p>
            <p><input type="checkbox" class="w3-check w3-border" name="sip_explicit_mwi_subscription" '.@$mwi_checked.'>&nbsp;&nbsp;
            <label><b>MWI aktiv*</b></label></p>
            <p><input type="checkbox" class="w3-check w3-border" name="mwi_led_line" '.@$mwi_ll_checked.'>&nbsp;&nbsp;
            <label><b>MWI LED aktiv*</b></label></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Telefoneinstellungen:</h5>
            <p><label><b>Sommerzeit:</b></label><br />
            <select class="w3-select w3-padding" name="dst_config" style="width:200px">
              <option value="0" '.@$dst_0.'>Deaktiviert</option>
              <option value="1" '.@$dst_1.'>Aktiviert</option>
              <option value="2" '.@$dst_2.'>Automatisch</option>
            </select></p>
          </div>
        </div>
	<p><input type="checkbox" class="w3-check w3-border" name="restart_after_save">&nbsp;&nbsp;
        <label><b>Telefon direkt neu starten (nur bei Änderungen notwendig, die mit * gekennzeichnet sind).</b></label></p>
        <p><input type="hidden" name="settings" value="telefon"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
      </form>
    </div>';

    ?>
