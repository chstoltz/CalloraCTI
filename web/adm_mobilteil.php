<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

require ('phpseclib3/autoload.php');
      use phpseclib3\Crypt\PublicKeyLoader;
      use phpseclib3\Math\BigInteger;

if (isset($_POST['startsub'])) {
  $command = '<SetRestrictedSubscriptionDuration restrictedSubscrDur="1" />';
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $command = '<SetDECTSubscriptionMode mode="Configured" />';
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $feedback = 'Registrierung für 2 Minuten gestartet.';
}
if (isset($_POST['submitdect'])) {
  extract($_POST, EXTR_OVERWRITE);
  $id = $nst-619;
  $command = "<SetPpProfile><ppProfile id=\"$id\" name=\"$nst\" ppData=\"UD_LedInfo=TRUE&#10;UD_DispLang=de&#10;\"/></SetPpProfile>";
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);

  $command = '<GetPublicKey />';
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);

  $xml = simplexml_load_string($result);
  $att = $xml->attributes();
  $exponent = $att['exponent'];
  $modulus = $att['modulus'];

  $rsa_key = PublicKeyLoader::load([
    'e' => new BigInteger($exponent, 16),
    'n' => new BigInteger($modulus, 16),
  ]);

  openssl_public_encrypt($sip_password, $enc_password, $rsa_key);
  $encryptedPassword = base64_encode($enc_password);
  openssl_public_encrypt($pin, $enc_pin, $rsa_key);
  $encryptedPin = base64_encode($enc_pin);

  $command = "<CreateFixedPP><user uid=\"$id\" relType=\"fixed\" altDisplayNum=\"$nst\" ppn=\"$id\" name=\"$name\" num=\"$sip_user\" addId=\"$nst\" pin=\"$encryptedPin\" sipAuthId=\"$sip_user\" sipPw=\"$encryptedPassword\" ppProfileId=\"$id\" monitoringMode=\"Active\" /><pp ppn=\"$id\" relType=\"fixed\" uid=\"$id\" ipei=\"$ipei\" ac=\"$auth_code\" encrypt=\"1\" /></CreateFixedPP>";
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  
  mysqli_query($db_conn,"INSERT INTO usr_mobilteil(nst,name,sip_user,sip_password,auth_code,ipei,model,pin,pin_lock,id) VALUES ('$nst','$name','$sip_user','$sip_password','$auth_code','$ipei','$model','$pin','$pin_lock','$id')");
  mysqli_query($db_conn,"INSERT INTO usr_telefon(nst,displayname,model) VALUES ('$nst','$nst','9999')");
}
if (isset($_GET['del'])) {
  $id = $_GET['del'];
  $nst = $_GET['nst'];
  $command = "<DeletePPUser uid=\"$id\" />";
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  mysqli_query($db_conn,"DELETE FROM usr_mobilteil WHERE id = '$id'");
  mysqli_query($db_conn,"DELETE FROM usr_telefon WHERE nst = '$nst'");
}
if (isset($_POST['submitedit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $command = '<GetPublicKey />';
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);

  $xml = simplexml_load_string($result);
  $att = $xml->attributes();
  $exponent = $att['exponent'];
  $modulus = $att['modulus'];

  $rsa_key = PublicKeyLoader::load([
    'e' => new BigInteger($exponent, 16),
    'n' => new BigInteger($modulus, 16),
  ]);

  openssl_public_encrypt($sip_password, $enc_password, $rsa_key);
  $encryptedPassword = base64_encode($enc_password);
  openssl_public_encrypt($pin, $enc_pin, $rsa_key);
  $encryptedPin = base64_encode($enc_pin);

  $command = "<SetPPUser><user uid=\"$id\" altDisplayNum=\"$submitedit\" ppn=\"$id\" name=\"$name\" num=\"$sip_user\" addId=\"$submitedit\" pin=\"$encryptedPin\" sipAuthId=\"$sip_user\" sipPw=\"$encryptedPassword\" monitoringMode=\"Active\" /></SetPPUser>";
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  
  $command = "<SetPPDev><pp ppn=\"$id\" uid=\"$id\" ipei=\"$ipei\" ac=\"$auth_code\" encrypt=\"1\" /></SetPPDev>";
  $result = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  
  mysqli_query($db_conn,"UPDATE usr_mobilteil SET name='$name',sip_user='$sip_user',sip_password='$sip_password',auth_code='$auth_code',ipei='$ipei',model='$model',pin='$pin',pin_lock='$pin_lock' WHERE nst='$submitedit'");
  }
  $command = '<GetPARK />';
  $park = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
      
  $park_xml = simplexml_load_string($park);
  $park_att = $park_xml->attributes();
  $park = $park_att->initialPARK;
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Mobilteile (PARK: <?php echo $park; ?>)</div>
</div>
<div class="w3-container">
<ul class="w3-ul">
  <li class="w3-bar">
    <div class="w3-bar-item" style="width:150px">Nebenstelle</div>
    <div class="w3-bar-item" style="width:150px">Name</div>
    <div class="w3-bar-item" style="width:150px">SIP Benutzer</div>
    <div class="w3-bar-item" style="width:150px">SIP Passwort</div>
    <div class="w3-bar-item" style="width:150px">Auth Code</div>
    <div class="w3-bar-item" style="width:200px">IPEI</div>
    <div class="w3-bar-item" style="width:100px">Modell</div>
    <div class="w3-bar-item" style="width:100px">Firmware</div>
    <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-bolt" title="Ladestand"></i></div>
    <div class="w3-bar-item" style="width:100px">PIN</div>
    <div class="w3-bar-item" style="width:200px">PIN Tastensperre</div>
  </li>
  <?php
$query = mysqli_query($db_conn,"SELECT * FROM adm_sipdect");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
}
if (isset($_GET['edit'])) {
  $edit_nst = $_GET['edit'];
}
$query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil");
while ($row = mysqli_fetch_array($query)) {
  extract($row, EXTR_OVERWRITE);
  $command = "<GetPPState ppn =\"$id\" />";
  $mt = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $xml = simplexml_load_string($mt);
  $att = $xml->attributes();
  $command = "<GetPPFirmwareUpdateStatus ppn=\"$id\" />";
  $dl = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $dl_xml = simplexml_load_string($dl);
  $dl_att = $dl_xml->ppFwSt->attributes();
  if($dl_att->state == 'active') {
    $dl_icon = '<i class="fa-solid fa-download" title="'.round($dl_att->bytes/1024).' kB"></i>';
  }

  if($nst == @$edit_nst) {
    echo '<form method="post" action="adm_mobilteil.php">
            <li class="w3-bar">
              <div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="name" value="'.$name.'"></div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="sip_user" value="'.$sip_user.'"></div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="sip_password" value="'.$sip_password.'"></div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="auth_code" value="'.$auth_code.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="ipei" value="'.$ipei.'"></div>
              <div class="w3-bar-item" style="width:100px">
                <select class="w3-select w3-padding" name="model">';
                  $model_query = mysqli_query($db_conn,"SELECT * FROM model WHERE dect = 1");
                  while($model_row = mysqli_fetch_array($model_query)) {
                    if($model == $model_row['model']) {
                      echo '<option value="'.$model_row['model'].'" selected>'.$model_row['model'].'</option>';
                    } else {
                      echo '<option value="'.$model_row['model'].'">'.$model_row['model'].'</option>';
                    }
                  }
          echo '</select>
              </div>
              <div class="w3-bar-item" style="width:100px">'.$att->swVersion.@$dl_icon.'</div>
              <div class="w3-bar-item" style="width:50px">';
              $bat_icon = match(true) {
                $att->batteryLevel >= 80 => '<i class="fa-solid fa-battery-full" style="color:#63E6BE;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel >= 50 => '<i class="fa-solid fa-battery-half" style="color:#FFD43B;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel >= 26 => '<i class="fa-solid fa-battery-quarter" style="color:#ff7800;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel <= 25 => '<i class="fa-solid fa-battery-empty" style="color:#e01b24;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                default => '<i class="fa-solid fa-question" title="Ladestand unbekannt"></i>',
              };
        echo  $bat_icon.'</div>     
              <div class="w3-bar-item" style="width:100px"><input class="w3-input w3-border" type="text" name="pin" value="'.$pin.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="pin_lock" value="'.$pin_lock.'"></div>
              <div class="w3-bar-item" style="width:50px">
                <input type="hidden" name="submitedit" value="'.$nst.'" />
                <input type="hidden" name="id" value="'.$id.'" />
                <button type="submit"><i class="fa-solid fa-check"></i></button>
              </div>
              <div class="w3-bar-item" style="width:50px">
                <a href="adm_mobilteil.php"><i class="fa-solid fa-xmark"></i></a>
              </div>
            </li>
            </form>';
            unset($dl_icon);
    } else {
      echo '<li class="w3-bar">
              <div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:150px">'.$name.'</div>
              <div class="w3-bar-item" style="width:150px">'.$sip_user.'</div>
              <div class="w3-bar-item" style="width:150px">'.$sip_password.'</div>
              <div class="w3-bar-item" style="width:150px">'.$auth_code.'</div>
              <div class="w3-bar-item" style="width:200px">'.$ipei.'</div>
              <div class="w3-bar-item" style="width:100px">'.$model.'</div>
              <div class="w3-bar-item" style="width:100px">'.$att->swVersion.@$dl_icon.'</div>
              <div class="w3-bar-item" style="width:50px">';
              $bat_icon = match(true) {
                $att->batteryLevel >= 80 => '<i class="fa-solid fa-battery-full" style="color:#63E6BE;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel >= 50 => '<i class="fa-solid fa-battery-half" style="color:#FFD43B;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel >= 26 => '<i class="fa-solid fa-battery-quarter" style="color:#ff7800;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                $att->batteryLevel <= 25 => '<i class="fa-solid fa-battery-empty" style="color:#e01b24;" title="Ladestand: '.$att->batteryLevel.'"></i>',
                default => '<i class="fa-solid fa-question" title="Ladestand unbekannt"></i>',
              };
        echo  $bat_icon.'</div>
              <div class="w3-bar-item" style="width:100px">'.$pin.'</div>
              <div class="w3-bar-item" style="width:200px">'.$pin_lock.'</div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_mobilteil.php?edit='.$nst.'"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_mobilteil.php?del='.$id.'&nst='.$nst.'" onClick="return confirm(\'Möchtest du das Mobilteil '.$name.' mit der Nebenstelle '.$nst.' wirklich löschen?\');"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>
            </li>';
            unset($dl_icon);
    }
  }

