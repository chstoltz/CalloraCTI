<?php

$query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
  if($call_waiting_tone == 1) { $cwt_checked = 'checked'; } else { $cwt_checked = ''; }
  if($sip_explicit_mwi_subscription == 1) { $mwi_checked = 'checked'; } else { $mwi_checked = ''; }
  $brightness_level = 'bl_'.$brightness_level;
  $$brightness_level = 'selected';
  $inactivity_brightness_level = 'ibl_'.$inactivity_brightness_level;
  $$inactivity_brightness_level = 'selected';
  $handset_volume = 'hv_'.$handset_volume;
  $$handset_volume = 'selected';
  $speaker_volume = 'sv_'.$speaker_volume;
  $$speaker_volume = 'selected';
  $headset_volume = 'hsv_'.$headset_volume;
  $$headset_volume = 'selected';
  $ringer_volume = 'rv_'.$ringer_volume;
  $$ringer_volume = 'selected';
  if($line1_ring_tone == 'Silent') {
    $rt_Silent = 'selected';
  } elseif ($line1_ring_tone == 'Custom') {
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
              <option value="10" '.@$hv_10.'>10</option>
              <option value="11" '.@$hv_11.'>11</option>
              <option value="12" '.@$hv_12.'>12</option>
              <option value="13" '.@$hv_13.'>13</option>
              <option value="14" '.@$hv_14.'>14</option>
              <option value="15" '.@$hv_15.'>15</option>
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
              <option value="10" '.@$sv_10.'>10</option>
              <option value="11" '.@$sv_11.'>11</option>
              <option value="12" '.@$sv_12.'>12</option>
              <option value="13" '.@$sv_13.'>13</option>
              <option value="14" '.@$sv_14.'>14</option>
              <option value="15" '.@$sv_15.'>15</option>
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
              <option value="10" '.@$hsv_10.'>10</option>
              <option value="11" '.@$hsv_11.'>11</option>
              <option value="12" '.@$hsv_12.'>12</option>
              <option value="13" '.@$hsv_13.'>13</option>
              <option value="14" '.@$hsv_14.'>14</option>
              <option value="15" '.@$hsv_15.'>15</option>
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
              <option value="10" '.@$rv_10.'>10</option>
              <option value="11" '.@$rv_11.'>11</option>
              <option value="12" '.@$rv_12.'>12</option>
              <option value="13" '.@$rv_13.'>13</option>
              <option value="14" '.@$rv_14.'>14</option>
              <option value="15" '.@$rv_15.'>15</option>
            </select></p>
            <p><label><b>Leitung 1 Klingelton:</b></label><br />
            <select class="w3-select w3-padding" name="line1_ring_tone" style="width:200px">
              <option value="Ringer1" '.@$rt_Ringer1.'>Klingelton 1</option>
              <option value="Ringer2" '.@$rt_Ringer2.'>Klingelton 2</option>
              <option value="Ringer3" '.@$rt_Ringer3.'>Klingelton 3</option>
              <option value="Ringer4" '.@$rt_Ringer4.'>Klingelton 4</option>
              <option value="Ringer5" '.@$rt_Ringer5.'>Klingelton 5</option>
              <option value="Ringer6" '.@$rt_Ringer6.'>Klingelton 6</option>
              <option value="Ringer7" '.@$rt_Ringer7.'>Klingelton 7</option>
              <option value="Ringer8" '.@$rt_Ringer8.'>Klingelton 8</option>
              <option value="Ringer9" '.@$rt_Ringer9.'>Klingelton 9</option>
              <option value="Ringer10" '.@$rt_Ringer10.'>Klingelton 10</option>
              <option value="Silent" '.@$rt_Silent.'>Ruhe</option>
              <option value="Custom" '.@$rt_Custom.'>Eigener Klingelton</option>
            </select></p>
            <p><label>URL Eigener Klingelton 1*:</label>
            <input type="text" class="w3-input w3-border" name="custom_ringtone_1" value="'.@$custom_ringtone_1.'" style="width:200px"></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Displayeinstellungen:</h5>
            <p><label><b>Bildschirmhelligkeit:</b></label><br />
            <select class="w3-select w3-padding" name="brightness_level" style="width:200px">
              <option value="3" '.@$bl_3.'>3</option>
              <option value="4" '.@$bl_4.'>4</option>
              <option value="5" '.@$bl_5.'>5</option>
              <option value="6" '.@$bl_6.'>6</option>
              <option value="7" '.@$bl_7.'>7</option>
              <option value="8" '.@$bl_8.'>8</option>
              <option value="9" '.@$bl_9.'>9</option>
              <option value="10" '.@$bl_10.'>10</option>
              <option value="11" '.@$bl_11.'>11</option>
              <option value="12" '.@$bl_12.'>12</option>
              <option value="13" '.@$bl_13.'>13</option>
              <option value="14" '.@$bl_14.'>14</option>
              <option value="15" '.@$bl_15.'>15</option>
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
              <option value="5" '.@$ibl_5.'>5</option>
              <option value="6" '.@$ibl_6.'>6</option>
              <option value="7" '.@$ibl_7.'>7</option>
              <option value="8" '.@$ibl_8.'>8</option>
              <option value="9" '.@$ibl_9.'>9</option>
              <option value="10" '.@$ibl_10.'>10</option>
              <option value="11" '.@$ibl_11.'>11</option>
              <option value="12" '.@$ibl_12.'>12</option>
              <option value="13" '.@$ibl_13.'>13</option>
              <option value="14" '.@$ibl_14.'>14</option>
              <option value="15" '.@$ibl_15.'>15</option>
            </select></p>
          </div>
          <div class="w3-row w3-quarter">
	    <h5>Telefonieeinstellungen:</h5>
            <p><input type="checkbox" class="w3-check w3-border" name="call_waiting_tone" '.@$cwt_checked.'>&nbsp;&nbsp;
            <label><b>Anklopfen</b></label></p>
            <p><input type="checkbox" class="w3-check w3-border" name="sip_explicit_mwi_subscription" '.@$mwi_checked.'>&nbsp;&nbsp;
            <label><b>MWI aktiv*</b></label></p>
          </div>
        </div>
	<p><input type="checkbox" class="w3-check w3-border" name="restart_after_save">&nbsp;&nbsp;
        <label><b>Telefon direkt neu starten (nur bei Änderungen notwendig, die mit * gekennzeichnet sind).</b></label></p>
        <p><input type="hidden" name="settings" value="telefon"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
      </form>
    </div>';

?>
