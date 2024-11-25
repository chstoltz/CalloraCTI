<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

if (isset($_POST['submituser'])) {
  extract($_POST, EXTR_OVERWRITE);
  $username = $_POST['username'];
  $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE username='$username'");
  if (mysqli_num_rows($query) == '0') {
    $salt = rand();
    $salt = hash('sha512', $salt);
    $password = $salt . $password;
    $password = hash('sha512', $password);
    mysqli_query($db_conn,"INSERT INTO adm_benutzer (id,username,password,salt,nst,email,regdate,level) VALUES (NULL,'$username','$password','$salt','$nst','$email',CURRENT_TIMESTAMP,'1')");
    $user_id = mysqli_insert_id($db_conn);
    if($fb_ports == '1') {
      $fb_ports_set = '["-1","20","21","22","23","24","25","26","27","28","29","10","11","12","13","14","15"]';
    } elseif($fb_ports == '2') {
      $own = $nst-600;
      $fb_ports_set = '["-1","'.$own.'"]';
    } else {
      $fb_ports_set = NULL;
    }
    mysqli_query($db_conn,"INSERT INTO usr_einstellungen (user_id,fb_ab,fb_book,fb_deflection,fb_ports) VALUES ('$user_id','$fb_ab','$fb_book','$fb_deflection','$fb_ports_set')");
  } else {
    $feedback = 'Benutzername existiert bereits oder nicht erlaubt!';
  }
  if(!isset($feedback)) { $feedback = 'Benutzer erfolgreich angelegt!'; }
}