?>
<form method="post" action="adm_mobilteil.php">
  <li class="w3-bar">
    <?php
      $userquery = mysqli_query($db_conn,"SELECT adm_benutzer.username,adm_benutzer.nst FROM adm_benutzer LEFT JOIN usr_telefon ON adm_benutzer.nst = usr_telefon.nst WHERE usr_telefon.nst IS NULL AND adm_benutzer.username != 'admin'");
      if(mysqli_num_rows($userquery) >= 1) {
    ?>
    <div class="w3-bar-item" style="width:150px">
    <select class="w3-select w3-padding" name="nst">
    <?php
        while($userrow=mysqli_fetch_array($userquery)) {
          $user_nst = $userrow['nst'];
          $user_username = $userrow['username'];
          echo '<option value="'.$user_nst.'">['.$user_nst.'] '.$user_username.'</option>';
        }
    ?>
    </select>
    </div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="name"></div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="sip_user"></div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="sip_password"></div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="auth_code"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="ipei"></div>
    <div class="w3-bar-item" style="width:100px">
      <select class="w3-select w3-padding" name="model">
        <?php
          $model_query = mysqli_query($db_conn,"SELECT * FROM model WHERE dect = 1");
          while($model_row = mysqli_fetch_array($model_query)) {
            $model_row_model = $model_row['model'];
            echo '<option value="'.$model_row_model.'">'.$model_row_model.'</option>';
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item" style="width:100px"> </div>
    <div class="w3-bar-item" style="width:50px"> </div>
    <div class="w3-bar-item" style="width:100px"><input class="w3-input w3-border" type="text" name="pin"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="pin_lock"></div>
    <div class="w3-bar-item" style="width:50px">
      <input type="hidden" name="submitdect" />
      <button type="submit"><i class="fa-solid fa-check"></i></button>
    </div>
    <?php
      } else {
        echo '<div class="w3-bar-item">Keine freien Benutzer. Bitte erst einen <a href="adm_benutzer.php">Benutzer anlegen</a>.</div>';
      }
    ?>
  </li>
</form>
</ul>
<form action="adm_mobilteil.php" method="post">
<p><input type="hidden" name="startsub" />
<button class="w3-btn w3-blue" type="submit">DECT Registrierung starten</button></p>
</form>
</div>
<?php
include('footer.php');
?>
