<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

if (isset($_POST['submitphone'])) {
  extract($_POST, EXTR_OVERWRITE);
  $screenname = db_cell('username','adm_benutzer',$nst);
  mysqli_query($db_conn,"INSERT INTO usr_telefon(screenname,username,displayname,authname,password,proxy,registrar,nst,mac,model,exp) VALUES ('$screenname','$username','$nst','$authname','$password','$proxy_ip','$registrar_ip','$nst','$mac','$model','$exp')");
  provision($nst);
  sipnotify($nst);
}
if (isset($_GET['del'])) {
  $displayname = $_GET['del'];
  mysqli_query($db_conn,"DELETE FROM usr_telefon WHERE displayname = '$displayname'");
}
if (isset($_GET['reboot'])) {
  $nst = $_GET['reboot'];
  reboot($nst);
}
if (isset($_GET['prov'])) {
  $nst = $_GET['prov'];
  provision($nst);
  sipnotify($nst);
}
if (isset($_POST['submitedit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $displayname = $_POST['submitedit'];
  mysqli_query($db_conn,"UPDATE usr_telefon SET username='$username',authname='$authname',password='$password',proxy='$proxy_ip',registrar='$registrar_ip',model='$model',mac='$mac',exp='$exp' WHERE displayname = '$displayname'");
  provision($displayname);
  sipnotify($displayname);
}
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Telefone</div>
</div>
<div class="w3-container">
<ul class="w3-ul">
  <li class="w3-bar">
    <div class="w3-bar-item" style="width:150px">Nebenstelle</div>
    <div class="w3-bar-item" style="width:150px">SIP Benutzer</div>
    <div class="w3-bar-item" style="width:150px">SIP Auth</div>
    <div class="w3-bar-item" style="width:150px">SIP Passwort</div>
    <div class="w3-bar-item" style="width:150px">Proxy</div>
    <div class="w3-bar-item" style="width:150px">Registrar</div>
    <div class="w3-bar-item" style="width:100px">Modell</div>
    <div class="w3-bar-item" style="width:150px">Tastenmodul</div>
    <div class="w3-bar-item" style="width:150px">Firmware</div>
    <div class="w3-bar-item" style="width:200px">MAC</div>
  </li>
<?php
  if (isset($_GET['edit'])) {
    $edit_nst = $_GET['edit'];
  }
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon");
  while ($row = mysqli_fetch_array($query)) {
    extract($row, EXTR_OVERWRITE);
    if($nst == @$edit_nst) {
      if(filter_var($registrar, FILTER_VALIDATE_IP)==TRUE) {
        $r_check_ip = 'selected';
      } else {
        $r_check_fqdn = 'selected';
      }
      if(filter_var($proxy, FILTER_VALIDATE_IP)==TRUE) {
        $p_check_ip = 'selected';
      } else {
        $p_check_fqdn = 'selected';
      }
      echo '<form method="post" action="adm_telefon.php">
            <li class="w3-bar">
              <div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="username" value="'.$username.'"></div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="authname" value="'.$authname.'"></div>
              <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="password" value="'.$password.'"></div>
              <div class="w3-bar-item" style="width:150px">
                <select class="w3-select w3-padding" name="proxy">
                  <option value="'.$cfg['fb']['ip'].'" '.@$p_check_ip.'>[IP] '.$cfg['fb']['ip'].'</option>
                  <option value="'.$cfg['fb']['host'].'" '.@$p_check_fqdn.'>[FQDN] '.$cfg['fb']['host'].'</option>
                </select>
              </div>
              <div class="w3-bar-item" style="width:150px">
                <select class="w3-select w3-padding" name="registrar">
                  <option value="'.$cfg['fb']['ip'].'" '.@$r_check_ip.'>[IP] '.$cfg['fb']['ip'].'</option>
                  <option value="'.$cfg['fb']['host'].'" '.@$r_check_fqdn.'>[FQDN] '.$cfg['fb']['host'].'</option>
                </select>
              </div>
              <div class="w3-bar-item" style="width:100px">
                <select class="w3-select w3-padding" name="model">';
                  $model_query = mysqli_query($db_conn,"SELECT * FROM model WHERE sip = 1");
                  while($model_row = mysqli_fetch_array($model_query)) {
                    if($model == $model_row['model']) {
                      echo '<option value="'.$model_row['model'].'" selected>'.$model_row['model'].'</option>';
                    } else {
                      echo '<option value="'.$model_row['model'].'">'.$model_row['model'].'</option>';
                    }
                  }
          echo '</select>
              </div>
              <div class="w3-bar-item" style="width:150px">
                <select class="w3-select w3-padding" name="exp">
                  <option value="0">keins</option>';
                  $exp_query = mysqli_query($db_conn,"SELECT * FROM model WHERE exp = 1");
                  while($exp_row = mysqli_fetch_array($exp_query)) {
                    if($exp == $exp_row['model']) {
                      echo '<option value="'.$exp_row['model'].'" selected>M'.$exp_row['model'].'/M'.$exp_row['model']+10 .'</option>';
                    } else {
                      echo '<option value="'.$exp_row['model'].'">M'.$exp_row['model'].'/M'.$exp_row['model']+10 .'</option>';
                    }
                  }
          echo '</select>
              </div>
              <div class="w3-bar-item" style="width:150px">'.$firmware.'</div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="mac" value="'.$mac.'"></div>
              <div class="w3-bar-item" style="width:50px">
                <input type="hidden" name="submitedit" value="'.$nst.'" />
                <button type="submit"><i class="fa-solid fa-check"></i></button>
              </div>
              <div class="w3-bar-item" style="width:50px">
                <a href="adm_telefon.php"><i class="fa-solid fa-xmark"></i></a>
              </div>
            </li>
            </form>';
    } else {
      echo '<li class="w3-bar">';
        if($model == 9999) {
          echo '<div class="w3-bar-item" style="width:150px">'.$nst.'</div><div class="w3-bar-item">DECT Mobilteil - bitte unter Mobilteile bearbeiten</div>';
        } else {
          if($exp == '685') {
            $expm = 'M685/M695';
          } elseif($exp == '680') {
            $expm = 'M680/M690';
          } else {
            $expm = 'keins';
          }
        echo '<div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:150px">'.$username.'</div>
              <div class="w3-bar-item" style="width:150px">'.$authname.'</div>
              <div class="w3-bar-item" style="width:150px">'.$password.'</div>
              <div class="w3-bar-item" style="width:150px">'.$proxy.'</div>
              <div class="w3-bar-item" style="width:150px">'.$registrar.'</div>
              <div class="w3-bar-item" style="width:100px">'.$model.'</div>
              <div class="w3-bar-item" style="width:150px">'.$expm.'</div>
              <div class="w3-bar-item" style="width:150px">'.$firmware.'</div>
              <div class="w3-bar-item" style="width:200px">'.$mac.'</div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_telefon.php?edit='.$nst.'"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_telefon.php?prov='.$nst.'"><i class="fa-solid fa-rotate" title="Provisionierung auslösen"></i></a></div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_telefon.php?reboot='.$nst.'"><i class="fa-solid fa-arrow-rotate-left" title="Neu starten"></i></a></div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_telefon.php?del='.$nst.'" onClick="return confirm(\'Möchtest du das Telefon '.$model.' mit der Nebenstelle '.$nst.' wirklich löschen?\');"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>';
        }
      echo '</li>';
    }
  }

?>
<form method="post" action="adm_telefon.php">
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
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="username"></div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="text" name="authname"></div>
    <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" type="password" name="password"></div>
    <div class="w3-bar-item" style="width:150px">
      <select class="w3-select w3-padding" name="proxy_ip">
        <option value="<?php echo $cfg['fb']['ip']; ?>">[IP] <?php echo $cfg['fb']['ip']; ?></option>
        <option value="<?php echo $cfg['fb']['host']; ?>">[FQDN] <?php echo $cfg['fb']['host']; ?></option>
      </select>
    </div>
    <div class="w3-bar-item" style="width:150px">
      <select class="w3-select w3-padding" name="registrar_ip">
        <option value="<?php echo $cfg['fb']['ip']; ?>">[IP] <?php echo $cfg['fb']['ip']; ?></option>
        <option value="<?php echo $cfg['fb']['host']; ?>">[FQDN] <?php echo $cfg['fb']['host']; ?></option>
      </select>
    </div>
    <div class="w3-bar-item" style="width:100px">
      <select class="w3-select w3-padding" name="model">
        <?php
          $model_query = mysqli_query($db_conn,"SELECT * FROM model WHERE sip = 1");
          while($model_row = mysqli_fetch_array($model_query)) {
            $model_row_model = $model_row['model'];
            echo '<option value="'.$model_row_model.'">'.$model_row_model.'</option>';
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item" style="width:150px">
      <select class="w3-select w3-padding" name="exp">
        <option value="0">keins</option>
        <?php
          $exp_query = mysqli_query($db_conn,"SELECT * FROM model WHERE exp = 1");
          while($exp_row = mysqli_fetch_array($exp_query)) {
            $exp_row_model = $exp_row['model'];
            echo '<option value="'.$exp_row_model.'">M'.$exp_row_model.'/M'.$exp_row_model+10 .'</option>';
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item" style="width:150px"> </div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="mac"></div>
    <div class="w3-bar-item" style="width:50px">
      <input type="hidden" name="submitphone" />
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
</div>
<?php
if (isset($feedback)) {
  echo '<div class="w3-center">'.$feedback.'</div>';
}
include('footer.php');
?>
