<?php

include('config.php');

$q = $_GET['q'];
$nst = @$_GET['nst'];
$value = @$_GET['ziel'];
$label = @$_GET['label'];

switch($q) {
  case 'leer':
    echo '';
    break;
  case 'blf':
    $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE nst != '0' AND nst != '$nst'");
    if(mysqli_num_rows($query) != 0) {
      echo '<p><label>Beschriftung:</label>
            <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>
            <p><label>Inhalt:</label><br />
            <select class="w3-select w3-padding" name="value" style="width:300px" onchange="showUser(this.value)">';
      while($row = mysqli_fetch_array($query)) {
        if($value == $row['nst']) {
            $selected = 'selected';
        }
        echo '<option value="'.$row['nst'].'" '.@$selected.'>['.$row['nst'].'] '.$row['username'].'</option>';
        unset($selected);
      }
      echo '</select></p>';
    } else {
      echo 'Keine weiteren Nebenstellen verfügbar. Bitte weitere Benutzer/Telefone anlegen.';
    }
    break;
  case 'speeddial':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>
          <p><label>Ziel:</label>
          <input class="w3-input w3-border" type="text" name="value" value="'.@$value.'" style="width:300px"></p>';
    break;
  case 'line':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'mobilelink':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'voicemail':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'telefonbuch':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'anrufliste':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'rvt':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'kamera':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'tueroeffner':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  case 'services':
    echo '<p><label>Beschriftung:</label>
          <input class="w3-input w3-border" type="text" name="label" value="'.@$label.'" style="width:300px"></p>';
    break;
  }

?>