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
  $query = mysqli_query($db_conn,"SELECT * FROM adm_sipdect");
  if(mysqli_num_rows($query) == 0) {
    mysqli_query($db_conn,"INSERT INTO adm_sipdect (omm_ip,registrar_ip,proxy_ip,omm_password,root_password,system_name) VALUES ('$omm_ip','$registrar_ip','$proxy_ip','$omm_password','$root_password','$system_name')");
  } else {
    mysqli_query($db_conn,"UPDATE adm_sipdect SET omm_ip='$omm_ip',registrar_ip='$registrar_ip',proxy_ip='$proxy_ip',omm_password='$omm_password',root_password='$root_password,system_name='$system_name'");
  }
  exec("php {$cfg['cnf']['path']}.'/web/prov_gen_sipdect.php & > /dev/null 2>&1");
  exec("php {$cfg['cnf']['path']}.'/web/omm_prov_sync.php & > /dev/null 2>&1");
}
$query = mysqli_query($db_conn,"SELECT * FROM adm_sipdect");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  extract($array, EXTR_OVERWRITE);
}
if(isset($_POST['reboot'])) {
  $command = "<SystemRestart resetDB=\"false\" resetToFactoryDefaults=\"false\" />";
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $feedback = 'OMM erfolgreich neu gestartet!';
}
if(isset($_POST['check'])) {
  exec("php {$cfg['cnf']['path']}.'/web/omm_check_sync.php & > /dev/null 2>&1");
  $feedback = 'Benutzerdaten werden neu provisioniert!';
}
if(isset($_POST['prov'])) {
  exec("php {$cfg['cnf']['path']}.'/web/omm_prov_sync.php' & > /dev/null 2>&1");
  $feedback = 'OMM wird neu provisioniert!';
}
if(filter_var($registrar_ip, FILTER_VALIDATE_IP)==TRUE) {
    $r_check_ip = 'selected';
} else {
    $r_check_fqdn = 'selected';
}
if(filter_var($proxy_ip, FILTER_VALIDATE_IP)==TRUE) {
    $p_check_ip = 'selected';
} else {
    $p_check_fqdn = 'selected';
}
if(isset($_GET['park'])) {
  $command = '<PARKFromServer />';
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
}
if(isset($_POST['subs'])) {
  $command = '<SetRFPCapture enable="1" />';
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $command = '<GetRFPCaptureList />';
  $capturelist = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $capture = 1;
}
if(isset($_POST['stop'])) {
  $command = '<SetRFPCapture enable="0" />';
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $command = '<DeleteRFPCaptureList />';
}
if(isset($_POST['reload'])) {
  $command = '<GetRFPCaptureList />';
  $capturelist = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $capture = 1;
}
if(isset($_GET['del'])) {
  $rfpid = $_GET['del'];
  $command = "<DeleteRFP id=\"$rfpid\" />";
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
}
if(isset($_GET['addrfp'])) {
  extract($_GET,EXTR_OVERWRITE);
  $command = "<CreateRFP><rfp id=\"$addrfp\" ethAddr=\"$ethAddr\" dectOn=\"1\" hwType=\"$hwType\" pagingArea=\"0\" cluster=\"1\" site=\"1\" /></CreateRFP>";
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $command = '<GetRFPCaptureList />';
  $capturelist = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
  $capture = 1;
}
if(isset($_POST['submitedit'])) {
  $rfpid = $_POST['submitedit'];
  $name = $_POST['name'];
  $command = "<SetRFP><rfp id=\"$rfpid\" name=\"$name\" /></SetRFP>";
  axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
}
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">SIP-DECT</div>
</div>
<div class="w3-row w3-container">
  <div class="w3-row w3-quarter w3-padding-large">
    <h5>OMM Einstellungen:</h5>
    <form method="post" action="adm_sipdect.php">
      <p><label>OMM IP Adresse:</label>
      <input class="w3-input w3-border" type="text" name="omm_ip" value="<?php echo @$omm_ip; ?>" style="width:300px"></p>
      <p><label>Registrar Adresse:</label><br />
      <select class="w3-select w3-padding" name="registrar_ip" style="width:300px">
        <option value="<?php echo $cfg['fb']['ip']; ?>" <?php echo @$r_check_ip; ?>>[IP] <?php echo $cfg['fb']['ip']; ?></option>
        <option value="<?php echo $cfg['fb']['host']; ?>" <?php echo @$r_check_fqdn; ?>>[FQDN] <?php echo $cfg['fb']['host']; ?></option>
      </select>
      <p><label>Proxy Adresse:</label><br />
      <select class="w3-select w3-padding" name="proxy_ip" style="width:300px">
        <option value="<?php echo $cfg['fb']['ip']; ?>" <?php echo @$p_check_ip; ?>>[IP] <?php echo $cfg['fb']['ip']; ?></option>
        <option value="<?php echo $cfg['fb']['host']; ?>" <?php echo @$p_check_fqdn; ?>>[FQDN] <?php echo $cfg['fb']['host']; ?></option>
      </select>
      <p><label>OMM Passwort:</label>
      <input class="w3-input w3-border" type="text" name="omm_password" value="<?php echo @$omm_password; ?>" style="width:300px"></p>
      <p><label>Root Passwort:</label>
      <input class="w3-input w3-border" type="text" name="root_password" value="<?php echo @$root_password; ?>" style="width:300px"></p>
      <p><label>System Name:</label>
      <input class="w3-input w3-border" type="text" name="system_name" value="<?php echo @$system_name; ?>" style="width:300px"></p>
      <p><input type="hidden" name="submit" />
      <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
    </form>
    <form action="adm_sipdect.php" method="post" name="reboot">
      <input type="hidden" name="reboot"><p><button class="w3-btn w3-blue" type="submit">OMM neu starten</button></p>
    </form>
    <form action="adm_sipdect.php" method="post" name="check">
      <input type="hidden" name="check"><p><button class="w3-btn w3-blue" type="submit">OMM Check-Sync</button></p>
    </form>
    <form action="adm_sipdect.php" method="post" name="prov">
      <input type="hidden" name="prov"><p><button class="w3-btn w3-blue" type="submit">OMM Prov-Sync</button></p>
    </form>
  </div>
  <div class="w3-row w3-rest w3-padding-large">
  <?php
      
      $result = axi_login($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass']);
      $xml = simplexml_load_string($result);
      $att = $xml->attributes();

      $command = '<GetPARK />';
      $park = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
      
      $park_xml = simplexml_load_string($park);
      $park_att = $park_xml->attributes();
      
    ?>
    <h5>OMM Daten:</h5>
    <h6><?php echo $att->ommVersion; ?> (Protokollversion: <?php echo $att->protocolVersion; ?>)</h6>
    <ul class="w3-ul">
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px">PARK</div>
        <div class="w3-bar-item" style="width:130px">Mobilteile FW:</div>
        <div class="w3-bar-item" style="width:100px">600d</div>
        <div class="w3-bar-item" style="width:100px">6x2d/650c</div>
        <div class="w3-bar-item" style="width:100px">602v2</div>
        <div class="w3-bar-item" style="width:100px">700</div>
      </li>

      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px"><?php echo $park_att->park; echo ' ('.$park_att->initialPARK.')'; if($park_att->park == '') { ?> <a class="w3-tag w3-blue" href="adm_sipdect?park=1">PARK anfordern</a><?php } ?></div>
        <div class="w3-bar-item" style="width:130px"> </div>
        <div class="w3-bar-item" style="width:100px"><?php echo $att->minPPSwVersion1; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $att->minPPSwVersion2; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $att->minPPSwVersion3; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $att->minPPSwVersion4; ?></div>
      </li>
    </ul>
    <h5>RFP Daten:</h5>
    <ul class="w3-ul">
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:80px">RPF ID</div>
        <div class="w3-bar-item" style="width:150px">Name</div>
        <div class="w3-bar-item" style="width:100px">Hardware</div>
        <div class="w3-bar-item" style="width:180px">MAC Adresse</div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-floppy-disk" title="Software Version"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-tower-cell" title="DECT Status"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-wifi" title="WLAN Status"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-magnifying-glass" title="Paging-Bereich"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-location-dot" title="Standort"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-circle-nodes" title="Cluster"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-link" title="Online/Verbunden"></i></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-handshake" title="Sync Status"></i></div>
      </li>
      <?php
        $command = "<GetRFP id=\"0\" maxRecords=\"5\" withState=\"1\" withDetails=\"1\" />";
        $rfp = axi($cfg['omm']['ip'],$cfg['omm']['user'],$cfg['omm']['pass'],$command);
        if (isset($_GET['edit'])) {
          $edit_rfp = $_GET['edit'];
        }
        $rfp_xml = simplexml_load_string($rfp);
        foreach($rfp_xml->rfp as $rfp) {
          $rfp_att = $rfp->attributes();
          if($rfp_att->id == @$edit_rfp) {
      ?>
      <li class="w3-bar">
        <form action="adm_sipdect.php" method="post">
          <div class="w3-bar-item" style="width:80px"><?php echo $rfp_att->id; ?></div>
          <div class="w3-bar-item" style="width:150px"><input class="w3-input w3-border" name="name" value="<?php echo $rfp_att->name; ?>"></div>
          <div class="w3-bar-item" style="width:100px"><?php echo $rfp_att->hwType; ?></div>
          <div class="w3-bar-item" style="width:180px"><?php echo $rfp_att->ethAddr; ?></div>
          <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-circle-info" title="<?php echo $rfp_att->swVersion; ?>"></i></div>
          <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->dectOn == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
          <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->wlanOn == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
          <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->pagingArea; ?></div>
          <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->site; ?></div>
          <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->cluster; ?></div>
          <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->connected == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
          <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->syncState == 'Synced') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
          <div class="w3-bar-item" style="width:50px"><input type="hidden" name="submitedit" value="<?php echo $rfp_att->id; ?>" /><button type="submit"><i class="fa-solid fa-floppy-disk" title="Speichern"></i></button></div>
          <div class="w3-bar-item" style="width:50px"><a href="adm_sipdect.php"><i class="fa-solid fa-xmark" title="Abbrechen"></i></a></div>
        </form>
      </li>
      <?php
          } else {
      ?>
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:80px"><?php echo $rfp_att->id; ?></div>
        <div class="w3-bar-item" style="width:150px"><?php echo $rfp_att->name; ?></div>
        <div class="w3-bar-item" style="width:100px"><?php echo $rfp_att->hwType; ?></div>
        <div class="w3-bar-item" style="width:180px"><?php echo $rfp_att->ethAddr; ?></div>
        <div class="w3-bar-item" style="width:50px"><i class="fa-solid fa-circle-info" title="<?php echo $rfp_att->swVersion; ?>"></i></div>
        <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->dectOn == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
        <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->wlanOn == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
        <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->pagingArea; ?></div>
        <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->site; ?></div>
        <div class="w3-bar-item" style="width:50px"><?php echo $rfp_att->cluster; ?></div>
        <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->connected == 'true') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
        <div class="w3-bar-item" style="width:50px"><?php if($rfp_att->syncState == 'Synced') { echo '<i class="fa-solid fa-check" style="color: #63E6BE;"></i>'; } else { echo '<i class="fa-solid fa-xmark" style="color: #e01b24;"></i>'; } ?></div>
        <div class="w3-bar-item" style="width:50px"><a href="adm_sipdect.php?edit=<?php echo $rfp_att->id; ?>"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
        <div class="w3-bar-item" style="width:50px"><a href="adm_sipdect.php?del=<?php echo $rfp_att->id; ?>"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>
      </li>
      <?php
          }
        $nextid = $rfp_att->id+1;
        }
      ?>
    </ul>
    <h5>RFP Suche:</h5>
    <?php
      if(!isset($capture)) {
    ?>
    <ul class="w3-ul">
      <li class="w3-bar">
        <div class="w3-bar-item">
          <form action="adm_sipdect.php" method="post">
            <input type="hidden" name="subs"><button class="w3-btn w3-blue" type="submit">Neue RFP suchen</button>
          </form>
        </div>
      </li>
    </ul>
    <?php
      } else {
    ?>
    <ul class="w3-ul">
      <li class="w3-bar">
        <div class="w3-bar-item">
          <form action="adm_sipdect.php" method="post">
            <input type="hidden" name="stop"><button class="w3-btn w3-blue" type="submit">Suche anhalten</button>
          </form>
        </div>
        <div class="w3-bar-item">
          <form action="adm_sipdect.php" method="post">
            <input type="hidden" name="reload"><button class="w3-btn w3-blue" type="submit">Liste aktualisieren</button>
          </form>
        </div>
      </li>
    </ul>
    <ul class="w3-ul">
      <?php 
        $xml_cap = simplexml_load_string($capturelist); 
        foreach($xml_cap->rfp as $newrfp) {
        $newrfp_att = $newrfp->attributes();
      ?>
      <li class="w3-bar">
        <form action="adm_sipdect.php" method="post">
          <div class="w3-bar-item" style="width:200px"><?php echo $newrfp_att->ethAddr; ?></div>
          <div class="w3-bar-item" style="width:200px"><?php echo $newrfp_att->ipAddr; ?></div>
          <div class="w3-bar-item" style="width:150px"><?php echo $newrfp_att->hwType; ?></div>
          <div class="w3-bar-item">
            <a href="adm_sipdect.php?addrfp=<?php echo $nextid; ?>&ethAddr=<?php echo $newrfp_att->ethAddr; ?>&hwType=<?php echo $newrfp_att->hwType; ?>"><i class="fa-solid fa-plus"></i></a>
          </div>
        </form>
      </li>
      <?php
        }
      ?>
    </ul>
    <?php
      }
    ?>
  </div>
</div>
<?php
include('footer.php');
?>
