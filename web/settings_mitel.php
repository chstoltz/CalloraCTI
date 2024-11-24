<?php

$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
  if($call_forward_disabled == 1) { $cfd_checked = 'checked'; } else { $cfd_checked = ''; }
  $$idle_screen_font_color = 'selected';
  $dst_config = 'dst_'.$dst_config;
  $$dst_config = 'selected';
  if($call_waiting_tone == 1) { $cwt_checked = 'checked'; } else { $cwt_checked = ''; }
  if($sip_explicit_mwi_subscription == 1) { $mwi_checked = 'checked'; } else { $mwi_checked = ''; }
  $mwi_led_line = 'mwi_'.$mwi_led_line;
  $$mwi_led_line = 'selected';
  if($missed_calls_indicator_disabled == 1) { $mci_checked = 'checked'; } else { $mci_checked = ''; }
  $brightness_level = 'bl_'.$brightness_level;
  $$brightness_level = 'selected';
  $inactivity_brightness_level = 'ibl_'.$inactivity_brightness_level;
  $$inactivity_brightness_level = 'selected';
  if($switch_focus_to_ringing_line == 1) { $sf_checked = 'checked'; } else { $sf_checked = ''; }
  $handset_volume = 'hv_'.$handset_volume;
  $$handset_volume = 'selected';
  $speaker_volume = 'sv_'.$speaker_volume;
  $$speaker_volume = 'selected';
  $headset_volume = 'hsv_'.$headset_volume;
  $$headset_volume = 'selected';
  $ringer_volume = 'rv_'.$ringer_volume;
  $$ringer_volume = 'selected';
  $audio_mode = 'am_'.$audio_mode;
  $$audio_mode = 'selected';
  if($line1_ring_tone == -1) {
    $rt_gl = 'selected';
  } else {
    $line1_ring_tone = 'rt_'.$line1_ring_tone;
    $$line1_ring_tone = 'selected';
  }
}

