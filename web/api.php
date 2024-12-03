<?php

include('config.php');
include('funktionen.php');

if(isset($_GET['nst'])) {
    
  $nst = $_GET['nst'];
  $ip = $_SERVER['REMOTE_ADDR'];
  $query = mysqli_query($db_conn,"SELECT model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model = model.model AND usr_telefon.displayname = '$nst'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];

  if(isset($_GET['action'])) {
    $action = $_GET['action'];
    if($action == 'first') {
      $status = db_cell('state','callstate',$nst);
      switch($status) {
        case 'incoming':
          $url = "http://{$cfg['cnf']['fqdn']}/web/answer.php?nst=$nst";
          break;
        case 'outgoing':
          $url = "http://{$cfg['cnf']['fqdn']}/web/hangup.php?nst=$nst";
          break;
        case 'connected':
          $url = "http://{$cfg['cnf']['fqdn']}/web/hangup.php?nst=$nst";
          break;
        case 'offhook':
          $url = "http://{$cfg['cnf']['fqdn']}/web/hangup.php?nst=$nst";
          break; 
        default:
          break;         
      }
    } else {
      $query = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$action'");
      $array = mysqli_fetch_array($query);
      $state = $array['state'];
      switch($state) {
        case 'IDLE':
          $url = "http://{$cfg['cnf']['fqdn']}/web/call.php?nst=$nst&number=$action";
          break;
        case 'disconnected':
          $url = "http://{$cfg['cnf']['fqdn']}/web/call.php?nst=$nst&number=$action";
          break;
        case 'onhook':
          $url = "http://{$cfg['cnf']['fqdn']}/web/call.php?nst=$nst&number=$action";
          break;
        case 'incoming':
          $url = "http://{$cfg['cnf']['fqdn']}/web/pickup.php?nst=$nst";
          break;
        default:
          break;
      }
    }
    if($isset($urls)) {
      file_get_contents($url);
    }
  } else {
    $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$nst'");
    if(mysqli_num_rows($query) == 0) {
      mysqli_query($db_conn,"INSERT INTO cticlient (nst, ip) VALUES ('$nst', '$ip')");
    } else {
      mysqli_query($db_conn,"UPDATE cticlient SET ip='$ip' WHERE nst = '$nst'");
    }
    $query = mysqli_query($db_conn,"SELECT ziel,label FROM tasten WHERE nst='$nst' AND type='blf'");
    if(mysqli_num_rows($query) != 0) {
      while($row = mysqli_fetch_assoc($query)) {
        $ctiarray[] = $row;
      }
    }

    echo json_encode($ctiarray);

  }
}
if(isset($_GET['del'])) {
  
  $nst = $_GET['del'];
  mysqli_query($db_conn,"DELETE FROM cticlient WHERE nst = '$nst'");

}

?>
