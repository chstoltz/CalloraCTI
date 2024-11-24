<?php

include('config.php');
include('funktionen.php');

parse_str(implode('&', array_slice($argv, 1)), $_GET);
extract($_GET, EXTR_OVERWRITE);

switch($var1) {
  case 'CALL':
    $dect = $var3+600;
    $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$dect'");
    if($rowcount = mysqli_num_rows($callstate) == 0) {
      mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$dect', 'outgoing', '$var5')");
    } else {
      mysqli_query($db_conn,"UPDATE callstate SET state='outgoing',remotenumber='$var5' WHERE nst = '$dect'");
    }
    mysqli_query($db_conn,"INSERT INTO callmonitor (datum, aktion, id, nst, lokal, remote, konto) VALUES ('$var0', '$var1', '$var2', '$var3', '$var4', '$var5', '$var6')");
    $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$dect'");
    while($row = mysqli_fetch_array($search_keys)) {
      $ziel = $row['nst'];
      $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
      $array = mysqli_fetch_array($query);
      $hersteller = $array['hersteller'];
      switch($hersteller) {
        case 'mitel':
          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
        case 'yealink':
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_RED=on"/></YealinkIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
        case 'snom':
          $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
          $array = mysqli_fetch_array($query);
          $admin_password_phone = $array['admin_password_phone'];
          $protocol = $array['protocol'];
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOn%26color%3DRED';
          file_get_contents($url);
          break;
      }
    }
    break;
  case 'CONNECT':
    $dect = $var3+600;
    $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$dect'");
    if($rowcount = mysqli_num_rows($callstate) == 0) {
      mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$dect', 'connected', '$var4')");
    } else {
      mysqli_query($db_conn,"UPDATE callstate SET state='connected',remotenumber='$var4' WHERE nst = '$dect'");
    }
    mysqli_query($db_conn,"UPDATE callmonitor SET datum='$var0',aktion='$var1',nst='$var3',remote='$var4' WHERE id='$var2'");
    $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$dect'");
    while($row = mysqli_fetch_array($search_keys)) {
      $ziel = $row['nst'];
      $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
      $array = mysqli_fetch_array($query);
      $hersteller = $array['hersteller'];
      switch($hersteller) {
        case 'mitel':
          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=on:red"/></AastraIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
        case 'yealink':
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_RED=on"/></YealinkIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
        case 'snom':
          $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
          $array = mysqli_fetch_array($query);
          $admin_password_phone = $array['admin_password_phone'];
          $protocol = $array['protocol'];
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOn%26color%3DRED';
          file_get_contents($url);
          break;
      }
    }
    break;
  case 'DISCONNECT':
    $query = mysqli_query($db_conn,"SELECT * FROM callmonitor WHERE id='$var2'");
    $array = mysqli_fetch_array($query);
    $dect = $array['nst']+600;
    $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$dect'");
    if($rowcount = mysqli_num_rows($callstate) == 0) {
      mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$dect', 'disconnected', '0')");
    } else {
      mysqli_query($db_conn,"UPDATE callstate SET state='disconnected',remotenumber='0' WHERE nst = '$dect'");
    }
    mysqli_query($db_conn,"DELETE FROM callmonitor WHERE id='$var2'");
    $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$dect'");
    while($row = mysqli_fetch_array($search_keys)) {
      $ziel = $row['nst'];
      $label = $row['label'];
      $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
      $array = mysqli_fetch_array($query);
      $hersteller = $array['hersteller'];
      switch($hersteller) {
        case 'mitel':
          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$label.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
        case 'yealink':
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
	        $xml='<YealinkIPPhoneConfiguration Beep="no"><Item>linekey.'.$keyno.'.label = '.$label.'</Item></YealinkIPPhoneConfiguration>';
	        push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
        case 'snom':
          $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
          $array = mysqli_fetch_array($query);
          $admin_password_phone = $array['admin_password_phone'];
          $protocol = $array['protocol'];
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOff';
          file_get_contents($url);
          break;
      }
    }
    break;
  case 'RING':
    mysqli_query($db_conn,"INSERT INTO callmonitor (datum, aktion, id, lokal, remote, konto) VALUES ('$var0', '$var1', '$var2', '$var4', '$var3', '$var5')");
    $dect = $var4+600;
    $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$dect'");
    while($row = mysqli_fetch_array($search_keys)) {
      $ziel = $row['nst'];
      $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
      $array = mysqli_fetch_array($query);
      $hersteller = $array['hersteller'];
      switch($hersteller) {
        case 'mitel':
          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=fastflash:yellow"/></AastraIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$var3.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          break;
          case 'yealink':
          $key = $row['taste'];
          $keyno = (int)substr($key, -1);
          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_ORANGE=fastflash"/></YealinkIPPhoneExecute>';
          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
          $xml='<YealinkIPPhoneConfiguration Beep="no"><Item>linekey.'.$keyno.'.label = '.$var3.'</Item></YealinkIPPhoneConfiguration>';
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
    break;
  case 'RESET':
    $query = mysqli_query($db_conn,"SELECT * FROM callmonitor");
    if(mysqli_num_rows($query != 0)) {
      while($row = mysqli_fetch_array($query)) {
        $id = row['id'];
        mysqli_query($db_conn,"DELETE FROM callmonitor WHERE id = '$id'");
      }
    }
    $dectarray = array(610,611,612,613,614,615);
    foreach($dectarray as $dect) {
      $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$dect'");
      if($rowcount = mysqli_num_rows($callstate) != 0) {
        mysqli_query($db_conn,"UPDATE callstate SET state='disconnected',remotenumber='0' WHERE nst = '$dect'");
      }
      $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$dect'");
      while($row = mysqli_fetch_array($search_keys)) {
        $ziel = $row['nst'];
        $label = $row['label'];
        $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
        $array = mysqli_fetch_array($query);
        $hersteller = $array['hersteller'];
        switch($hersteller) {
          case 'mitel':
            $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
            push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            $xml='<AastraIPPhoneConfiguration setType="remote"><ConfigurationItem><Parameter>'.$row['taste'].' label</Parameter><Value>'.$label.'</Value></ConfigurationItem></AastraIPPhoneConfiguration>';
            push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            break;
          case 'yealink':
            $key = $row['taste'];
            $keyno = (int)substr($key, -1);
            $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
            push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            $xml='<YealinkIPPhoneConfiguration Beep="no"><Item>linekey.'.$keyno.'.label = '.$label.'</Item></YealinkIPPhoneConfiguration>';
	          push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            break;
          case 'snom':
            $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen WHERE id='0'");
            $array = mysqli_fetch_array($query);
            $admin_password_phone = $array['admin_password_phone'];
            $protocol = $array['protocol'];
            $key = $row['taste'];
            $keyno = (int)substr($key, -1);
            $url = $protocol.'://admin:'.$admin_password_phone.'@'.phone_ip($ziel).'/minibrowser.htm?url='.$protocol.'://'.$cfg['cnf']['fqdn'].'%2Fweb%2Fsnom_remote.php%3Fled%3D'.$keyno.'%26value%3DOff';
            file_get_contents($url);
            break;
        }
      }
    }
    break;
}

?>
