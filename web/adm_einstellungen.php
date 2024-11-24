<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

$dir = $cfg['cnf']['path'].'/web/tmp/';
if(isset($_POST['deletecache'])) {
  deleteCache($dir);
  $feedback = 'TEMP geleert.';
}

if(isset($_POST['protocol_set'])) {
  $protocol = $_POST['protocol'];
  mysqli_query($db_conn,"UPDATE adm_einstellungen SET protocol='$protocol' WHERE id = '0'");
  $feedback = 'Gespeichert.';
}

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id = '0'");
$array = mysqli_fetch_array($query);
$protocol = $array['protocol'];
$$protocol = 'selected';

$size = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($files as $file) {
  if($file->isFile()) {
    $size += $file->getSize();
  }
}

?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Einstellungen</div>
</div>
<div class="w3-container">
  <p>Größe TEMP Verzeichnis: <?php echo round($size/1024/1024, 2); ?> MB<br />
  <form action="adm_einstellungen.php" method="post">
    <p><input type="hidden" name="deletecache" />
    <button class="w3-btn w3-blue" type="submit">TEMP leeren</button></p>
  </form>
</div>
<div class="w3-container">
  <form action="adm_einstellungen.php" method="post">
    <p><label>Protokoll (https verlangsamt die Telefone):</label><br />
    <select class="w3-select w3-padding" name="protocol" style="width:200px">
      <option value="http" <?php echo @$http; ?>>http</option>
      <option value="https" <?php echo @$https; ?>>https</option>
    </select>
    <p><input type="hidden" name="protocol_set" />
    <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
    Nach dem Wechsel des Protokolls, müssen alle Telefone neu provisioniert und neu gestartet werden.
<?php
  include('footer.php');
?>
