<?php

  include('config.php');
  include('session.php');
  include('funktionen.php');
  include('auth.php');
  include('usr_header.php');
  include('usr_menu.php');

  $nst = $_SESSION['nst'];

  $taste_art = $_GET['edit'];
  $taste_nr = $_GET['taste'];
  $taste = $taste_art.$taste_nr;

  $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst = '$nst' AND taste='$taste'");
  if(mysqli_num_rows($query)==1) {
    $array = mysqli_fetch_array($query);

    $type = 'key_'.$array['type'];
    $$type = 'selected';
    $label = $array['label'];
    $ziel = $array['ziel'];
    $keyid = $array['id'];
  }
  if(str_starts_with($taste_art, 'exp')) {
    $keytype = 'expkeys';
  } else {
    $keytype = $taste_art.'s';
  }
  

  
?>
<script>
function showKey(str) {
  if (str == "") {
    document.getElementById("txtHint").innerHTML = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("keyHint").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET","tastentyp.php?nst=<?php echo @$nst; ?>&ziel=<?php echo @$ziel; ?>&label=<?php echo @$label; ?>&q="+str,true);
    xmlhttp.send();
  }
}
window.onload = function() {
  var selectElement = document.getElementById("keyselect");
  var selectedValue = selectElement.value;
  showKey(selectedValue);
};
</script>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large"><?php echo $taste_art; ?> <?php echo $taste_nr; ?></div>
</div>
<div class="w3-container">
  <form action="einstellungen.php" method="post">
    <p><label>Tastentyp:</label><br />
    <select id="keyselect" class="w3-select w3-padding" name="type" style="width:300px" onchange="showKey(this.value)">
      <option value="leer">leer</option>
      <option value="blf" <?php echo @$key_blf; ?>>BLF</option>
      <option value="speeddial" <?php echo @$key_speeddial; ?>>Kurzwahl</option>
      <option value="line" <?php echo @$key_line; ?>>Leitung</option>
      <option value="mobilelink" <?php echo @$key_mobilelink; ?>>MobilLink</option>
      <option value="voicemail" <?php echo @$key_voicemail; ?>>Anrufbeantworter</option>
      <option value="telefonbuch" <?php echo @$key_telefonbuch; ?>>Telefonbuch</option>
      <option value="anrufliste" <?php echo @$key_anrufliste; ?>>Anrufliste</option>
      <option value="rvt" <?php echo @$key_rvt; ?>>Ruhe vor dem Telefon</option>
      <option value="kamera" <?php echo @$key_kamera; ?>>Kamera</option>
      <option value="tueroeffner" <?php echo @$key_tueroeffner; ?>>Türöffner</option>
      <option value="services" <?php echo @$key_services; ?>>Services</option>
    </select></p>
    <div id="keyHint"></div>
    <input type="hidden" name="key" value="<?php echo $taste; ?>">
    <input type="hidden" name="keytype" value="<?php echo $keytype; ?>">
	<input type="hidden" name="settings" value="keys">
	<button class="w3-btn w3-blue" type="submit">Speichern</button>
  </form>
</div>
<?php

  include('footer.php');

?>
