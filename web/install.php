<?php
$file = __DIR__ . '/config.php';
$prov = dirname(__DIR__).'/prov';

if(isset($_POST['submit'])) {
  extract($_POST, EXTR_OVERWRITE);
  try {
    $db_conn = new PDO("mysql:host=$db_host;dbname=$db_db", $db_user, $db_pass);
    // set the PDO error mode to exception
    $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $config = "<?php\n\n  \$db_host='$db_host';\n  \$db_user='$db_user';\n  \$db_pass='$db_pass';\n  \$db_db='$db_db';\n\n  \$db_conn = mysqli_connect(\$db_host,\$db_user,\$db_pass);\n             mysqli_select_db(\$db_conn,\$db_db);\n             mysqli_set_charset(\$db_conn,'utf8');\n\n?>";
    file_put_contents($file, $config);
    $connect = 1;
  } catch(PDOException $e) {
    $connect = 0;
  }
}
if(file_exists($file)) {
  include('config.php');
  $configfile = 1;
  $connect = 1;
}
if(isset($_POST['settings'])) {
  extract($_POST, EXTR_OVERWRITE);
  $cell_prefix = '["015","016","017"]';
  mysqli_query($db_conn,"INSERT INTO adm_einstellungen (ws_fqdn, ws_ip, ws_path, cell_prefix) VALUES ('$ws_fqdn','$ws_ip','$ws_path','$cell_prefix')");
  $done = 1;
}
include('adm_header.php');
?>
<div class="w3-content" style="max-width:2000px;margin-top:46px">
  <div class="w3-container w3-content w3-padding-64" style="max-width:800px" id="Anmelden">
    <h2><b>Callora<span style="color:#b3b2b2;">C</span><span style="color:#878786;">T</span><span style="color:#23b0e6;">I</span></b> - Installationsassistent</h2>
    <br />
    <ul class="w3-ul">
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px">
          Root-Verzeichnis beschreibbar:<br />
          (<?php echo __DIR__.'/'; ?>)
        </div>
        <div class="w3-bar-item">
          <?php 
            if(is_writeable(__DIR__)) {
              echo '<i class="fa-solid fa-square-check" style="color: #63E6BE;"></i>';
            } else {
              echo '<i class="fa-solid fa-square-xmark" style="color: #ed333b;"></i>';
              echo '</div><div class="w3-bar-item">Bitte Berechtigungen anpassen!';
              exit;
            }
          ?>
        </div>
      </li>
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px">
          Provisionierungs-Verzeichnis beschreibbar:<br />
          (<?php echo __DIR__.'/../prov/'; ?>)
        </div>
        <div class="w3-bar-item">
          <?php 
            if(is_writable(__DIR__.'/../prov')) {
              echo '<i class="fa-solid fa-square-check" style="color: #63E6BE;"></i>';
            } else {
              echo '<i class="fa-solid fa-square-xmark" style="color: #ed333b;"></i>';
              echo '</div><div class="w3-bar-item">Bitte Berechtigungen anpassen!';
              exit;
            }
          ?>
        </div>
      </li>
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px">
          MySQL/MariaDB Verbindung:
        </div>
        <div class="w3-bar-item" style="width:300px">
          <?php
            if(!isset($connect)) {
          ?>
          <form method="post" action="install.php">
            <input class="w3-input w3-border" type="text" name="db_host" placeholder="Servername" /><br />
            <input class="w3-input w3-border" type="text" name="db_user" placeholder="Benutzername" /><br />
            <input class="w3-input w3-border" type="text" name="db_pass" placeholder="Passwort" /><br />
            <input class="w3-input w3-border" type="text" name="db_db" placeholder="Datenbankname" /><br />
            <p><input type="hidden" name="submit"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
          </form>
          <?php
            } elseif ($connect == 0) {
          ?>
          <form method="post" action="install.php">
            <input class="w3-input w3-border" type="text" name="db_host" value="<?php echo $db_host; ?>" placeholder="Servername" /><br />
            <input class="w3-input w3-border" type="text" name="db_user" value="<?php echo $db_user; ?>" placeholder="Benutzername" /><br />
            <input class="w3-input w3-border" type="text" name="db_pass" value="<?php echo $db_pass; ?>" placeholder="Passwort" /><br />
            <input class="w3-input w3-border" type="text" name="db_db" value="<?php echo $db_db; ?>" placeholder="Datenbankname" /><br />
            <p><input type="hidden" name="submit"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
            <p>Verbindung fehlgeschlagen, bitte Zugangsdaten überprüfen.</p>
          </form>
          <?php
            } else {
              echo '<i class="fa-solid fa-square-check" style="color: #63E6BE;"></i>';
            }
          ?>
        </div>
      </li>
      <?php if(isset($configfile)) { ?>
      <li class="w3-bar">
        <div class="w3-bar-item" style="width:300px">
          Datenbankstruktur eingelesen:
        </div>
        <div class="w3-bar-item">
          <?php
            $test = mysqli_query($db_conn,"SHOW TABLES LIKE 'callstate'");
            if(mysqli_num_rows($test) == 0) {
            $script = __DIR__.'/../sql/mysql.sql';
            $sqlScript = file($script);
            $query = '';
            foreach ($sqlScript as $line)	{
              $startWith = substr(trim($line), 0 ,2);
              $endWith = substr(trim($line), -1 ,1);
              if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                continue;
              }
              $query = $query . $line;
              if ($endWith == ';') {
                mysqli_query($db_conn,$query) or die('<i class="fa-solid fa-square-xmark" style="color: #ed333b;"></i> Problem in SQL Query <b>' . $query. '</b></div>');
                $query= '';		
              }
            }
            echo '<i class="fa-solid fa-square-check" style="color: #63E6BE;"></i>';
            $settings = 1;
          } else {
            echo '<i class="fa-solid fa-square-check" style="color: #63E6BE;"></i>';
            $settings = 1;
          }
          ?>
        </div>
      </li>
      <?php } 
      if(@$settings == 1) {      
      ?>
      <li class="w3-bar">
        <div class="w3-bar-item">
        <form method="POST" action="install.php">
          <p><label><b>FQDN des Servers:</b></label>
          <input class="w3-input w3-border" type="text" name="ws_fqdn" value="<?php echo $_SERVER['HTTP_HOST']; ?>" /></p>
          <p><label><b>IP Adresse des Servers:</b></label>
          <input class="w3-input w3-border" type="text" name="ws_ip" value="<?php echo $_SERVER['SERVER_ADDR']; ?>" /><br /></p>
          <p><label><b>Lokaler Pfad auf dem Server:</b></label>
          <input class="w3-input w3-border" type="text" name="ws_path" value="<?php echo dirname(__DIR__); ?>" /><br />
          <p><input type="hidden" name="settings"><button class="w3-btn w3-blue" type="submit">Speichern</button></p>
        </form>
        </div>
      </li>
      <?php } ?>
    </ul>
  
  <?php if(isset($done)) { ?>
  <div class="w3-row">Installation erfolgreich! Aus Sicherheitsgründen solltest du die Datei <span class="w3-tag w3-blue">install.php</span> jetzt löschen.</div>
  <p>Zugangsdaten:<br />
  Benutzername: <b>admin</b><br />
  Passwort: <b>admin</b></p>
  <p>... weiter zu <a href="index.php"><b>Callora<span style="color:#b3b2b2;">C</span><span style="color:#878786;">T</span><span style="color:#23b0e6;">I</span></b></a> ...</p>
  <?php } ?>
 </div>
<?php 
  include('footer.php'); 
?>
