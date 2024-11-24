<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');

$result = $x_contact->GetCallList();
$xml = simplexml_load_file($result);
echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Anruflisten</div>
      </div>
      <p> </p>
      <div class="w3-row w3-container">
        <ul class="w3-ul">
          <li class="w3-bar">
            <div class="w3-bar-item" style="width:400px"><h5>Name</h5></div>
            <div class="w3-bar-item" style="width:300px"><h5>Rufnummer</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Datum</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Dauer</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Rufart</h5></div>
          </li>';
$i=1;
foreach ($xml->Call as $call) {
  if($i % 20 == 0) {
    echo '<li class="w3-bar">
            <div class="w3-bar-item" style="width:400px"><h5>Name</h5></div>
            <div class="w3-bar-item" style="width:300px"><h5>Rufnummer</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Datum</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Dauer</h5></div>
            <div class="w3-bar-item" style="width:200px"><h5>Rufart</h5></div>
          </li>';
  }
  if(in_array($call->Port, json_decode(us('fb_ports'), true))) {
    if($call->Type == 1) { // eingehende Anrufe
      if($call->Name != '') { $name = htmlspecialchars($call->Name); } else { $name = '';}
      foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>&nbsp;&nbsp;'; break; } else { $icon='<i class="fa-solid fa-house"></i>&nbsp;&nbsp;'; } }
      if($call->Caller == '') { $icon = ''; }
      echo '<li class="w3-bar">
              <div class="w3-bar-item" style="width:400px">'.$name.'</div>
              <div class="w3-bar-item" style="width:300px">'.$icon.'<a href="#" onclick="executePHP(\''.$call->Caller.'\');">'.$call->Caller.'</a></div>
              <div class="w3-bar-item" style="width:200px">'.$call->Date.'</div>
	            <div class="w3-bar-item" style="width:200px">'.$call->Duration.'</div>
              <div class="w3-bar-item" style="width:200px"><i class="fa-solid fa-phone"></i> <i class="fa-solid fa-arrow-left"></i></div>
            </li>';
    }
    if($call->Type == 3) { // ausgehende Anrufe
      if($call->Name != '') { $name = htmlspecialchars($call->Name); } else { $name = '';}
      foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Called, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>&nbsp;&nbsp;'; break; } else { $icon='<i class="fa-solid fa-house"></i>&nbsp;&nbsp;'; } }
      if($call->Called == '') { $icon = ''; }
      echo '<li class="w3-bar">
              <div class="w3-bar-item" style="width:400px">'.$name.'</div>
              <div class="w3-bar-item" style="width:300px">'.$icon.'<a href="#" onclick="executePHP(\''.$call->Called.'\');">'.$call->Called.'</a></div>
              <div class="w3-bar-item" style="width:200px">'.$call->Date.'</div>
	            <div class="w3-bar-item" style="width:200px">'.$call->Duration.'</div>
              <div class="w3-bar-item" style="width:200px"><i class="fa-solid fa-phone"></i> <i class="fa-solid fa-arrow-right"></i></div>
	          </li>';
    }
    if($call->Type == 2) { // verpasste Anrufe
      if($call->Name != '') { $name = htmlspecialchars($call->Name); } else { $name = '';}
      foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>&nbsp;&nbsp;'; break; } else { $icon='<i class="fa-solid fa-house"></i>&nbsp;&nbsp;'; } }
      if($call->Caller == '') { $icon = ''; }
      echo '<li class="w3-bar">
              <div class="w3-bar-item" style="width:400px">'.$name.'</div>
              <div class="w3-bar-item" style="width:300px">'.$icon.'<a href="#" onclick="executePHP(\''.$call->Caller.'\');">'.$call->Caller.'</a></div>
              <div class="w3-bar-item" style="width:200px">'.$call->Date.'</div>
	            <div class="w3-bar-item" style="width:200px"> </div>
              <div class="w3-bar-item" style="width:200px"><i class="fa-solid fa-phone"></i> <i class="fa-solid fa-xmark"></i></div>
            </li>';
    }
    $i++;
  }
}
echo '</ul></div>';
include('footer.php');
?>
