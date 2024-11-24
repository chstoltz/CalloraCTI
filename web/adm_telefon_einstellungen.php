<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

if(isset($_POST['submit'])) {
  extract($_POST, EXTR_OVERWRITE);

  if(isset($web_interface_enabled)) { $web_interface_enabled = 1; } else { $web_interface_enabled = 0; }
  if(isset($sip_whitelist)) { $sip_whitelist = 1;} else { $sip_whitelist = 0; }
  if(isset($polling)) { $polling = 1; } else { $polling = 0; }
  if(isset($options_password_enabled)) { $options_password_enabled = 1;} else { $options_password_enabled = 0; }

  mysqli_query($db_conn,"UPDATE adm_einstellungen SET xml_application_post_list='$xml_application_post_list',ntp_server1='$ntp_server1',ntp_server2='$ntp_server2',ntp_server3='$ntp_server3',admin_password_phone='$admin_password_phone',web_interface_enabled='$web_interface_enabled',ip_whitelist='$ip_whitelist',sip_whitelist='$sip_whitelist',polling='$polling',options_password_enabled='$options_password_enabled',codecs='$codecs'");
  $feedback = 'Gespeichert!';
  if(isset($restart)) {
    $query = mysqli_query($db_conn,"SELECT nst FROM regevent WHERE regstate = 'REGISTERED'");
    if(mysqli_num_rows($query) != 0) {
      while($row = mysqli_fetch_array($query)) {
        $nst = $row['nst'];
        $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Command: FastReboot"/></AastraIPPhoneExecute>';
        push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      }
      $feedback .= ' Neustart wurde eingeleitet.';
    }
  }
}

$query1 = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen");
$array1 = mysqli_fetch_array($query1);
extract($array1, EXTR_OVERWRITE);

if($web_interface_enabled == 1) { $wie = 'checked'; }
if($sip_whitelist == 1) { $sw = 'checked'; }
if($polling == 1) { $pl = 'checked'; }
if($options_password_enabled ==1) { $ope = 'checked'; }
$codecs = 'Codec_'.$codecs;
$$codecs = 'selected';
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Telefoneinstellungen</div>
</div>
<div class="w3-container">
  <form action="adm_telefon_einstellungen.php" method="post">
    <div class="w3-row w3-quarter w3-padding-large">
      <p><label><b>XML Push Server:</b></label>
      <input class="w3-input w3-border" type="text" name="xml_application_post_list" value="<?php echo $xml_application_post_list; ?>" style="width:300px"></p>
      <p><label><b>NTP Server 1:</b></label>
      <input class="w3-input w3-border" type="text" name="ntp_server1" value="<?php echo $ntp_server1; ?>" style="width:300px"></p>
      <p><label><b>NTP Server 2:</b></label>
      <input class="w3-input w3-border" type="text" name="ntp_server2" value="<?php echo $ntp_server2; ?>" style="width:300px"></p>
      <p><label><b>NTP Server 3:</b></label>
      <input class="w3-input w3-border" type="text" name="ntp_server3" value="<?php echo $ntp_server3; ?>" style="width:300px"></p>
      <p><label><b>Admin Passwort Telefone:</b></label>
      <input class="w3-input w3-border" type="text" name="admin_password_phone" value="<?php echo $admin_password_phone; ?>" style="width:300px"></p>
      <p><input class="w3-check" type="checkbox" name="web_interface_enabled" <?php echo @$wie; ?>>
      <label><b>Web Interface am Telefon aktiv</b></label></p>
      <p><label><b>Whitelist für SIP Options:</b></label>
      <input class="w3-input w3-border" type="text" name="ip_whitelist" value="<?php echo $ip_whitelist; ?>" style="width:300px"></p>
      <p><input class="w3-check" type="checkbox" name="sip_whitelist" <?php echo @$sw; ?>>
      <label><b>SIP Whitelist aktvieren</b></label></p>
      <p><input class="w3-check" type="checkbox" name="polling" <?php echo @$pl; ?>>
      <label><b>Polling aktiv (für Gesprächsdaueranzeige, nur Mitel)</b></label></p>
      <p><input type="checkbox" class="w3-check w3-border" name="options_password_enabled" <?php echo @$ope; ?>>&nbsp;&nbsp;
      <label><b>Telefoneinstellungen passwortgeschützt</b></label></p>
    </div>
    <div class="w3-row w3-quarter w3-padding-large">
      <p><label><b>Codecliste:</b></label><br />
      <select class="w3-select w3-padding" name="codecs">
        <option value="1" <?php echo @$codec_1; ?>>G.722 (Prio 1) & G.711a (Prio 2)</option>
        <option value="2" <?php echo @$codec_2; ?>>G.711a (Prio 1) & G.722 (Prio 2)</option>
        <option value="3" <?php echo @$codec_3; ?>>G.711a (ausschließlich)</option>
        <option value="4" <?php echo @$codec_4; ?>>G.722 (ausschließlich)</option>
      </select></p>
    </div>
    <div class="w3-row">
    <p><input type="checkbox" class="w3-check w3-border" name="restart">&nbsp;&nbsp;
    <label><b>Alle Telefone direkt neu starten.</b></label></p>
    <p><input type="hidden" name="submit" />
    <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
</div>
    </form>
  </div>

<?php

include('footer.php');

?>