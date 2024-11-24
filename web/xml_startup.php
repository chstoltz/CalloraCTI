<?php
include('config.php');
include('funktionen.php');
$user_agent=$_SERVER["HTTP_USER_AGENT"];
if(stristr($user_agent,"Aastra")) {
  $value=preg_split("/ MAC:/",$user_agent);
  $end=preg_split("/ /",$value[1]);
  $value[1]=preg_replace("/\-/","",$end[0]);
  $value[2]=preg_replace("/V:/","",$end[1]);
  $firmware = $value[2];
  $mac = $value[1];
  mysqli_query($db_conn,"UPDATE usr_telefon SET firmware='$firmware' WHERE mac='$mac'");
} 
if(stristr($user_agent,"Mitel")) {
  $value=preg_split("/ MAC:/",$user_agent);
  $end=preg_split("/ /",$value[1]);
  $value[1]=preg_replace("/\-/","",$end[0]);
  $value[2]=preg_replace("/V:/","",$end[1]);
  $firmware = $value[2];
  $mac = $value[1];
  mysqli_query($db_conn,"UPDATE usr_telefon SET firmware='$firmware' WHERE mac='$mac'");
}
if(stristr($user_agent,"snom")) {
  $array = explode(' ', $user_agent);
  $mac = $array[10];
  $firmware = $array[3];
  mysqli_query($db_conn,"UPDATE usr_telefon SET firmware='$firmware' WHERE mac='$mac'");
}
if(stristr($user_agent,"Yealink")) {
  $array = explode(' ', $user_agent);
  $firmware = $array[2];
  mysqli_query($db_conn,"UPDATE usr_telefon SET firmware='$firmware' WHERE mac='$mac'");
}
if(isset($_GET['vendor'])) {
  $vendor = $_GET['vendor'];
}
switch($vendor) {
  case 'mitel':
    $endecho = '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
    break;
  case 'yealink':
    $endecho = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI=""/></YealinkIPPhoneExecute>';
    break;
  case 'snom':
    $endecho = '';
    break;
}
echo $endecho;
?>
