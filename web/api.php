<?php

include('config.php');

if(isset($_GET['nst'])) {
    
  $nst = $_GET['nst'];
  $ip = $_SERVER['REMOTE_ADDR'];
  $query = mysqli_query($db_conn,"SELECT * FROM cticlient WHERE nst = '$nst'");
  if(mysqli_num_rows($query) == 0) {
    mysqli_query($db_conn,"INSERT INTO cticlient (nst, ip) VALUES ('$nst', '$ip')");
  } else {
    mysqli_query($db_conn,"UPDATE cticlient SET ip='$ip' WHERE nst = '$nst'");
  }
  $query = mysqli_query($db_conn,"SELECT ziel,label FROM tasten WHERE nst='$nst' AND type='blf'");
  if(mysqli_num_rows($query) != 0) {
    while($row = mysqli_fetch_assoc($query)) {
      $array[] = $row;
    }
  }

  echo json_encode($array);

}
if(isset($_GET['del'])) {
  
  $nst = $_GET['del'];
  mysqli_query($db_conn,"DELETE FROM cticlient WHERE nst = '$nst'");

}

?>
