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

if(isset($_POST['dectsystem_set'])) {
  $dectsystem = $_POST['dectsystem'];
  mysqli_query($db_conn,"UPDATE adm_einstellungen SET dectsystem='$dectsystem' WHERE id = '0'");
  $feedback = 'Dect System gespeichert.';
}

$dectsystem = 'dect_'.$array['dectsystem'];
$$dectsystem = 'selected';

if(isset($_POST['backup'])) {
  //SQL Dump erstellen
  $timestamp = date('Ymd_His');
  $sqlFile = $cfg['cnf']['path'].'/sql/sqldump_'.$timestamp.'.sql';
  $file = fopen($sqlFile, 'w');
  fwrite($file, "-- Dump der Datenbank: $db_db\n");
  fwrite($file, "-- Erstellungsdatum: " . date('Y-m-d H:i:s') . "\n\n");
  $tablesResult = $db_conn->query('SHOW TABLES');
  if ($tablesResult) {
    while ($table = $tablesResult->fetch_row()) {
      $tableName = $table[0];
      $createTableResult = $db_conn->query("SHOW CREATE TABLE `$tableName`");
      if ($createTableResult) {
        $createTableRow = $createTableResult->fetch_row();
        fwrite($file, "\n-- Struktur für Tabelle `$tableName`\n");
        fwrite($file, $createTableRow[1] . ";\n\n");
      }
      $dataResult = $db_conn->query("SELECT * FROM `$tableName`");
      if ($dataResult) {
        while ($row = $dataResult->fetch_assoc()) {
          $columns = array_keys($row);
          $values = array_values($row);
          foreach ($values as &$value) {
            $value = "'" . $db_conn->real_escape_string($value) . "'";
          }
          $sql = "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
          fwrite($file, $sql);
        }
      }
    }
  }
  fclose($file);
  //ZIP Archiv erstellen
  $backupDir = $cfg['cnf']['path'].'/backup';
  $zipFile = $backupDir . '/backup_'.$timestamp.'.zip';
  $zip = new ZipArchive();
  if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    addDirToZip($zip, $cfg['cnf']['path'], $cfg['cnf']['path']);
    $zip->close();
    $feedback = 'Backup erfolgreich erstellt.';
  } else {
    $feedback = 'Backup gescheitert.';
  }
}

if(isset($_GET['removebackup'])) {
  $removeBackup = $_GET['removebackup'];
  $backups = array_diff(scandir($cfg['cnf']['path'].'/backup', SCANDIR_SORT_DESCENDING), array('.', '..'));
  foreach($backups as $backup) {
    if($backup == $removeBackup) {
      unlink($cfg['cnf']['path'].'/backup/'.$backup);
    }
  }
}

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
<div class="w3-row w3-third">
  <div class="w3-container">
    <p>Größe TEMP Verzeichnis: <?php echo round($size/1024/1024, 2); ?> MB</p>
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
    </form>
    Nach dem Wechsel des Protokolls, müssen alle Telefone neu provisioniert und neu gestartet werden.
  </div>
  <div class="w3-container">
    <form action="adm_einstellungen.php" method="post">
      <p><label>DECT System:</label><br />
      <select class="w3-select w3-padding" name="dectsystem" style="width:200px">
        <option value="mitel" <?php echo @$dect_mitel; ?>>Mitel SIP-DECT</option>
        <option value="gigaset" <?php echo @$dect_gigaset; ?>>Gigaset Pro</option>
      </select>
      <p><input type="hidden" name="dectsystem_set" />
      <button class="w3-btn w3-blue" type="submit">Speichern</button></p>
    </form>
  </div>
</div>
<div class="w3-row w3-third">
  <div class="w3-container">
    <p>Backups:</p>
    <form action="adm_einstellungen.php" method="post">
      <p><input type="hidden" name="backup" />
      <button class="w3-btn w3-blue" type="submit">Backup erstellen</button></p>
    </form>
    <?php
      $backups = array_diff(scandir($cfg['cnf']['path'].'/backup', SCANDIR_SORT_DESCENDING), array('.', '..'));
      foreach($backups as $backup) {
        $fileSize = filesize($cfg['cnf']['path'].'/backup/'.$backup);
    ?>
        <a href="../backup/<?php echo $backup; ?>"><?php echo $backup; ?></a> (<?php echo round($fileSize/1024/1024, 2); ?> MB)&nbsp;&nbsp;<a href="adm_einstellungen.php?removebackup=<?php echo $backup; ?>" onClick="return confirm('Möchtest du das Backup <?php echo $backup; ?> wirklich löschen?');"><i class="fa-solid fa-trash" title="Löschen"></i></a><br />
    <?php
      }
    ?>
  </div>
</div>
<?php
  include('footer.php');
?>