if (isset($_GET['del'])) {
  $id = $_GET['del'];
  $query = mysqli_query($db_conn,"SELECT nst FROM adm_benutzer WHERE id = '$id'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];
  if($id != 1) {
    $del_query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
    if(mysqli_num_rows($del_query) == 1) {
      $del_array = mysqli_fetch_array($del_query);
      if ($del_array['model'] == 9999) {
        $mt_query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil WHERE nst = '$nst'");
        if(mysqli_num_rows($mt_query) == 1) {
          $mt_array = mysqli_fetch_array($mt_query);
          $mt_id = $mt_array['id'];
          $command = "<DeletePPUser uid=\"$mt_id\" />";
          axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
          mysqli_query($db_conn,"DELETE FROM usr_mobilteil WHERE nst = '$nst'");
        }
        mysqli_query($db_conn,"DELETE FROM usr_telefon WHERE nst = '$nst'");
        $feedback = 'und Mobilteil';
      } else {
        mysqli_query($db_conn,"DELETE FROM usr_telefon WHERE nst = '$nst'");
        $feedback = 'und Telefon';
      }
    }
    mysqli_query($db_conn,"DELETE FROM callstate WHERE nst = '$nst'");
    mysqli_query($db_conn,"DELETE FROM poll WHERE nst = '$nst'");
    mysqli_query($db_conn,"DELETE FROM regevent WHERE nst = '$nst'");
    mysqli_query($db_conn,"DELETE FROM tasten WHERE nst = '$nst'");
    mysqli_query($db_conn,"DELETE FROM adm_benutzer WHERE id = '$id'");
    mysqli_Query($db_conn,"DELETE FROM usr_einstellungen WHERE user_id = '$id'");
    $feedback = 'Benutzer '.@$feedback.' gelöscht.';
  }
}
if (isset($_POST['submitedit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $id = $_POST['submitedit'];
  if ($password != '') {
    $salt = rand();
    $salt = hash('sha512', $salt);
    $password = $salt . $password;
    $password = hash('sha512', $password);
    mysqli_query($db_conn,"UPDATE adm_benutzer SET password='$password',salt='$salt' WHERE id = '$id'");
    $feedback = 'Kennwort erfolgreich geändert!<br />';
  }
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $feedback = 'E-Mail Adresse ist ungültig!';
  } else {
    mysqli_query($db_conn,"UPDATE adm_benutzer SET email = '$email' WHERE id = '$id'");
      $feedback .= 'E-Mail erfolgreich geändert!<br />';
  }
  $query = mysqli_query($db_conn,"SELECT nst FROM adm_benutzer WHERE id = '$id'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];

  if($fb_ports == '1') {
    $fb_ports_set = '["-1","20","21","22","23","24","25","26","27","28","29","10","11","12","13","14","15"]';
  } elseif($fb_ports == '2') {
    $own = $nst-600;
    $fb_ports_set = '["-1","'.$own.'"]';
  } else {
    $fb_ports_set = NULL;
  }
  mysqli_query($db_conn,"UPDATE usr_einstellungen SET fb_ports='$fb_ports_set',fb_ab='$fb_ab',fb_book='$fb_book',fb_deflection='$fb_deflection' WHERE user_id = '$id'");
  $feedback .= 'Einstellungen gespeichert!';
  unset($id);
}
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Benutzer</div>
</div>
<div class="w3-container">
<ul class="w3-ul">
  <li class="w3-bar">
    <div class="w3-bar-item" style="width:150px">Nebenstelle</div>
    <div class="w3-bar-item" style="width:200px">Benutzername</div>
    <div class="w3-bar-item" style="width:200px">Passwort</div>
    <div class="w3-bar-item" style="width:200px">E-Mail</div>
    <div class="w3-bar-item" style="width:200px">Anrufbeantworter</div>
    <div class="w3-bar-item" style="width:200px">Telefonbuch</div>
    <div class="w3-bar-item" style="width:200px">Anruflisten</div>
    <div class="w3-bar-item" style="width:250px">Rufumleitung</div>
  </li>
<?php
  if (isset($_GET['useredit'])) {
    $edit_id = $_GET['useredit'];
  }
  $ab_list = $x_tam->GetList();
  $xml_tam = simplexml_load_string($ab_list);
  $book_list = $x_contact->GetPhoneBookList();
  $array_book = explode(",",$book_list);
  $def_list = $x_contact->GetDeflections();
  $xml_def = simplexml_load_string($def_list);
  
  $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer JOIN usr_einstellungen WHERE adm_benutzer.id = usr_einstellungen.user_id ORDER BY adm_benutzer.nst ASC");
  while ($row = mysqli_fetch_array($query)) {
    extract($row, EXTR_OVERWRITE);
    if($user_id == @$edit_id) {
      
      //$fb_deflection = json_decode(us('fb_deflection'), true);
      //$fb_ports = json_decode(us('fb_ports'), true);
      echo '<form method="post" action="adm_benutzer.php">
            <li class="w3-bar">
              <div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:200px">'.$username.'</div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="password" name="password"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="email" value="'.$email.'"></div>
              <div class="w3-bar-item" style="width:200px">
                <select class="w3-select w3-padding" name="fb_ab">
                  <option value="99">keiner</option>';
                  foreach($xml_tam->Item as $item) {
	                  if($item->Display == 1) {
	                    if($item->Index == @$fb_ab) { $selected = 'selected'; } else { $selected = ''; }
  	                  echo '<option value="'.$item->Index.'" '.$selected.'>'.$item->Name.'</option>';
	                  }
                  }
        echo '</select>
              </div>
              <div class="w3-bar-item" style="width:200px">
                <select class="w3-select w3-padding" name="fb_book">
                  <option value="99">keins</option>';
                    foreach($array_book as $book) {
                      if($fb_book == $book) { $fb_book_select = 'selected'; }
                      $bookname = $x_contact->GetPhonebook(new SoapParam($book, 'NewPhonebookID'));
                      $bookname = $bookname['NewPhonebookName'];
	                    echo '<option value="'.$book.'" '.@$fb_book_select.'>'.$bookname.'</option>';
                    }
        echo '</select>
              </div>
              <div class="w3-bar-item" style="width:200px">
                <select class="w3-select w3-padding" name="fb_deflection">';
                if ($fb_ports == '["-1","20","21","22","23","24","25","26","27","28","29","10","11","12","13","14","15"]' ) {
                  $alle = 'selected';
                } else {
                  $eigene = 'selected';
                }
            echo '<option value="1" '.@$alle.'>alle & verpasst</option>
                  <option value="2" '.@$eigene.'>eigene & verpasst</option>
                </select>
              </div>
              <div class="w3-bar-item" style="width:250px">
                <select class="w3-select w3-padding" name="fb_deflection">
                  <option value="99">keine</option>';
                    foreach($xml_def->Item as $item) {
                      if($fb_deflection == $item->DeflectionId) { $fb_deflection_selected = 'selected'; }
                      if($item->Type == 'toVoIP') {
                        switch($item->Mode) {
                          case 'eParallelCall':
                            $type = '[PR]';
                            break;
                          case 'eShortDelayed':
                            $type = '[20]';
                            break;
                          case 'eDelayedOrBusy':
                            $type = '[Busy]';
                            break;
                          case 'eImmediately':
                            $type = '[RUL]';
                            break;
                          case 'eLongDelayed':
                            $type = '[40]';
                            break;
                        }
                    echo '<option value="'.$item->DeflectionId.'" '.@$fb_deflection_selected.'>'.$item->Number.' => '.$item->DeflectionToNumber.' '.$type.'</option>';
                      }
                      unset($fb_deflection_selected);
                    }
 
          echo '</select>
              </div>
              <div class="w3-bar-item" style="width:50px">
                <input type="hidden" name="submitedit" value="'.$user_id.'" />
                
                <button type="submit"><i class="fa-solid fa-user-check"></i></button>
              </div>
              <div class="w3-bar-item" style="width:50px">
                <a href="adm_benutzer.php"><i class="fa-solid fa-xmark"></i></a>
              </div>
            </li>
            </form>';
    } else {
      if($user_id != '1') {
        if($fb_ab != '99') {
          $ab = $x_tam->GetInfo(new SoapParam($fb_ab, 'NewIndex'));
          $fb_ab_name = $ab['NewName'];
        } else {
          $fb_ab_name = 'keiner';
        }
        if($fb_book != '99') {
          $book = $x_contact->GetPhonebook(new SoapParam($fb_book, 'NewPhonebookID'));
          $fb_book_name = $book['NewPhonebookName'];
        } else {
          $fb_book_name = 'keins';
        }
        if ($fb_ports == '["-1","20","21","22","23","24","25","26","27","28","29","10","11","12","13","14","15"]' ) {
          $fb_ports_name = 'alle & verpasst';
        } else {
          $fb_ports_name = 'eigene & verpasst';
        }
        if($fb_deflection != '99') {
          $def = $x_contact->GetDeflection(new SoapParam($fb_deflection, 'NewDeflectionId'));
          $fb_def_name = $def['NewNumber'].' <i class="fa-solid fa-arrow-right"></i> '.$def['NewDeflectionToNumber'];
        } else {
          $fb_def_name = 'keine';
        }
      }
      echo '<li class="w3-bar">
              <div class="w3-bar-item" style="width:150px">'.$nst.'</div>
              <div class="w3-bar-item" style="width:200px">'.$username.'</div>
              <div class="w3-bar-item" style="width:200px">********</div>
              <div class="w3-bar-item" style="width:200px">'.$email.'</div>
              <div class="w3-bar-item" style="width:200px">'.@$fb_ab_name.'</div>
              <div class="w3-bar-item" style="width:200px">'.@$fb_book_name.'</div>
              <div class="w3-bar-item" style="width:200px">'.@$fb_ports_name.'</div>
              <div class="w3-bar-item" style="width:250px">'.@$fb_def_name.'</div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_benutzer.php?useredit='.$user_id.'"><i class="fa-solid fa-user-pen" title="Editieren"></i></a></div>';
              if($username == 'admin') {
                echo '';
              } else {
                echo '<div class="w3-bar-item" style="width:50px"><a href="adm_benutzer.php?del='.$user_id.'" onClick="return confirm(\'Möchtest du den Benutzer '.$username.' mit der Nebenstelle '.$nst.' wirklich löschen? Ein dem Benutzer zugeordnetes Endgerät wird auch gelöscht.\');"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>';
              }
      echo '</li>';
    }
  }
?>
<form method="post" action="adm_benutzer.php">
  <li class="w3-bar">
  <div class="w3-bar-item w3-padding" style="width:150px">
      <select class="w3-select w3-padding" name="nst">
        <?php
          $result = $x_voip->{'X_AVM-DE_GetClients'}();
          $sum_nst = 0;
          $xml = simplexml_load_string($result);
          foreach($xml->Item as $item) {
            $user_nst = $item->{'X_AVM-DE_InternalNumber'};
            $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE nst='$user_nst'");
            if(mysqli_num_rows($query)==0) {
                echo '<option value="'.$user_nst.'">'.$user_nst.'</option>';
                $sum_nst++;
            }
          }
        ?>
      </select>
    </div>
    <?php if($sum_nst == 0) {
       echo '<div class="w3-bar-item">Keine freien Nebenstellen. Bitte erst ein <a href="adm_fritzbox.php">IP Telefon anlegen</a>.</div>';
     } else {
     ?>
    <div class="w3-bar-item w3-padding" style="width:200px"><input class="w3-input w3-border" type="text" name="username"></div>
    <div class="w3-bar-item w3-padding" style="width:200px"><input class="w3-input w3-border" type="password" name="password"></div>
    <div class="w3-bar-item w3-padding" style="width:200px"><input class="w3-input w3-border" type="text" name="email"></div>
    <div class="w3-bar-item w3-padding" style="width:200px">
      <select class="w3-select w3-padding" name="fb_ab">
        <option value="99">keiner</option>
        <?php
          foreach($xml_tam->Item as $item) {
	          if($item->Display == 1) {
	            echo '<option value="'.$item->Index.'">'.$item->Name.'</option>';
	          }
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item w3-padding" style="width:200px">
      <select class="w3-select w3-padding" name="fb_book">
        <option value="99">keins</option>
        <?php
          foreach($array_book as $book) {
            $bookname = $x_contact->GetPhonebook(new SoapParam($book, 'NewPhonebookID'));
            $bookname = $bookname['NewPhonebookName'];
	          echo '<option value="'.$book.'">'.$bookname.'</option>';
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item w3-padding" style="width:200px">
      <select class="w3-select w3-padding" name="fb_ports">
        <option value="1">alle & verpasst</option>
        <option value="2">eigene & verpasst</option>
      </select>
    </div>
    <div class="w3-bar-item w3-padding" style="width:250px">
      <select class="w3-select w3-padding" name="fb_deflection">
        <option value="99">keine</option>
        <?php
          foreach($xml_def->Item as $item) {
            if($item->Type == 'toVoIP') {
              switch($item->Mode) {
                case 'eParallelCall':
                  $type = '[PR]';
                  break;
                case 'eShortDelayed':
                  $type = '[20]';
                  break;
                case 'eDelayedOrBusy':
                  $type = '[Busy]';
                  break;
                case 'eImmediately':
                  $type = '[RUL]';
                  break;
                case 'eLongDelayed':
                  $type = '[40]';
                  break;
              }
            
            echo '<option value="'.$item->DeflectionId.'">'.$item->Number.' => '.$item->DeflectionToNumber.' '.$type.'</option>';
            }
          }
        ?>
      </select>
    </div>
    <div class="w3-bar-item" style="width:50px">
      <input type="hidden" name="submituser" />
      <button type="submit"><i class="fa-solid fa-user-plus" title="Benutzer anlegen"></i></button>
    </div>
    <?php } ?>
  </li>
  </form>
</ul>
</div>
<?php
include('footer.php');
?>
