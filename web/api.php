<?php

include('config.php');
include('funktionen.php');

if(isset($_GET['nst'])) {
    
  $nst = $_GET['nst'];
  $ip = $_SERVER['REMOTE_ADDR'];
  if(isset($_GET['action'])) {
    $action = $_GET['action'];
    if($action == 'first') {
      $status = db_cell('state','callstate',$nst);
      switch($status) {
        case 'incoming':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Headset"/></AastraIPPhoneExecute>';
          break;
        case 'outgoing':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></AastraIPPhoneExecute>';
          break;
        case 'connected':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></AastraIPPhoneExecute>';
          break;
        case 'offhook':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Key:Goodbye"/></AastraIPPhoneExecute>';
          break; 
        default:
          break;         
      }
      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
    } else {
      $query = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$action'");
      $array = mysqli_fetch_array($query);
      $state = $array['state'];
      switch($state) {
        case 'IDLE':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:**'.$action.'"/></AastraIPPhoneExecute>';
          break;
        case 'disconnected':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:**'.$action.'"/></AastraIPPhoneExecute>';
          break;
        case 'onhook':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:**'.$action.'"/></AastraIPPhoneExecute>';
          break;
        case 'incoming':
          $xml = '<AastraIPPhoneExecute><ExecuteItem URI="Dial:*09"/></AastraIPPhoneExecute>';
          break;
        default:
          break;
      }
      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
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
