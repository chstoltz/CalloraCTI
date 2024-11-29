<?php
include('config.php');
include('funktionen.php');

if(isset($_GET['nummer'])) {
  $nummer = $_GET['nummer'];
  $query = mysqli_query($db_conn,"SELECT * FROM usr_mobilteil WHERE sip_user = '$nummer'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];
} elseif(isset($_GET['mac'])) {
  $mac = $_GET['mac'];
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE mac = '$mac'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];
} else {
  $nst = $_GET['nst'];
}
if(isset($_GET['vendor'])) {
  $vendor = $_GET['vendor'];
}
switch($vendor) {
  case 'mitel':
    $remotenumber = $_GET['remotenumber']; 
    $endecho = '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
    break;
  case 'yealink':
    $remotenumber = $_GET['remotenumber']; 
    $remotenumber = explode('@', substr($remotenumber,4));
    $remotenumber = $remotenumber[0];
    $endecho = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI=""/></YealinkIPPhoneExecute>';
    break;
  case 'snom':
    $remotenumber = $_GET['remotenumber']; 
    $remotenumber = explode('@', $remotenumber);
    $remotenumber = $remotenumber[0];
    $endecho = '';
    break;
}
$intern = substr($remotenumber, 2);

$callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
if($rowcount = mysqli_num_rows($callstate) == 0) {
  mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'incoming', '$remotenumber')");
} else {
  mysqli_query($db_conn,"UPDATE callstate SET state='incoming',remotenumber='$remotenumber' WHERE nst = '$nst'");
}
$search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
while($row = mysqli_fetch_array($search_keys)) {
  $ziel = $row['nst'];
  $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
  $array = mysqli_fetch_array($query);
  $hersteller = $array['hersteller'];
  switch($hersteller) {
    case 'mitel':
      $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=fastflash:yellow"/></AastraIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      if(!str_starts_with($row['taste'], 'exp')) {
        $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$remotenumber.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
        push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      }
      break;
    case 'yealink':
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_ORANGE=fastflash"/></YealinkIPPhoneExecute>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      $xml='<YealinkIPPhoneConfiguration Beep="no"><Item>linekey.'.$keyno.'.label = '.$remotenumber.'</Item></YealinkIPPhoneConfiguration>';
      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      break;
    case 'snom':
      $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
      $array = mysqli_fetch_array($query);
      $admin_password_phone = $array['admin_password_phone'];
      $protocol = $array['protocol'];
      $key = $row['taste'];
      $keyno = (int)substr($key, -1);
      $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3Dblinkfast%26color%3Dorange';
      file_get_contents($url);
      break;
  }
}
$webcam_query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
if(mysqli_num_rows($webcam_query) == 1) {
  $webcam_array = mysqli_fetch_array($webcam_query);
  if($remotenumber == $webcam_array['wc_nst']) {
    $wc_query = mysqli_query($db_conn,"SELECT model FROM usr_telefon WHERE displayname = '$nst'");
    $wc_array = mysqli_fetch_array($wc_query);
    $model = $wc_array['model'];
    if(($model == '6940') || ($model == '6873') || ($model == 'T57W') || ($model == 'T48U') || ($model == 'D865') || ($model == 'D862')) {
      $wc_url = $webcam_array['wc_url_l'];
      $width = 800;
      $height = 372;
    } elseif(($model == '6930') || ($model == '6869') || ($model == 'T54W') || ($model == 'T46U') || ($model == 'D785') || ($model == 'D812') || ($model == 'D815')) {
      $wc_url = $webcam_array['wc_url_m'];
      $width = 480;
      $height = 192;
    } else {
      $wc_url = $webcam_array['wc_url_s'];
      $width = 320;
      $height = 184;
    }
    switch ($vendor) {
      case 'mitel':
        echo '<AastraIPPhoneImageScreen destroyOnExit="yes" Timeout="0">
          <TopTitle icon="1" Color="yellow">Türklingel</TopTitle>
          <IconList>
          <Icon index="1">Icon:Home</Icon>
          </IconList>
          <Image height="'.$height.'" width="'.$width.'">'.$wc_url.'</Image>
          <SoftKey index="1"><Label>Beenden</Label><URI>SoftKey:Exit</URI></SoftKey>
          <SoftKey index="2"><Label>Neu laden</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_mitel.php?nst=$$DISPLAYNAME$$&amp;action=webcam</URI></SoftKey>
          </AastraIPPhoneImageScreen>';
        break;
      case 'yealink':
        echo '<YealinkIPPhoneImageScreen Beep="no" Mode="fullscreen">
	          <Image height="'.$height.'" width="'.$width.'">'.$wc_url.'</Image>
            <SoftKey index="1"><Label>Beenden</Label><URI>SoftKey:Exit</URI></SoftKey>
            </YealinkIPPhoneImageScreen>';
        break;
      case 'snom':
        $img = file_get_contents($wc_url);
        $b64img = base64_encode($img);
        echo '<SnomIPPhoneImage>
                <LocationX>0</LocationX>
                <LocationY>0</LocationY>
                <Data encoding="base64">'.$b64img.'</Data>
              </SnomIPPhoneImage>';
        break;
    }
  }
} else {
  echo $endecho;
}
?>
