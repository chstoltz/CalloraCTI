<?php
include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('usr_header.php');
include('usr_menu.php');

if(isset($_GET['toggle'])) {
  $t = $_GET['toggle'];
  $id = $_GET['id'];
  $toggle = $x_contact->SetDeflectionEnable(
  new SoapParam($id, 'NewDeflectionId'),
  new SoapParam($t, 'NewEnable'));
}

echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Rufumleitungen</div>
      </div>';
if (us('fb_deflection') == '99') {
  echo '<div class="w3-container">Keine Rufumleitung zugeordnet</div>';
} else {
  echo '<div class="w3-row w3-container"><ul class="w3-ul">
        <li class="w3-bar">
          <div class="w3-bar-item" style="width:15px">ID</div>
          <div class="w3-bar-item" style="width:75px">Status</div>
          <div class="w3-bar-item" style="width:200px">Rufnummer</div>
          <div class="w3-bar-item" style="width:200px">Weiterleitung an</div>
          <div class="w3-bar-item" style="width:200px">Art</div>
          <div class="w3-bar-item" style="width:150px">über VoIP Acc.</div>
        </li>';
$def_id = us('fb_deflection');
$result = $x_contact->GetDeflection(new SoapParam($def_id, 'NewDeflectionId'));

switch($result['NewMode']) {
  case 'eParallelCall':
    $type = 'Parallelruf';
    break;
  case 'eShortDelayed':
    $type = 'nach 20 Sekunden';
    break;
  case 'eDelayedOrBusy':
    $type = 'nach Zeit und bei besetzt';
    break;
  case 'eImmediately':
    $type = 'sofort';
    break;
  case 'eLongDelayed':
    $type = 'nach 40 Sekunden';
    break;
  }
if($result['NewOutgoing'] != '') {
  $voipaccountid = substr($result['NewOutgoing'], -1);
  $voipaccounts = $x_voip->{'X_AVM-DE_GetVoIPAccounts'}();
  $xmlvoipaccounts = simplexml_load_string($voipaccounts);
  foreach($xmlvoipaccounts->Account as $account) {
    if($account->VoIPAccountIndex == $voipaccountid) {
      $def_account = $account->VoIPNumber;
    }
  }
} else { 
  $def_account = 'Automatisch'; 
}
if($result['NewEnable'] == 0) { $enable = '<i class="fa-solid fa-toggle-off" style="color:#FF0000"></i>'; $toggle=1; } else { $enable = '<i class="fa-solid fa-toggle-on" style="color:#00FF00"></i>'; $toggle=0; }
  echo '<li class="w3-bar">
          <div class="w3-bar-item" style="width:15px">'.$def_id.'</div>
          <div class="w3-bar-item" style="width:75px"><div class="w3-center"><a href="rufumleitung.php?toggle='.$toggle.'&id='.$def_id.'">'.$enable.'</a></div></div>
          <div class="w3-bar-item" style="width:200px">'.$result['NewNumber'].'</div>
          <div class="w3-bar-item" style="width:200px">'.$result['NewDeflectionToNumber'].'</div>
          <div class="w3-bar-item" style="width:200px">'.$type.'</div>
          <div class="w3-bar-item" style="width:150px">'.$def_account.'</div>
        </li>';

echo '</ul></div>';
}
include('footer.php');
?>
