<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

echo '<div class="w3-bar w3-grey">
        <div class="w3-bar-item w3-padding-large">Leitungen</div>
      </div>';
$result = $x_voip->{'X_AVM-DE_GetVoIPAccounts'}();
$xml = simplexml_load_string($result);
echo '<div class="w3-row w3-container"><ul class="w3-ul">
        <li class="w3-bar">
          <div class="w3-bar-item" style="width:50px">ID</div>
          <div class="w3-bar-item" style="width:200px">Rufnummer</div>
          <div class="w3-bar-item" style="width:150px">Status</div>
        </li>';
foreach($xml->Account as $account) {
  switch($account->{'X_AVM-DE_VoIPStatus'}) {
    case 0:
      $account_status = '<i class="fa-solid fa-circle-minus" style="color: #ff0000;" title="Rufnummer deaktiviert"></i>';
      break;
    case 1:
      $account_status = '<i class="fa-solid fa-circle-xmark" style="color: #ff0000;" title="Rufnummer nicht registiert"></i>';
      break;
    case 2:
      $account_status = '<i class="fa-solid fa-circle" style="color: #00ff00;" title="Rufnummer registriert"></i>';
      break;
    case 3:
      $account_status = '<i class="fa-solid fa-circle-user" style="color: #00ff00;" title="Laufendes Gespräch"></i>';
      break;
    case 4:
      $account_status = '<i class="fa-solid fa-circle-question" style="color: #ff0000;" title="Status unbekannt"></i>';
      break;
  }
  echo '<li class="w3-bar">
          <div class="w3-bar-item" style="width:50px">'.$account->VoIPAccountIndex.'</div>
          <div class="w3-bar-item" style="width:200px">'.$account->VoIPNumber.'</div>
          <div class="w3-bar-item" style="width:150px">'.$account_status.'</div>
        </li>';       
}
echo '</ul></div>';
include('footer.php');
?>