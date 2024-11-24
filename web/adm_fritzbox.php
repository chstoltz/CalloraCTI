<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

// Post Fritz!Box Einstellungen

if(isset($_POST['settings'])) {
  extract($_POST, EXTR_OVERWRITE);
  $query = mysqli_query($db_conn,"SELECT * FROM adm_fritzbox");
  if(mysqli_num_rows($query) == 0) {
    mysqli_query($db_conn,"INSERT INTO adm_fritzbox (fb_url,fb_ip,fb_user,fb_pass) VALUES ('$fb_url','$fb_ip','$fb_user','$fb_pass')");
  } else {
    mysqli_query($db_conn,"UPDATE adm_fritzbox SET fb_url='$fb_url',fb_ip='$fb_ip',fb_user='$fb_user',fb_pass='$fb_pass'");
  }
}

// Post Fritz!Box Neustart

if(isset($_POST['reboot'])) {
  $deviceconfig->Reboot();
  $feedback = 'Fritz!Box erfolgreich neu gestartet!';
}

// Get IP Telefon löschen

if(isset($_GET['del'])) {
  $id = $_GET['del'];
  
  $intern = $x_voip->{'X_AVM-DE_GetClient2'}(new SoapParam($id, 'NewX_AVM-DE_ClientIndex'));
  $nst = $intern['NewX_AVM-DE_InternalNumber'];

  $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE nst = '$nst'");
  $array = mysqli_fetch_array($query);
  $usr_id = $array['id'];
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
    mysqli_query($db_conn,"DELETE FROM adm_benutzer WHERE id = '$usr_id'");
    $feedback = 'Benutzer '.@$feedback.' gelöscht.';
  }

  $delete = $x_voip->{'X_AVM-DE_DeleteClient'}(new SoapParam($id, 'NewX_AVM-DE_ClientIndex'));
  $feedback = 'IP Telefon gelöscht. '.$feedback;
}

// Post Edit