echo '<form class="w3-container w3-padding-large" action="einstellungen.php" method="post" name="telefon">
        <div class="w3-container" style="width:75%">
          <div class="w3-row w3-quarter">
	    <h5>Audioeinstellungen:</h5>
            <p><label><b>Hörerlautstärke*:</b></label><br />
            <select class="w3-select w3-padding" name="handset_volume" style="width:200px">
              <option value="0" '.@$hv_0.'>0</option>
              <option value="1" '.@$hv_1.'>1</option>
              <option value="2" '.@$hv_2.'>2</option>
              <option value="3" '.@$hv_3.'>3</option>
              <option value="4" '.@$hv_4.'>4</option>
              <option value="5" '.@$hv_5.'>5</option>
              <option value="6" '.@$hv_6.'>6</option>
              <option value="7" '.@$hv_7.'>7</option>
              <option value="8" '.@$hv_8.'>8</option>
              <option value="9" '.@$hv_9.'>9</option>
            </select></p>
            <p><label><b>Lautspecherlautstärke*:</b></label><br />
            <select class="w3-select w3-padding" name="speaker_volume" style="width:200px">
              <option value="0" '.@$sv_0.'>0</option>
              <option value="1" '.@$sv_1.'>1</option>
              <option value="2" '.@$sv_2.'>2</option>
              <option value="3" '.@$sv_3.'>3</option>
              <option value="4" '.@$sv_4.'>4</option>
              <option value="5" '.@$sv_5.'>5</option>
              <option value="6" '.@$sv_6.'>6</option>
              <option value="7" '.@$sv_7.'>7</option>
              <option value="8" '.@$sv_8.'>8</option>
              <option value="9" '.@$sv_9.'>9</option>
            </select></p>
            <p><label><b>Headsetlautstärke*:</b></label><br />
            <select class="w3-select w3-padding" name="headset_volume" style="width:200px">
              <option value="0" '.@$hsv_0.'>0</option>
              <option value="1" '.@$hsv_1.'>1</option>
              <option value="2" '.@$hsv_2.'>2</option>
              <option value="3" '.@$hsv_3.'>3</option>
              <option value="4" '.@$hsv_4.'>4</option>
              <option value="5" '.@$hsv_5.'>5</option>
              <option value="6" '.@$hsv_6.'>6</option>
              <option value="7" '.@$hsv_7.'>7</option>
              <option value="8" '.@$hsv_8.'>8</option>
              <option value="9" '.@$hsv_9.'>9</option>
            </select></p>
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
            </select></p>
            <p><label><b>Audiomodus:</b></label><br />
            <select class="w3-select w3-padding" name="audio_mode" style="width:200px">
              <option value="0" '.@$am_0.'>Lautsprecher</option>
              <option value="1" '.@$am_1.'>Headset</option>
              <option value="2" '.@$am_2.'>Lautsprecher/Headset</option>
              <option value="3" '.@$am_3.'>Headset/Lautsprecher</option>
            </select></p>
            <p><label><b>Leitung 1 Klingelton:</b></label><br />
            <select class="w3-select w3-padding" name="line1_ring_tone" style="width:200px">
              <option value="-1" '.@$rt_gl.'>Global</option>
              <option value="0" '.@$rt_0.'>Klingelton 1</option>
              <option value="1" '.@$rt_1.'>Klingelton 2</option>
              <option value="2" '.@$rt_2.'>Klingelton 3</option>
              <option value="3" '.@$rt_3.'>Klingelton 4</option>
              <option value="4" '.@$rt_4.'>Klingelton 5</option>
              <option value="5" '.@$rt_5.'>Klingelton 6</option>
              <option value="6" '.@$rt_6.'>Klingelton 7</option>
              <option value="7" '.@$rt_7.'>Klingelton 8</option>
              <option value="8" '.@$rt_8.'>Klingelton 9</option>
              <option value="9" '.@$rt_9.'>Klingelton 10</option>
              <option value="10" '.@$rt_10.'>Klingelton 11</option>
              <option value="11" '.@$rt_11.'>Klingelton 12</option>
              <option value="12" '.@$rt_12.'>Klingelton 13</option>
              <option value="13" '.@$rt_13.'>Klingelton 14</option>
              <option value="14" '.@$rt_14.'>Klingelton 15</option>
              <option value="15" '.@$rt_15.'>Klingelton 16</option>
              <option value="20" '.@$rt_20.'>Velocity</option>
              <option value="21" '.@$rt_21.'>Skyline</option>
              <option value="22" '.@$rt_22.'>Rise</option>
              <option value="23" '.@$rt_23.'>Daybreak</option>
              <option value="24" '.@$rt_24.'>After Hours</option>
              <option value="25" '.@$rt_25.'>Open Road</option>
              <option value="26" '.@$rt_26.'>Pronto</option>
              <option value="27" '.@$rt_27.'>Voyage</option>
              <option value="28" '.@$rt_28.'>Bloom</option>
              <option value="29" '.@$rt_29.'>Move</option>
              <option value="100" '.@$rt_100.'>Eigener Klingelton 1</option>
              <option value="101" '.@$rt_101.'>Eigener Klingelton 2</option>
              <option value="102" '.@$rt_102.'>Eigener Klingelton 3</option>
              <option value="103" '.@$rt_103.'>Eigener Klingelton 4</option>
              <option value="104" '.@$rt_104.'>Eigener Klingelton 5</option>
              <option value="105" '.@$rt_105.'>Eigener Klingelton 6</option>
              <option value="106" '.@$rt_106.'>Eigener Klingelton 7</option>
              <option value="107" '.@$rt_107.'>Eigener Klingelton 8</option>
            </select></p>
            <p><label>URL Eigener Klingelton 1*:</label>
            <input type="text" class="w3-input w3-border" name="custom_ringtone_1" value="'.@$custom_ringtone_1.'" style="width:200px"></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Displayeinstellungen:</h5>
            <p><label><b>Schriftfarbe Ruhebildschirm*:</b></label><br />
            <select class="w3-select w3-padding" name="idle_screen_font_color" style="width:200px">
              <option value="blue" '.@$blue.'>blau</option>
              <option value="black" '.@$black.'>schwarz</option>
              <option value="white" '.@$white.'>weiß</option>
            </select></p>
            <p><label><b>Bildschirmhelligkeit:</b></label><br />
            <select class="w3-select w3-padding" name="brightness_level" style="width:200px">
              <option value="1" '.@$bl_1.'>1</option>
              <option value="2" '.@$bl_2.'>2</option>
              <option value="3" '.@$bl_3.'>3</option>
              <option value="4" '.@$bl_4.'>4</option>
              <option value="5" '.@$bl_5.'>5</option>
            </select></p>
            <p><label><b>Hintergrundbeleuchtung (1-36000s):</b></label>
            <input type="text" class="w3-input w3-border" name="bl_on_time" value="'.@$bl_on_time.'" style="width:200px"></p>
            <p><label><b>Bildschirmhelligkeit (Inaktivität):</b></label><br />
            <select class="w3-select w3-padding" name="inactivity_brightness_level" style="width:200px">
              <option value="0" '.@$ibl_0.'>0</option>
              <option value="1" '.@$ibl_1.'>1</option>
              <option value="2" '.@$ibl_2.'>2</option>
              <option value="3" '.@$ibl_3.'>3</option>
              <option value="4" '.@$ibl_4.'>4</option>
            </select></p>
            <p><label><b>Bildschirmschoner Zeit (1-14400s):</b></label>
            <input type="text" class="w3-input w3-border" name="screen_save_time" value="'.@$screen_save_time.'" style="width:200px"></p>
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
            <p><label><b>Leitung für MWI*:</b></label><br />
            <select class="w3-select w3-padding" name="mwi_led_line" style="width:200px">
              <option value="0" '.@$mwi_0.'>Alle</option>
              <option value="1" '.@$mwi_1.'>Leitung 1</option>
              <option value="2" '.@$mwi_2.'>Leitung 2</option>
              <option value="3" '.@$mwi_3.'>Leitung 3</option>
            </select></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Telefoneinstellungen:</h5>
            <p><input type="checkbox" class="w3-check w3-border" name="switch_focus_to_ringing_line" '.@$sf_checked.'>&nbsp;&nbsp;
            <label><b>Wechsle Sicht zu eingehendem Anruf</b></label></p>
            <p><label><b>Einstellung Zeitumstellung:</b></label><br />
            <select class="w3-select w3-padding" name="dst_config" style="width:200px">
              <option value="0" '.@$dst_0.'>aus</option>
              <option value="1" '.@$dst_1.'>30 Minuten Sommerzeit</option>
              <option value="2" '.@$dst_2.'>1 Stunde Sommerzeit</option>
              <option value="3" '.@$dst_3.'>automatisch</option>
            </select></p>
	    <p><label><b>Tastenmodul</b></label><br />
      <select class="w3-select w3-padding" name="exp" style="width:200px">
        <option value="0" '.@$exp_0.'>keins</option>
        <option value="680" '.@$exp_1.'>M680/M690</option>
        <option value="685" '.@$exp_2.'>M685/M695</option>
        </select></p>
          </div>
        </div>
	<p><input type="checkbox" class="w3-check w3-border" name="restart_after_save">&nbsp;&nbsp;
        <label><b>Telefon direkt neu starten (nur bei Änderungen notwendig, die mit * gekennzeichnet sind).</b></label></p>
        <p><input type="hidden" name="settings" value="telefon"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
      </form>
    </div>';

    ?>
    