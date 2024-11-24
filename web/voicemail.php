<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');
if(isset($_GET['toggle'])) {
  $t = $_GET['toggle'];
  $toggle = $x_tam->SetEnable(
    new SoapParam($fb_ab, 'NewIndex'),
    new SoapParam($t, 'NewEnable'));
}
$path = __DIR__ . '/tmp/*.wav';
$info = $x_tam->GetInfo(new SoapParam(us('fb_ab'), 'NewIndex'));
if($info['NewEnable'] == 0) { $enable = '<i class="fa-solid fa-toggle-off" style="color:#FF0000"></i>'; $toggle=1; } else { $enable = '<i class="fa-solid fa-toggle-on" style="color:#00FF00"></i>'; $toggle=0; }
switch($info['NewCapacity']) {
  case 0:
    $cap = 'keine';
    break;
  case 1:
    $cap = '1 Minute';
    break;
  default:
    $cap = $info['NewCapacity'] . ' Minuten';
    break;
}
echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Voicemail</div>
      	<div class="w3-bar-item w3-padding-large"><a href="voicemail.php?&toggle='.$toggle.'">'.$enable.'</a></div>
	      <div class="w3-bar-item w3-padding-large">Name: '.$info['NewName'].'</div>
	      <div class="w3-bar-item w3-padding-large">Restzeit: '.$cap.'</div>
      </div>';
if(isset($_GET['del'])) {
  $d = $_GET['del'];
  $d = (int)$d;
  $delete = $x_tam->DeleteMessage(
     new SoapParam(us('fb_ab'), 'NewIndex'),
     new SoapParam($d, 'NewMessageIndex'));
}
if(isset($_GET['status'])) {
  $mark = $_GET['status'];
  $mark = (int)$mark;
  $id = $_GET['id'];
  $id = (int)$id;
  $status = $x_tam->MarkMessage(
     new SoapParam(us('fb_ab'), 'NewIndex'),
     new SoapParam($id, 'NewMessageIndex'),
     new SoapParam($mark, 'NewMarkedAsRead')
     );
}
$result = $x_tam->GetMessageList(new SoapParam(us('fb_ab'), 'NewIndex'));
$xml = simplexml_load_file($result);
$sid = explode('sid=',$result);
$sid = explode('&', $sid[1]);
$sid = $sid[0];
echo '<div class="w3-row w3-container"><ul class="w3-ul">';
if(count($xml->Message) > 0) {
  echo '<li class="w3-bar">
          <div class="w3-bar-item" style="width:15px">ID</div>
	        <div class="w3-bar-item" style="width:75px">Status</div>
          <div class="w3-bar-item" style="width:200px">Name</div>
          <div class="w3-bar-item" style="width:200px">Rufnummer</div>
          <div class="w3-bar-item" style="width:150px">Datum</div>
	        <div class="w3-bar-item" style="width:100px">Länge</div>
	        <div class="w3-bar-item"> </div>
          <div class="w3-bar-item"> </div>
	        <div class="w3-bar-item"> </div>
        </li>';
  foreach($xml->Message as $message) {
    echo '<li class="w3-bar">
            <div class="w3-bar-item" style="width:15px">'.$message->Index.'</div>';
    if($message->New==1) { $status = '<a href="voicemail.php?status=1&id='.$message->Index.'"><i class="fa-regular fa-envelope"></i></a>'; } else { $status = '<a href="voicemail.php?status=0&id='.$message->Index.'"><i class="fa-regular fa-envelope-open"></i></a>'; }
    echo '  <div class="w3-bar-item" style="width:75px"><div class="w3-center">'.$status.'</div></div>';
    if($message->Name != '') { echo '<div class="w3-bar-item" style="width:200px">'.$message->Name.'</div>'; } else { echo '<div class="w3-bar-item" style="width:200px"><i>unbekannt</i></div>'; }
    echo '<div class="w3-bar-item" style="width:200px">'.$message->Number.'</div>
          <div class="w3-bar-item" style="width:150px">'.$message->Date.'</div>
          <div class="w3-bar-item" style="width:100px">'.$message->Duration.'</div>
          <div class="w3-bar-item"><a href="#" onclick="showAudio(\''.$message->Index.'\')"><i class="fa-solid fa-play"></i></a></div>
          <div class="w3-bar-item"><a href="#" onclick="executePHP(\''.$message->Number.'\');"><i class="fa-solid fa-phone"></i></a></div>
          <div class="w3-bar-item"><a href="voicemail.php?del='.$message->Index.'"><i class="fa-solid fa-trash"></i></a></div>
        </li>';
  }
} else {
  echo '<li class="w3-bar"><div class="w3-bar-item">keine Nachrichten</div></li>';
}
echo '</ul><div id="txtAudio"></div></div>';
include('footer.php');
?>