if(isset($_POST['edit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $out = explode(',',$OutGoingNumber);
  $outgoing = $out[1];
  $list = new SimpleXMLElement("<List></List>");
  foreach($InComingNumbers as $n_in) {
    $array = explode(',',$n_in);
    $item = $list->addChild('Item');
    $item->addChild('Number',$array[1]);
    $item->addChild('Type','eVoIP');
    $item->addChild('Index',$array[0]);
    $item->addChild('Name');
    $num_array[] = $array[1];
  }
  $xml_e = urlencode($list->asXML());
  try {
    $x_voip->{'X_AVM-DE_SetClient4'}(
      new SoapParam((int)$edit, 'NewX_AVM-DE_ClientIndex'),
      new SoapParam($ClientPassword, 'NewX_AVM-DE_ClientPassword'),
      new SoapParam($ClientUsername, 'NewX_AVM-DE_ClientUsername'),
      new SoapParam($PhoneName, 'NewX_AVM-DE_PhoneName'),
      new SoapParam('', 'NewX_AVM-DE_ClientId'),
      new SoapParam($outgoing, 'NewX_AVM-DE_OutGoingNumber'),
      new SoapParam($list->asXML(), 'NewX_AVM-DE_InComingNumbers'));
  } catch (Exception $e) {
    switch($e->detail->UPnPError->errorCode) {
      case '866':
        $zwofa = $x_auth->SetConfig(new SoapParam('start', 'NewAction'));
        print_r($zwofa);
        $token = $zwofa['NewToken'];
        $dtmf = explode(';',$zwofa['NewMethods']);
        $dtmf = $dtmf[1];
        $active2fa = 1;
        break;
      case '867':
        $feedback = '2FA gesperrt, bitte 60 Minuten warten.';
        break;
      case '868':
        $feedback = '2FA Authentisierung bereits gestartet, es kann kein weiterer Vorgang gestartet werden.';
        break;
    }
    //echo($x_voip->__getLastResponse());
    //echo PHP_EOL;
    //echo($x_voip->__getLastRequest());
  }
  if(!isset($active2fa)) {
    $feedback = 'Änderungen gespeichert.';
  } 
}

// Post Edit Bestätigen

if(isset($_POST['confirmedit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $headerXML = <<<XML
<avm:token xmlns:avm="avm.de" mustUnderstand="1">$token</avm:token>
XML;
$header = new SoapHeader('avm.de', 'token', new SoapVar($headerXML, XSD_ANYXML), false);

  $x_voip->__setSoapHeaders($header);
  try {
    $x_voip->{'X_AVM-DE_SetClient4'}(
      new SoapParam((int)$confirmedit, 'NewX_AVM-DE_ClientIndex'),
      new SoapParam($ClientPassword, 'NewX_AVM-DE_ClientPassword'),
      new SoapParam($ClientUsername, 'NewX_AVM-DE_ClientUsername'),
      new SoapParam($PhoneName, 'NewX_AVM-DE_PhoneName'),
      new SoapParam('', 'NewX_AVM-DE_ClientId'),
      new SoapParam($outgoing, 'NewX_AVM-DE_OutGoingNumber'),
      new SoapParam(urldecode($incoming), 'NewX_AVM-DE_InComingNumbers'));
    } catch (Exception $e) {
      echo($x_voip->__getLastResponse());
      echo PHP_EOL;
      echo($x_voip->__getLastRequest());
    }  
  $feedback = 'Änderungen gespeichert.';
}

// Post IP Telefon anlegen

if(isset($_POST['add'])) {
  extract($_POST, EXTR_OVERWRITE);
  $out = explode(',',$OutGoingNumber);
  $outgoing = $out[1];
  $list = new SimpleXMLElement("<List></List>");
  foreach($InComingNumbers as $n_in) {
    $array = explode(',',$n_in);
    $item = $list->addChild('Item');
    $item->addChild('Number',$array[1]);
    $item->addChild('Type','eVoIP');
    $item->addChild('Index',$array[0]);
    $item->addChild('Name');
    $num_array[] = $array[1];
  }
  $xml_e = urlencode($list->asXML());
  try {
    $x_voip->{'X_AVM-DE_SetClient4'}(
      new SoapParam((int)$add, 'NewX_AVM-DE_ClientIndex'),
      new SoapParam($ClientPassword, 'NewX_AVM-DE_ClientPassword'),
      new SoapParam($ClientUsername, 'NewX_AVM-DE_ClientUsername'),
      new SoapParam($PhoneName, 'NewX_AVM-DE_PhoneName'),
      new SoapParam('', 'NewX_AVM-DE_ClientId'),
      new SoapParam($outgoing, 'NewX_AVM-DE_OutGoingNumber'),
      new SoapParam($list->asXML(), 'NewX_AVM-DE_InComingNumbers'));
  } catch (Exception $e) {
    switch($e->detail->UPnPError->errorCode) {
      case '866':
        $zwofa = $x_auth->SetConfig(new SoapParam('start', 'NewAction'));
        $token = $zwofa['NewToken'];
        $dtmf = explode(';',$zwofa['NewMethods']);
        $dtmf = $dtmf[1];
        $active2fa = 1;
        break;
      case '867':
        $feedback = '2FA gesperrt, bitte 60 Minuten warten.';
        break;
      case '868':
        $feedback = '2FA Authentisierung bereits gestartet, es kann kein weiterer Vorgang gestartet werden.';
        break;
    } 
  }
  if(!isset($active2fa)) {
    $feedback = 'IP-Telefon angelegt.';
  }
}

// Post anlegen bestätigen

if(isset($_POST['confirm'])) {
  extract($_POST, EXTR_OVERWRITE);
  $headerXML = <<<XML
<avm:token xmlns:avm="avm.de" mustUnderstand="1">$token</avm:token>
XML;
$header = new SoapHeader('avm.de', 'token', new SoapVar($headerXML, XSD_ANYXML), false);

  $x_voip->__setSoapHeaders($header);
  try {
    $x_voip->{'X_AVM-DE_SetClient4'}(
      new SoapParam((int)$confirm, 'NewX_AVM-DE_ClientIndex'),
      new SoapParam($ClientPassword, 'NewX_AVM-DE_ClientPassword'),
      new SoapParam($ClientUsername, 'NewX_AVM-DE_ClientUsername'),
      new SoapParam($PhoneName, 'NewX_AVM-DE_PhoneName'),
      new SoapParam('', 'NewX_AVM-DE_ClientId'),
      new SoapParam($outgoing, 'NewX_AVM-DE_OutGoingNumber'),
      new SoapParam(urldecode($incoming), 'NewX_AVM-DE_InComingNumbers'));
    } catch (Exception $e) {
      //echo($x_voip->__getLastResponse());
      //echo PHP_EOL;
      //echo($x_voip->__getLastRequest());
    }
  $feedback = 'IP-Telefon angelegt.';
}
$query = mysqli_query($db_conn,"SELECT * FROM adm_fritzbox");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
}
?>

<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Fritz!Box</div>
</div>
<div class="w3-container">
<div class="w3-row w3-quarter w3-padding-large">
<h5>Fritz!Box Einstellungen:</h5>
<form method="post" action="adm_fritzbox.php">
  <p><label>Fritz!Box FQDN (default: fritz.box):</label>
  <input class="w3-input w3-border" type="text" name="fb_url" value="<?php echo @$fb_url; ?>" style="width:300px"></p>
  <p><label>Fritz!Box IP (default: 192.168.178.1):</label>
  <input class="w3-input w3-border" type="text" name="fb_ip" value="<?php echo @$fb_ip; ?>" style="width:300px"></p>
  <p><label>Fritz!Box Benutzer (default: fritzXXXX):</label>
  <input class="w3-input w3-border" type="text" name="fb_user" value="<?php echo @$fb_user; ?>" style="width:300px"></p>
  <p><label>Fritz!Box Passwort:</label>
  <input class="w3-input w3-border" type="text" name="fb_pass" value="<?php echo @$fb_pass; ?>" style="width:300px"></p>
  <p><input type="hidden" name="settings" />
  <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
</form>
<form action="adm_fritzbox.php" method="post" name="reboot">
<p><input type="hidden" name="reboot"><button class="w3-btn w3-blue" type="submit">Fritz!Box neu starten</button></p>
</form>
</div>
<div class="w3-row w3-half w3-padding-large">
  <h5>Angelegte IP-Telefone:</h5>
  <ul class="w3-ul">  
    <li class="w3-bar">
      <div class="w3-bar-item" style="width:50px">&nbsp;</div>
      <div class="w3-bar-item" style="width:50px">Nst.</div>
      <div class="w3-bar-item" style="width:200px">Name</div>
      <div class="w3-bar-item" style="width:100px">Benutzer</div>
      <div class="w3-bar-item" style="width:100px"><i class="fa-solid fa-phone" title="Rufnummer ausgehend"></i><i class="fa-solid fa-arrow-right" title="Rufnummer ausgehend"></i></div>
      <div class="w3-bar-item" style="width:100px"><i class="fa-solid fa-phone" title="Rufnummer(n) eingehend"></i><i class="fa-solid fa-arrow-left" title="Rufnummer(n) eingehend"></i></div>      
  </li>
  <?php
    $result = $x_voip->{'X_AVM-DE_GetNumberOfClients'}();
    $result = $result-1;
    for($i=0;$i<=$result;$i++) {
      $client = $x_voip->{'X_AVM-DE_GetClient3'}(new SoapParam($i, 'NewX_AVM-DE_ClientIndex'));
      $xml = simplexml_load_string($client['NewX_AVM-DE_InComingNumbers']);
      ?>
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:50px"><?php echo $i; ?></div>
        <div class="w3-bar-item" style="width:50px"><?php echo $client['NewX_AVM-DE_InternalNumber']; ?></div>
        <div class="w3-bar-item" style="width:200px"><?php echo $client['NewX_AVM-DE_PhoneName']; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $client['NewX_AVM-DE_ClientUsername']; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $client['NewX_AVM-DE_OutGoingNumber']; ?></div>
        <div class="w3-bar-item" style="width:100px">
      <?php $in = ''; foreach ($xml->Item as $item) { ?>
        <?php echo $item->Number; $in .= $item->Number.','; ?><br />
      <?php } $in = rtrim($in, ','); ?>
        </div>
        <div class="w3-bar-item"><a href="adm_fritzbox.php?edit=<?php echo $i.'&out='.$client['NewX_AVM-DE_OutGoingNumber'].'&in='.$in ?>"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
        <div class="w3-bar-item"><a href="adm_fritzbox.php?del=<?php echo $i;?>" onClick="return confirm('Möchtest du das IP-Telefon <?php echo $client['NewX_AVM-DE_PhoneName']; ?> mit der Nebenstelle <?php echo $client['NewX_AVM-DE_InternalNumber']; ?> wirklich löschen? Ein dem IP-Telefon zugeordneter Benutzer samt Endgerät werden dabei auch gelöscht.');"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>
      </li>
      <?php
    }
?>
<li class="w3-bar">
  <div class="w3-bar-item" style="width:50px"> </div>
  <div class="w3-bar-item" style="width:50px"> </div>
  <div class="w3-bar-item" style="width:200px"> </div>
  <div class="w3-bar-item" style="width:100px"> </div>
  <div class="w3-bar-item" style="width:100px"> </div>
  <div class="w3-bar-item" style="width:100px"> </div>
  <?php 
    if($result <= 8) {
  ?>
  <div class="w3-bar-item"><a href="adm_fritzbox.php?add=1"><i class="fa-solid fa-plus" title="IP-Telefon anlegen"></i></a></div>
  <?php } else { echo '<div class="w3-bar-item">Maximale Anzahl an Nebenstellen erreicht.</div>'; } ?>
  </li>
</ul>
</div>
<div class="w3-row w3-quarter w3-padding-large">
<?php
if(isset($_GET['edit'])) {
  $edit_id = $_GET['edit'];
  $edit = $x_voip->{'X_AVM-DE_GetClient3'}(new SoapParam($edit_id, 'NewX_AVM-DE_ClientIndex'));
  $pw_query = mysqli_query($db_conn,"SELECT password FROM usr_telefon WHERE username ='{$edit['NewX_AVM-DE_ClientUsername']}'");
  $pw_array = mysqli_fetch_array($pw_query);
  $pw = @$pw_array['password'];
?>
<h5>Nebenstelle <?php echo $edit['NewX_AVM-DE_InternalNumber']; ?> bearbeiten:</h5>
<form method="post" action="adm_fritzbox.php">
  <p><label>Name IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="PhoneName" value="<?php echo $edit['NewX_AVM-DE_PhoneName']; ?>" style="width:300px"></p>
  <p><label>Benutzername IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientUsername" value="<?php echo $edit['NewX_AVM-DE_ClientUsername']; ?>" style="width:300px"></p>
  <p><label>Passwort IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientPassword" style="width:300px" value="<?php echo $pw; ?>" placeholder="Bitte Passwort eingeben"></p>
  <p><label>ausgehende Rufnummer:</label><br />
  <select class="w3-select w3-padding w3-border" name="OutGoingNumber" style="width:300px">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if($number->Number == $_GET['out']) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>
  </select></p>
  <?php $sum_in = $x_voip->{'X_AVM-DE_GetNumberOfNumbers'}(); ?>
  <p><label>eingehende Rufnummer(n):</label><br />
  <select class="w3-select w3-padding w3-border" name="InComingNumbers[]" style="width:300px" multiple size="<?php echo $sum_in-1; ?>">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $in = $_GET['in'];
    if(str_contains($in, ',')) {
    $n_in = explode(',',$in);
    } else {
      $n_in[] = $in;
    }
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if(in_array($number->Number, $n_in)) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>   
  </select></p>
  <p><input type="hidden" name="edit" value="<?php echo $edit_id; ?>"/>
  <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
</form>
<?php
}
if((isset($_POST['edit'])) AND (isset($active2fa))) {
  $edit_post = $x_voip->{'X_AVM-DE_GetClient3'}(new SoapParam($edit, 'NewX_AVM-DE_ClientIndex'));
?>
<h5>Nebenstelle <?php echo $edit_post['NewX_AVM-DE_InternalNumber']; ?> bearbeiten:</h5>
<form method="post" action="adm_fritzbox.php">
  <p><label>Name IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="PhoneName" style="width:300px" value="<?php echo $PhoneName; ?>" readonly="readonly"></p>
  <p><label>Benutzername IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientUsername" style="width:300px" value="<?php echo $ClientUsername; ?>" readonly="readonly"></p>
  <p><label>Passwort IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientPassword" style="width:300px" value="<?php echo $ClientPassword; ?>" readonly="readonly"></p>
  <p><label>ausgehende Rufnummer:</label><br />
  <select class="w3-select w3-padding w3-border" name="OutGoingNumber" style="width:300px" readonly="readonly">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if($number->Number == $outgoing) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>
  </select></p>
  <?php $sum_in = $x_voip->{'X_AVM-DE_GetNumberOfNumbers'}(); ?>
  <p><label>eingehende Rufnummer(n):</label><br />
  <select class="w3-select w3-padding w3-border" name="InComingNumbers[]" style="width:300px" multiple size="<?php echo $sum_in-1; ?>" readonly="readonly">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if(in_array($number->Number, $num_array)) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>   
  </select></p>
  <p><input type="hidden" name="confirmedit" value="<?php echo $edit; ?>"/>
  <input type="hidden" name="token" value="<?php echo $token; ?>"/>
  <input type="hidden" name="outgoing" value="<?php echo $outgoing; ?>"/>
  <input type="hidden" name="incoming" value="<?php echo $xml_e; ?>"/>
  <button class="w3-btn w3-blue" type="submit">Bestätigen</button></p>
</form>
<p>2FA Authentisierung ist aktiv. Zur Bestätigung bitte eine Taste an der Fritz!Box drücken, oder über ein Telefon die Nummer <span class="w3-tag w3-blue"><?php echo $dtmf; ?></span> wählen. Danach auf <span class="w3-tag w3-blue">Bestätigen</span> klicken.</p>
<?php
}

if(isset($_GET['add'])) {
  $add_id = $x_voip->{'X_AVM-DE_GetNumberOfClients'}();
?>
<h5>Neue Nebenstelle erstellen:</h5>
<form method="post" action="adm_fritzbox.php">
  <p><label>Name IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="PhoneName" style="width:300px"></p>
  <p><label>Benutzername IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientUsername" style="width:300px"></p>
  <p><label>Passwort IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientPassword" style="width:300px"></p>
  <p><label>ausgehende Rufnummer:</label><br />
  <select class="w3-select w3-padding w3-border" name="OutGoingNumber" style="width:300px">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
      }
    }
  ?>
  </select></p>
  <?php $sum_in = $x_voip->{'X_AVM-DE_GetNumberOfNumbers'}(); ?>
  <p><label>eingehende Rufnummer(n):</label><br />
  <select class="w3-select w3-padding w3-border" name="InComingNumbers[]" style="width:300px" multiple size="<?php echo $sum_in-1; ?>">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
      }
    }
  ?>   
  </select></p>
  <p><input type="hidden" name="add" value="<?php echo $add_id; ?>"/>
  <button class="w3-btn w3-blue" type="submit">Erstellen</button></p>
