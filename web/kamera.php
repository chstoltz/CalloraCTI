
<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');

$query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
if(mysqli_num_rows($query) == 1) {
  $array = mysqli_fetch_array($query);
  if($array['wc_url_browser'] != '') {
    $webcam_url = $array['wc_url_browser'];
    $image = '<img src="'.$webcam_url.'" class="w3-container w3-row w3-padding-large">';
  } else {
    $image = '<img src="img/logo.png" style="width:500px" />';
  }
  echo $image;
}
include ('footer.php');
?>
