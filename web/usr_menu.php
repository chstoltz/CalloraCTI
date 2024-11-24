<body onload="showUser()">
<div class="w3-top">
<div class="w3-bar w3-blue">
  <a href="https://www.callora.de" class="w3-bar-item w3-button w3-padding-large w3-right"><b>CalloraCTI</b></a>
  <a href="logout.php" class="w3-bar-item w3-button w3-padding-large w3-right"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;&nbsp;Abmelden</a>
  <a href="einstellungen.php" class="w3-bar-item w3-button w3-padding-large w3-right"><i class="fa-solid fa-gear"></i>&nbsp;&nbsp;Einstellungen</a>
  <div class="w3-bar-item w3-right"><form id="form" onsubmit="executePHP(this.number.value)"><input class="w3-input w3-border" type="text" name="number" id="number" style="line-height:0.2em;height:30px" accesskey="d" placeholder="Nummer wählen" /></form></div>
  <a href="anrufliste.php" class="w3-bar-item w3-button w3-padding-large"><i class="fa-solid fa-list"></i>&nbsp;&nbsp;Anruflisten</a>
  <a href="telefonbuch.php" class="w3-bar-item w3-button w3-padding-large"><i class="fa-regular fa-address-book"></i>&nbsp;&nbsp;Telefonbuch</a>
  <a href="voicemail.php" class="w3-bar-item w3-button w3-padding-large"><i class="fa-solid fa-voicemail"></i>&nbsp;&nbsp;Voicemail</a>
  <a href="rufumleitung.php" class="w3-bar-item w3-button w3-padding-large"><i class="fa-solid fa-arrows-turn-to-dots"></i>&nbsp;&nbsp;Rufumleitungen</a>
<?php
  $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
  if(mysqli_num_rows($query) == 1) {
      echo '<a href="kamera.php" class="w3-bar-item w3-button w3-padding-large"><i class="fa-solid fa-camera"></i>&nbsp;&nbsp;Kamera</a>';
    }
?>
</div>
</div>
<div class="w3-sidebar w3-bar-block w3-light-grey" style="width:400px;right:0">
  <div class="w3-bar w3-green"><div class="w3-bar-item w3-padding-large">Dashboard</div></div>
  <div id="txtHint"></div>
</div>
<div id="main" style="margin-top:47px">