</form>
<?php
}

if((isset($_POST['add'])) AND (isset($active2fa))) {
  $add_id = $x_voip->{'X_AVM-DE_GetNumberOfClients'}();
?>
<h5>Neue Nebenstelle erstellen:</h5>
<form method="post" action="adm_fritzbox.php?">
  <p><label>Name IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="PhoneName" style="width:300px" value="<?php echo $PhoneName; ?>" readonly="readonly"></p>
  <p><label>Benutzername IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientUsername" style="width:300px" value="<?php echo $ClientUsername; ?>" readonly="readonly"></p>
  <p><label>Passwort IP-Telefon:</label>
  <input class="w3-input w3-border" type="text" name="ClientPassword" style="width:300px" value="<?php echo $ClientPassword; ?>" readonly="readonly"></p>
  <p><label>ausgehende Rufnummer:</label><br />
  <select class="w3-select w3-padding w3-border" name="OutGoingNumber" style="width:300px" readonly="readonly">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if($number->Number == $outgoing) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>
  </select></p>
  <?php $sum_in = $x_voip->{'X_AVM-DE_GetNumberOfNumbers'}(); ?>
  <p><label>eingehende Rufnummer(n):</label><br />
  <select class="w3-select w3-padding w3-border" name="InComingNumbers[]" style="width:300px" multiple size="<?php echo $sum_in-1; ?>" readonly="readonly">
  <?php
    $numbers = $x_voip->{'X_AVM-DE_GetNumbers'}();
    $n_xml = simplexml_load_string($numbers);
    foreach($n_xml->Item as $number) {
      if($number->Type == 'eVoIP') {
        if(in_array($number->Number, $num_array)) {
          echo '<option value="'.$number->Index.','.$number->Number.'" selected>'.$number->Number.'</option>';
        } else {
          echo '<option value="'.$number->Index.','.$number->Number.'">'.$number->Number.'</option>';
        }
      }
    }
  ?>   
  </select></p>
  <p><input type="hidden" name="confirm" value="<?php echo $add_id; ?>"/>
  <input type="hidden" name="token" value="<?php echo $token; ?>"/>
  <input type="hidden" name="outgoing" value="<?php echo $outgoing; ?>"/>
  <input type="hidden" name="incoming" value="<?php echo $xml_e; ?>"/>
  <button class="w3-btn w3-blue" type="submit">Bestätigen</button></p>
</form>
<p>2FA Authentisierung ist aktiv. Zur Bestätigung bitte eine Taste an der Fritz!Box drücken, oder über ein Telefon die Nummer <span class="w3-tag w3-blue"><?php echo $dtmf; ?></span> wählen. Danach auf <span class="w3-tag w3-blue">Bestätigen</span> klicken.</p>
<?php
}
?>
</div>
</div>
<?php
include('footer.php');
?>
