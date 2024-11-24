<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');

$result = $x_contact->GetPhoneBook(new SoapParam(us('fb_book'), 'NewPhoneBookID'));
$xml = simplexml_load_file($result['NewPhonebookURL']);
if(isset($_GET['limit'])) {
  $limit = $_GET['limit'];
  $limit = (int)$limit;
} else {
  $limit = 0;
}
echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Telefonbuch</div>
        <div class="w3-bar-item"><form action="telefonbuch.php"><input class="w3-input w3-border" type="text" name="suche" style="line-height:0.2em;height:30px" accesskey="s" placeholder="Namen suchen"></form></div>
      </div>';
echo '<div class="w3-bottom">';
if(!isset($_GET['s'])) {
  echo '<div class="w3-row w3-grey w3-third">';
  if($limit == 0) {
    echo '<div class="w3-right">&lt; zurück</div>';
  } else {
    echo '<div class="w3-right"><a href="telefonbuch.php?limit='.$limit-12 .'">&lt; zurück</a></div>';
  }
  echo '</div>'; 
  echo '<div class="w3-row w3-grey w3-third"><div class="w3-center">';
} else {
  echo '<div class="w3-row w3-grey"><div class="w3-center"><a href="telefonbuch.php">[Reset]</a> | ';
}
foreach (range('A', 'Z') as $char) {
  echo '<a href="telefonbuch.php?s='.$char.'">'.$char.'</a>&nbsp;';
}
echo '</div></div>';
if(!isset($_GET['s'])) { echo '<div class="w3-row w3-grey w3-third"><a href="telefonbuch.php?limit='.$limit+12 .'">weiter &gt;</a></div>'; }
echo '</div>';
if(isset($_GET['s'])) {
  $suche = $_GET['s'];
  echo '<div class="w3-row w3-container w3-margin-bottom w3-margin-top"><ul class="w3-ul">
        <li class="w3-bar">
          <div class="w3-bar-item" style="width:400px"><h5>Name</h5></div>
          <div class="w3-bar-item"><h5>Rufnummern</h5></div>
        </li>';
  foreach ($xml->phonebook->contact as $contact) {
    $result = str_starts_with($contact->person->realName, $suche);
    if ($result !== false) {
      if ($contact->uniqueid >= 100) {
        $contacts[] = $contact;
      }
    }
  }
  if(empty($contacts)) {
    echo '<li class="w3-bar"><div class="w3-bar-item">kein Eintrag</div></li></ul></div>';
    include('footer.php');
    exit;
  }
  $sum = count($contacts); 
  for ($c=0; $sum; $c++) {
    $number = json_decode(json_encode($contacts[$c]->telephony->number),true);
    array_shift($number);
    $cnt_numbers = count($number);
    echo '<li class="w3-bar"><div class="w3-bar-item" style="width:400px">'.$contacts[$c]->person->realName.'</div>';
    foreach ($number as $phone) {
      if(!is_numeric($phone)) {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $phone = '0'.substr($phone, 2);
      }
      foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($phone, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>'; break; } else { $icon='<i class="fa-solid fa-house"></i>'; } }
        echo '<div class="w3-bar-item" style="width:300px">'.$icon.'&nbsp;&nbsp;<a href="#" onclick="executePHP(\''.$phone.'\');">'.$phone.'</a></div>';
    }
    echo '</li>';
  }
  echo '</ul></div>';
}
if(isset($_GET['suche'])) {
  echo '<div class="w3-row w3-container w3-margin-bottom w3-margin-top"><ul class="w3-ul">
        <li class="w3-bar">
         <div class="w3-bar-item" style="width:400px"><h5>Name</h5></div>
          <div class="w3-bar-item"><h5>Rufnummern</h5></div>
        </li>';
  $suche = $_GET['suche'];
  $c = 0;
  foreach ($xml->phonebook->contact as $contact) {
    $result = strpos(strtolower($contact->person->realName), strtolower($suche));
    if ($result !== false) {
      echo '<li class="w3-bar"><div class="w3-bar-item" style="width:400px">'.$contact->person->realName.'</div>';
      $cnt_numbers = count($contact->telephony->number);
      foreach ($contact->telephony->number as $number) {
        if(!is_numeric($number)) {
          $number = preg_replace("/[^0-9]/", "", $number);
          $number = '0'.substr($number, 2);
	}
	foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($number, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>'; break; } else { $icon='<i class="fa-solid fa-house"></i>'; } }
	echo '<div class="w3-bar-item" style="width:300px">'.$icon.'&nbsp;&nbsp;<a href="#" onclick="executePHP(\''.$number.'\');">'.$number.'</a></div>';
      }
      echo'</li>';
      $c++;
    }
  }
  if ($c == 0) {
    echo '<li class="w3-bar"><div class="w3-bar-item">kein Eintrag</div></li>';
  } 
  echo '</ul></div>';
  include('footer.php');
  exit;
}
echo '<div class="w3-row w3-container w3-margin-top">';
foreach ($xml->phonebook->contact as $contact) {
  if ($contact->uniqueid >= 100) {
    $contacts[] = $contact;
  }
}
echo '<ul class="w3-ul">
        <li class="w3-bar">
          <div class="w3-bar-item" style="width:400px"><h5>Name</h5></div>
          <div class="w3-bar-item"><h5>Rufnummern</h5></div>
        </li>';
for ($c=$limit; $c<$limit+12; $c++) {
  $number = json_decode(json_encode($contacts[$c]->telephony->number),true);
  array_shift($number);
  $cnt_numbers = count($number);
  echo '<li class="w3-bar"><div class="w3-bar-item" style="width:400px">'.$contacts[$c]->person->realName.'</div>';
  foreach ($number as $phone) {
    if(!is_numeric($phone)) {
      $phone = preg_replace("/[^0-9]/", "", $phone);
      $phone = '0'.substr($phone, 2);
    }
    foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($phone, $prefix)) { $icon='<i class="fa-solid fa-mobile-screen-button"></i>'; break; } else { $icon='<i class="fa-solid fa-house"></i>'; } }
    echo '<div class="w3-bar-item" style="width:300px">'.$icon.'&nbsp;&nbsp;<a href="#" onclick="executePHP(\''.$phone.'\');">'.$phone.'</a></div>';
  }
  echo '</li>';  
} 
echo '</ul></div>';
include('footer.php');
?>
