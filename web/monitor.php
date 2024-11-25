<?php
  include('config.php');
  include('funktionen.php');
  $nst = $_GET['nst'];

  $user_query = mysqli_query($db_conn,"SELECT id FROM adm_benutzer WHERE nst = '$nst'");
  $user_array = mysqli_fetch_array($user_query);
  $user_id = $user_array['id'];
  $settings_query = mysqli_query($db_conn,"SELECT * FROM usr_einstellungen WHERE user_id = '$user_id'");
  $settings_array = mysqli_fetch_array($settings_query);
  $cell_prefix = json_decode($cfg['cnf']['cell_prefix'], true);
  $fb_ports = json_decode($settings_array['fb_ports'], true);

  $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
  $array = mysqli_fetch_array($callstate);
  $duration = mysqli_query($db_conn,"SELECT * FROM poll WHERE nst='$nst'");
  $duration_array = mysqli_fetch_array($duration);
  //if($duration_array['linestate'] == 'CONNECTED') {
  if(db_cell('linestate','poll',$nst) == 'CONNECTED') {
    //$clock = $duration_array['duration'];
    $clock = db_cell('duration','poll',$nst);
  }
  $webcam_query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
  if(mysqli_num_rows($webcam_query) == 1) {
    $webcam_array = mysqli_fetch_array($webcam_query);
    $webcam_array['wc_active'] = TRUE;
  }
  echo '<div class="w3-bar-item"><h5>Eigene Leitung:</h5></div><div class="w3-bar-item">';
  //$status = $array['state'];
  $status = db_cell('state','callstate',$nst);
  //$remotenumber = $array['remotenumber'];
  $remotenumber = db_cell('remotenumber','callstate',$nst);
  foreach($cell_prefix as $prefix) { if(str_starts_with($remotenumber, $prefix)) { $icon='fa-mobile-screen-button'; $icon2='fa-mobile-screen-button'; break; } else { $icon='fa-house'; $icon2='fa-house'; } }
  switch($status) {
    case 'IDLE':
      echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$nst.': Leitung frei</b>';
      break;
    case 'disconnected':
      echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$nst.': Leitung frei</b>';
      break;
    case 'onhook':
      echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$nst.': Leitung frei</b>';
      break;
    case 'incoming':
      if(($webcam_array['wc_active'] == TRUE) AND ($remotenumber == $webcam_array['wc_nst'])) {
        echo '<a href="#" onclick="answer('.$nst.')"><i class="fa-solid '.$icon.' fa-bounce" style="color: #ffd43b;"></i></a>&nbsp;&nbsp;<b>Türklingel</b>&nbsp;&nbsp;<a href="#" onclick="webcam()"><i class="fa-solid fa-video fa-bounce" style="color: #ffff00;"></i></a>';
      } else {
        echo '<a href="#" onclick="answer('.$nst.')"><i class="fa-solid '.$icon.' fa-bounce" style="color: #ffd43b;"></i></a>&nbsp;&nbsp;<b>ruft: ' .$remotenumber .'</b>';
      }
      break;
    case 'outgoing':
      echo '<a href="#" onclick="hangup('.$nst.')"><i class="fa-solid '.$icon.' fa-bounce" style="color: #ffd43b;"></i></a>&nbsp;&nbsp;<b>wählt: ' .$remotenumber.'</b>';
      break;
    case 'connected':
      echo '<a href="#" onclick="hangup('.$nst.')"><i class="fa-solid '.$icon2.' fa-beat" style="color: #ff0000;"></i></a>&nbsp;&nbsp;<b>620: Gespräch: ' .$remotenumber.'&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa-regular fa-clock fa-beat"></i>&nbsp;&nbsp;'.gmdate("i:s", @$clock).'</b></a>';
      break;
    case 'offhook':
      echo '<a href="#" onclick="hangup('.$nst.')"><i class="fa-solid '.$icon2.' fa-beat" style="color: #ff0000;"></i></a>&nbsp;&nbsp;<b>620: Hörer abgehoben</b>';
      break;
  }
  echo '</div><div class="w3-bar-item"><h5>BLF Tasten:</h5></div>';
  $query = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND type='blf'");
  if(mysqli_num_rows($query) != 0) {
    while($row = mysqli_fetch_array($query)) {
      echo '<div class="w3-bar-item">';
      $blf_taste = $row['ziel'];
      $blf_name = $row['label'];
      $blf_query = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$blf_taste'");
      $blf_array = mysqli_fetch_array($blf_query);
      $blf_status = $blf_array['state'];
      $blf_remotenumber = $blf_array['remotenumber'];
      foreach($cell_prefix as $prefix) { if(str_starts_with($blf_remotenumber, $prefix)) { $blficon='fa-mobile-screen-button'; $blficon2='fa-mobile-screen-button'; break; } else { $blficon='fa-house'; $blficon2='fa-house'; } }
      switch($blf_status) {
        case 'IDLE':
          echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): Leitung frei</b>';
          break;
        case 'disconnected':
          echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): Leitung frei</b>';
          break;
        case 'onhook':
          echo '<i class="fa-solid fa-circle" style="color: #00ff00;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): Leitung frei</b>';
          break;
        case 'incoming':
          echo '<a href="#" onclick="pickup('.$nst.')"><i class="fa-solid '.$blficon.' fa-bounce" style="color: #ffd43b;"></i></a>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): wird gerufen von '.$blf_remotenumber.'</b></a>';
          break;
        case 'outgoing':
          echo '<i class="fa-solid '.$blficon.' fa-bounce" style="color: #ffd43b;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): wählt '.$blf_remotenumber.'</b>';
          break;
        case 'connected':
          echo '<i class="fa-solid '.$blficon2.' fa-beat" style="color: #ff0000;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): im Gespräch mit '.$blf_remotenumber.'</b>';
          break;
        case 'offhook':
          echo '<i class="fa-solid '.$blficon2.' fa-beat" style="color: #ff0000;"></i>&nbsp;&nbsp;<b>'.$blf_name.' ('.$blf_taste.'): Hörer abgehoben</b>';
          break;
      }
      echo '</div>';
    }
    if(($webcam_array['wc_active'] == TRUE) AND ($remotenumber == $webcam_array['wc_nst']) AND (filter_var($webcam_array['wc_url_browser'], FILTER_VALIDATE_URL))) {
      if($webcam_array['wc_browser_type'] == 'image') {
        echo '<div class="w3-bar-item w3-center"><a href="#" onclick="webcam()"><img src="'.$webcam_array['wc_url_browser'].'" style="width:300px"></a></div>';
      }
      if($webcam_array['wc_browser_type'] == 'video') {
        echo '<div class="w3-bar-item w3-center"><video src="'.$webcam_array['wc_url_browser'].'" autoplay style="width:300px"></video></div>';
      }
    }
  }

?>
