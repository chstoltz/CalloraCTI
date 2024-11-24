<?php
include('config.php');
include('funktionen.php');
$ab = $_GET['ab'];
$result = $x_tam->GetMessageList(new SoapParam($ab, 'NewIndex'));
$xml = simplexml_load_file($result);
$sid = explode('sid=',$result);
$sid = explode('&', $sid[1]);
$sid = $sid[0];
$query = mysqli_query($db_conn,"SELECT * FROM adm_fritzbox");
$array = mysqli_fetch_array($query);
$fb_url = $array['fb_url'];
if(isset($_GET['p'])) {
  $p = $_GET['p'];
  $p = (int)$p;
  $filename = sha1(rand());
  foreach($xml->Message as $message) {
    if($message->Index == $p) {
      $url = 'https://'.$fb_url.':49443'.$message->Path.'&sid='.$sid;
      set_time_limit(0);
      $fp = fopen (__DIR__ . '/tmp/' . $filename.'.wav', 'w+');
      $ch = curl_init(str_replace(" ","%20",$url));
      curl_setopt($ch, CURLOPT_TIMEOUT, 600);
      curl_setopt($ch, CURLOPT_FILE, $fp); 
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_exec($ch); 
      curl_close($ch);
      fclose($fp);
      echo '<div class="w3-bar-item"><audio controls autoplay src="https://'.$_SERVER['SERVER_NAME'].'/web/tmp/'.$filename.'.wav"></audio></div>';
    }
  }
  $status = $x_tam->MarkMessage(
    new SoapParam($ab, 'NewIndex'),
    new SoapParam($p, 'NewMessageIndex'),
    new SoapParam(1, 'NewMarkedAsRead')
    );
} 
?>

