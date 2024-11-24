<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

if (isset($_POST['submitcam'])) {
  extract($_POST, EXTR_OVERWRITE);
  mysqli_query($db_conn,"INSERT INTO usr_tuerklingel(id,wc_url_s,wc_url_m,wc_url_l,wc_url_browser,wc_browser_type,wc_nst,door_url,door_key) VALUES ('$id','$wc_url_s','$wc_url_m','$wc_url_l','$wc_url_browser','$wc_browser_type','$wc_nst','$door_url','$door_key')");
}
if (isset($_GET['del'])) {
  $id = $_GET['del'];
  mysqli_query($db_conn,"DELETE FROM usr_tuerklingel WHERE id = '$id'");
}
if (isset($_POST['submitedit'])) {
  extract($_POST, EXTR_OVERWRITE);
  $id = $_POST['submitedit'];
  mysqli_query($db_conn,"UPDATE usr_tuerklingel SET id='$id',wc_url_s='$wc_url_s',wc_url_m='$wc_url_m',wc_url_l='$wc_url_l',wc_url_browser='$wc_url_browser',wc_browser_type='$wc_browser_type',wc_nst='$wc_nst',door_url='$door_url',door_key='$door_key'");
}
?>
<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Türklingel</div>
</div>
<div class="w3-container">
<ul class="w3-ul">
  <li class="w3-bar">
    <div class="w3-bar-item" style="width:50px">ID</div>
    <div class="w3-bar-item" style="width:200px">URL 6867i/6920</div>
    <div class="w3-bar-item" style="width:200px">URL 6869i/6930</div>
    <div class="w3-bar-item" style="width:200px">URL 6873i/6940</div>
    <div class="w3-bar-item" style="width:200px">URL Browser</div>
    <div class="w3-bar-item" style="width:200px">Medientyp Browser</div>
    <div class="w3-bar-item" style="width:200px">Webcam NSt</div>
    <div class="w3-bar-item" style="width:200px">Türöffner URL</div>
    <div class="w3-bar-item" style="width:200px">Türöffner DTMF</div>
  </li>
<?php
if (isset($_GET['edit'])) {
  $edit_id = $_GET['edit'];
}
$query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
while ($row = mysqli_fetch_array($query)) {
  extract($row, EXTR_OVERWRITE);
  if($id == @$edit_id) {
    echo '<form method="post" action="adm_tuerklingel.php">
            <li class="w3-bar">
              <div class="w3-bar-item" style="width:50px">'.$id.'</div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_s" value="'.$wc_url_s.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_m" value="'.$wc_url_m.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_l" value="'.$wc_url_l.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_browser" value="'.$wc_url_browser.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_browser_type" value="'.$wc_browser_type.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_nst" value="'.$wc_nst.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="door_url" value="'.$door_url.'"></div>
              <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="door_key" value="'.$door_key.'"></div>
              <div class="w3-bar-item" style="width:50px">
                <input type="hidden" name="submitedit" value="'.$id.'" />
                <button type="submit"><i class="fa-solid fa-check"></i></i></button>
              </div>
              <div class="w3-bar-item" style="width:50px">
                <a href="adm_tuerklingel.php"><i class="fa-solid fa-xmark"></i></a>
              </div>
            </li>
            </form>';
    } else {
      echo '<li class="w3-bar">';
        echo '<div class="w3-bar-item" style="width:50px">'.$id.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_url_s.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_url_m.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_url_l.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_url_browser.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_browser_type.'</div>
              <div class="w3-bar-item" style="width:200px">'.$wc_nst.'</div>
              <div class="w3-bar-item" style="width:200px">'.$door_url.'</div>
              <div class="w3-bar-item" style="width:200px">'.$door_key.'</div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_tuerklingel.php?edit='.$id.'"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
              <div class="w3-bar-item" style="width:50px"><a href="adm_tuerklingel.php?del='.$id.'"><i class="fa-solid fa-trash" title="Löschen"></i></a></div>';
      echo '</li>';
    }
  }


if(mysqli_num_rows($query) == 0) {
?>
<form method="post" action="usr_telefon.php">
  <li class="w3-bar">
    <div class="w3-bar-item" style="width:50px"> </div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_s"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_m"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_l"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_url_browser"></div>
    <div class="w3-bar-item" style="width:200px">
      <select class="w3-select w3-padding" name="wc_browser_type">
        <option value="image">Bild</option>
        <option value="video">Video</option>
      </select>
    </div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="wc_nst"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="door_url"></div>
    <div class="w3-bar-item" style="width:200px"><input class="w3-input w3-border" type="text" name="door_key"></div>
    <div class="w3-bar-item" style="width:50px">
      <input type="hidden" name="submitcam" />
      <button type="submit"><i class="fa-solid fa-check"></i></button>
    </div>
  </li>
</form>
<?php } ?>
</ul>
</div>
<?php
if (isset($feedback)) {
  echo '<div class="w3-center">'.$feedback.'</div>';
}
include('footer.php');
?>
