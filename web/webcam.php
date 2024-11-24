<?php
include('config.php');
$webcam_url = db_cell('wc_url_browser','kamera',$nst);
echo '<img src="'.$webcam_url.'">';
if(isset($_POST['door'])) {
  $keypress = str_split(db_cell('door_key','kamera',$nst));
  $xml = '<AastraIPPhoneExecute>';
  foreach($keypress as $key) {
    switch($key) {
      case 1:
        $char = 'KeyPad1';
	      break;
      case 2:
	      $char = 'KeyPad2';
	      break;
      case 3:
	      $char = 'KeyPad3';
	      break;
      case 4:
	      $char = 'KeyPad4';
	      break;
      case 5:
	      $char = 'KeyPad5';
	      break;
      case 6:
	      $char = 'KeyPad6';
	      break;
      case 7:
	      $char = 'KeyPad7';
	      break;
      case 8:
	      $char = 'KeyPad8';
	      break;
      case 9:
	      $char = 'KeyPad9';
	      break;
      case 0:
	      $char = 'KeyPad0';
	      break;
      case '*':
	      $char = 'KeyPadStar';
	      break;
      case '#':
	      $char = 'KeyPadPound';
	      break;
    }
    $xml .= '<ExecuteItem URI="Key:'.$char.'"/>';
  }
  $xml .= '</AastraIPPhoneExecute>';
  push2phone($server,phone_ip($nst),$xml);
}
if(db_cell('door_active','kamera',$nst) == true) {
  if(db_cell('door_url','kamera',$nst) != '') {
    $door_url = db_cell('door_url','kamera',$nst);
    echo '<script>function dooropen() {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.open("GET","'.$door_url.'",true);
                    xmlhttp.send();
                  }</script>';
    echo '<br /><a href="#" onclick="dooropen()">Türöffner</a>';
  } else {
    echo '<form action="webcam.php" method="post">
          <input type="hidden" name="door" value="open">
	        <input type="submit" value="Türöffner">
	        </form>';
  }
}
?>
