<?php
include('config.php');
include('funktionen.php');
if(isset($_GET['action'])) { 
  $action = $_GET['action'];
  $nst = $_GET['nst'];

  $usr_query = mysqli_query($db_conn,"SELECT * FROM usr_einstellungen join adm_benutzer where adm_benutzer.id = usr_einstellungen.user_id and adm_benutzer.nst = '$nst'");
  $usr_array = mysqli_fetch_array($usr_query);
  $fb_ab = $usr_array['fb_ab'];
  $fb_ports = $usr_array['fb_ports'];
  $fb_book = $usr_array['fb_book'];
  $fb_deflection = $usr_array['fb_deflection'];

  if(isset($_GET['regstate'])) { $regstate = $_GET['regstate']; }
  if(isset($_GET['regcode'])) { $regcode = $_GET['regcode']; }
  if(isset($_GET['ip'])) { $ip = $_GET['ip']; }
  if(isset($_GET['remotenumber'])) { $remotenumber = $_GET['remotenumber']; $intern = substr($remotenumber, 2); }
  if((isset($_GET['remotenumber'])) AND (str_contains($remotenumber, '@'))) {
    //für snom, senden nummer@realm
    $remotenumber = explode('@', $remotenumber);
    $remotenumber = $remotenumber[0];
  }
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
  $array = mysqli_fetch_array($query);
  $username = $array['username'];
  $registrar = $array['registrar'];
  if(isset($_GET['duration'])) { $duration = $_GET['duration']; }
  if(isset($_GET['linestate'])) { $linestate = $_GET['linestate']; }
  switch($action) {
    case 'regevent':
      $regevent = mysqli_query($db_conn,"SELECT * FROM regevent WHERE nst='$nst'");
      if($rowcount = mysqli_num_rows($regevent) == 0) {
        mysqli_query($db_conn,"INSERT INTO regevent (nst, regstate, regcode, ip) VALUES ('$nst', '$regstate', '$regcode', '$ip')");
      } else {
        mysqli_query($db_conn,"UPDATE regevent SET regstate='$regstate',regcode='$regcode',ip='$ip' WHERE nst = '$nst'");
      }
      $callstate = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$nst'");
      if($rowcount = mysqli_num_rows($callstate) == 0) {
        mysqli_query($db_conn,"INSERT INTO callstate (nst, state, remotenumber) VALUES ('$nst', 'IDLE', '0')");
      } else {
        mysqli_query($db_conn,"UPDATE callstate SET state='IDLE',remotenumber='0' WHERE nst = '$nst'");
      }
      $search_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE ziel='$nst'");
      while($row = mysqli_fetch_array($search_keys)) {
        $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
	      $ziel = $row['nst'];
	      push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
      }
      $query_own_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND type='blf'");
      while($own_keys = mysqli_fetch_array($query_own_keys)) {
	      $ziel = $own_keys['ziel'];
	      $query_state = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$ziel'");
	      $state = mysqli_fetch_array($query_state);
	      switch($state['state']) {
	        case 'IDLE':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=off:green"/></AastraIPPhoneExecute>';
	          break;
	        case 'incoming':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=fastflash:yellow"/></AastraIPPhoneExecute>';
	          break;
	        case 'connected':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=on:red"/></AastraIPPhoneExecute>';
	          break;
	        case 'outgoing':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=on:red"/></AastraIPPhoneExecute>';
	          break;
	        case 'disconnected':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=off:green"/></AastraIPPhoneExecute>';
	          break;
	        case 'onhook':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=off:green"/></AastraIPPhoneExecute>';
	          break;
	        case 'offhook':
	          $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$own_keys['taste'].'=on:red"/></AastraIPPhoneExecute>';
	          break;
        }
	      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      }
      echo '<AastraIPPhoneExecute><ExecuteItem URI=""/></AastraIPPhoneExecute>';
      break;
    case 'call':
      $callstate = mysqli_query($db_conn,"SELECT state FROM callstate WHERE nst='$intern'");
      $state = mysqli_fetch_array($callstate);
      $ownstate = mysqli_query($db_conn,"SELECT state FROM callstate WHERE nst='$nst'");
      $ostate = mysqli_fetch_array($ownstate);
      if($state['state'] == 'incoming') {
        $ip = phone_ip($nst);
        $password = cell('admin_password_phone','adm_einstellungen',$nst);
        $url = "http://admin:$password@$ip/command.htm?number=*09";
      } elseif($ostate['state'] == 'connected') {
	      $xml = '<AastraIPPhoneExecute triggerDestroyOnExit="yes">
                <ExecuteItem URI="Key:Hold" interruptCall="yes" title="Test"/>
                <ExecuteItem URI="Key:Xfer" interruptCall="yes" title="Test"/>';
	      $keypress = str_split($remotenumber, 1);
        foreach($keypress as $key) {
          switch($key) {
 	          case 1:
              $char = 'KeyPad1';
              break;
 	          case 2:
              $char = 'KeyPad2';
              break;
            case 3:
	            $char = 'KeyPad3';
	            break;
	          case 4:
              $char = 'KeyPad4';
              break;
            case 5:
              $char = 'KeyPad5';
              break;
	          case 6:
              $char = 'KeyPad6';
              break;
            case 7:
              $char = 'KeyPad7';
              break;
            case 8:
              $char = 'KeyPad8';
              break;
	          case 9:
              $char = 'KeyPad9';
              break;
	          case 0:
              $char = 'KeyPad0';
              break;
	          case '*':
              $char = 'KeyPadStar';
              break;
	          case '#':
              $char = 'KeyPadPound';
              break;
	        }
	        $xml .= '<ExecuteItem URI="Key:'.$char.'" interruptCall="yes" title="Test"/>';
	      }
        //$xml .= '<ExecuteItem URI="Key:SoftKey8" interruptCall="yes" title="Test"/>';
        $xml .= '</AastraIPPhoneExecute>';
      } else {
        $ip = phone_ip($nst);
        $password = cell('admin_password_phone','adm_einstellungen',$nst);
        $url = "http://admin:$password@$ip/command.htm?number=$remotenumber";
      }
      file_get_contents($url);
      break;
    case 'voicemail':
      if(isset($_GET['del'])) {
        $s = (int)$_GET['del'];
        $delete = $x_tam->DeleteMessage(
          new SoapParam($fb_ab, 'NewIndex'),
          new SoapParam($s, 'NewMessageIndex')
          );
          echo '<SnomIPPhoneText><Title>Voicemail</Title>';
          echo '<Text>keine Nachrichten</Text';
          echo '</SnomIPPhoneText>';
          exit;
        }
      $result = $x_tam->GetMessageList(new SoapParam($fb_ab, 'NewIndex'));
      $xml = simplexml_load_file($result);
      $sid = explode('sid=',$result);
      $sid = explode('&', $sid[1]);
      $sid = $sid[0];
      if(isset($_GET['selection'])) {
        $s = $_GET['selection'];
        $s = (int)$s;
        $filename = sha1(rand());
        foreach($xml->Message as $message) {
          if($message->Index == $s) {
            // Port 49443 scheint keine Daten zu senden, daher fallback auf http
            $url = 'http://'.$cfg['fb']['host'].':49000'.$message->Path.'&sid='.$sid;
            set_time_limit(0);
            $fp = fopen ($cfg['cnf']['path'].'/web/tmp/' . $filename.'.tmp', 'w+');
            $ch = curl_init(str_replace(" ","%20",$url));
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            $ffmpeg_command = '/usr/bin/ffmpeg -i '.$cfg['cnf']['path'].'/web/tmp/'.$filename.'.tmp -acodec pcm_s16le -ar 8000 -ac 1 '.$cfg['cnf']['path'].'/web/tmp/'.$filename.'.wav';
            shell_exec($ffmpeg_command);
            echo '<SnomIPPhoneMenu speedselect="enter" scrollbar="no">';
            echo '<MenuItem name="Nachricht abspielen ('.$message->Number.')"><URL>phone://mb_nop#action_ifc:pui=play_wav,url=http://'.$cfg['cnf']['fqdn'].'/web/tmp/'.$filename.'.wav</URL></MenuItem>';
            echo '<MenuItem name="Rufnummer anrufen ('.$message->Number.')"><URL>phone://#numberdial='.$message->Number.'</URL></MenuItem>';
            echo '<MenuItem name="Nachricht löschen"><URL>http://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=voicemail&amp;del='.$message->Index.'</URL></MenuItem>';
            echo '</SnomIPPhoneMenu>';
            $status = $x_tam->MarkMessage(
              new SoapParam($fb_ab, 'NewIndex'),
              new SoapParam($s, 'NewMessageIndex'),
              new SoapParam(1, 'NewMarkedAsRead')
              );
          }
	      }
      } else {
        $SnomIPPhoneMenu = new SimpleXMLElement('<SnomIPPhoneMenu/>');
        $SnomIPPhoneMenu->addAttribute('speedselect','enter');
        $SnomIPPhoneMenu->addAttribute('scrollbar','yes');
        $Title = $SnomIPPhoneMenu->addChild('Title','Voicemail');
        if(count($xml->Message) > 0) {
          foreach($xml->Message as $message) {
            if($message->New == 1) { $icon_in = 1;} else { $icon_in = 2; }
            $MenuItem = $SnomIPPhoneMenu->addChild('MenuItem', null);
            $MenuItem->addAttribute('name', $message->Number.', '.$message->Date);
            $MenuItem->addChild('URL', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=voicemail&amp;selection='.$message->Index);
          }
        }
        echo $SnomIPPhoneMenu->asXML();
      }
      break;
    case 'anrufliste':
      if (isset($_GET['l'])) {  
        $l = $_GET['l'];
        switch ($l) {
 	        case 'e':
	          $type = 1;
	          $title = 'ankommende Anrufe';
	          break;
	        case 'a':
            $type = 3;
            $title = 'ausgehende Anrufe';
            break;
	        case 'v':
            $type = 2;
            $title = 'verpasste Anrufe';
            break;
	      }
        $result = $x_contact->GetCallList();
        $xml = simplexml_load_file($result);
        $SnomIPPhoneMenu = new SimpleXMLElement('<SnomIPPhoneMenu/>');
        $SnomIPPhoneMenu->addAttribute('has_scrollbar','yes');
        $SnomIPPhoneMenu->addAttribute('speedselect','enter');
        $Title = $SnomIPPhoneMenu->addChild('Title',$title);
        foreach ($xml->Call as $call) {
          if(in_array($call->Port, json_decode($fb_ports, true))) {
            if($call->Type == $type) {
              switch ($type) {
	              case 1:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
                  if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $SnomIPPhoneMenu->addChild('MenuItem', null);
                  $MenuItem->addAttribute('name', $name.$call->Caller.', '.$call->Date);
                  $MenuItem->addChild('URL', 'phone://mb_nop#numberdial='.$call->Caller);
                  break;
                case 3:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Called, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
	                if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $SnomIPPhoneMenu->addChild('MenuItem', null);
                  $MenuItem->addAttribute('name', $name.$call->Caller.', '.$call->Date);
                  $MenuItem->addChild('URL', 'phone://mb_nop#numberdial='.$call->Called);
	                break;
	              case 2:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
	                if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $SnomIPPhoneMenu->addChild('MenuItem', null);
                  $MenuItem->addAttribute('name', $name.$call->Caller.', '.$call->Date);
                  $MenuItem->addChild('URL', 'phone://mb_nop#numberdial='.$call->Caller);
	                break;
	            }
	          }
	        }
	      }
        echo $SnomIPPhoneMenu->asXML();
      } else {
        $SnomIPPhoneMenu = new SimpleXMLElement('<SnomIPPhoneMenu/>');
        $SnomIPPhoneMenu->addAttribute('has_scrollbar','no');
        $SnomIPPhoneMenu->addAttribute('speedselect','enter');
        $Title = $SnomIPPhoneMenu->addChild('Title','Anruflisten');
        $e = $SnomIPPhoneMenu->addChild('MenuItem', null);
        $e->addAttribute('name','ankommende Anrufe');
        $e->addChild('URL', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=anrufliste&amp;l=e');
        $a = $SnomIPPhoneMenu->addChild('MenuItem', null);
        $a->addAttribute('name','ausgehende Anrufe');
        $a->addChild('URL', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=anrufliste&amp;l=a');
        $v = $SnomIPPhoneMenu->addChild('MenuItem', null);
        $v->addAttribute('name','verpasste Anrufe');
        $v->addChild('URL', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=anrufliste&amp;l=v');
        echo $SnomIPPhoneMenu->asXML();
      }
      break;
    case 'telefonbuch':
      $result = $x_contact->GetPhoneBook(new SoapParam($fb_book, 'NewPhoneBookID'));
      $xml = simplexml_load_file($result['NewPhonebookURL']);

      $tbook = new SimpleXMLElement('<tbook/>');
      $tbook->addAttribute('e', '2');
      $tbook->addAttribute('version', '2.0');
      foreach($xml->phonebook->contact as $entry) {
        if($entry->uniqueid >= 100) {
          $entrys[] = $entry;
        }
      }
      $sum = count($entrys);
      for($c=0;$c<$sum;$c++) {
        $contact = $tbook->addChild('contact', null);
        $contact->addAttribute('fav', 'false');
        $contact->addAttribute('vip', 'false');
        $contact->addAttribute('blocked', 'false');
        $contact->addChild('last_name', htmlspecialchars($entrys[$c]->person->realName));
        $numbers = $contact->addChild('numbers', null);
        if(count($entrys[$c]->telephony->number) == 1) {
          if(!is_numeric($entrys[$c]->telephony->number)) {
            $telnumber = preg_replace("/[^0-9]/", "", $entrys[$c]->telephony->number);
            $telnumber = '0'.substr($telnumber, 2);
          }
          foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($telnumber, $prefix)) { $no_type = 'mobile'; break; } else { $no_type = 'business'; } }
          $number = $numbers->addChild('number', null);
          $number->addAttribute('no',$telnumber);
          $number->addAttribute('type',$no_type);
          $number->addAttribute('outgoing_id','0');
          
        } else {
          foreach ($entrys[$c]->telephony->number as $telnumber) {
            
            if(!is_numeric($telnumber)) {
              $telnumber = preg_replace("/[^0-9]/", "", $telnumber);
              $telnumber = '0'.substr($telnumber, 2);
            }
            foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($telnumber, $prefix)) { $no_type = 'mobile'; break; } else { $no_type = 'business'; } }
            $number = $numbers->addChild('number', null);
            $number->addAttribute('no',$telnumber);
            $number->addAttribute('type',$no_type);
            $number->addAttribute('outgoing_id','0');
          }
        }
      }
      $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE displayname = '$nst'");
      $array = mysqli_fetch_array($query);
      extract($array, EXTR_OVERWRITE);
      $provfile = $cfg['cnf']['path'].'/prov/snom-tbook-'.$mac.'.xml';
      file_put_contents($provfile, $tbook->asXML());
      //echo $tbook->asXML();
      break;
    case 'webcam':
      $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
      $array = mysqli_fetch_array($query);
      $wc_query = mysqli_query($db_conn,"SELECT model FROM usr_telefon WHERE displayname = '$nst'");
      $wc_array = mysqli_fetch_array($wc_query);
      $model = $wc_array['model'];
      if(($model == 'D865') || ($model == 'D862')) {
        $wc_url = $array['wc_url_l'];
        $width = 800;
        $height = 372;
      } elseif(($model == 'D785') || ($model == 'D812') || ($model == 'D815')) {
        $wc_url = $array['wc_url_m'];
        $width = 480;
        $height = 192;
      } else {
        $wc_url = $array['wc_url_s'];
        $width = 320;
        $height = 184;
      }
      $img = file_get_contents($wc_url);
      $b64img = base64_encode($img);
      echo '<SnomIPPhoneImage>
              <LocationX>0</LocationX>
              <LocationY>0</LocationY>
              <Data encoding="base64>'.$b64img.'</Data>
            </SnomIPPhoneImage>';
      break;
    case 'services':
      if(isset($_GET['toggle'])) {
        $toggle = $_GET['toggle'];
      }
      if(isset($_GET['id'])) {
        $def_id = $_GET['id'];
      }
      if(isset($_GET['service'])) {
        $service = $_GET['service'];
        switch($service) {
          case 'am':
            $change = $x_tam->SetEnable(
                  new SoapParam($fb_ab, 'NewIndex'),
                  new SoapParam($toggle, 'NewEnable'));
            break;
          case 'def':
            $change = $x_contact->SetDeflectionEnable(
              new SoapParam($def_id, 'NewDeflectionId'),
              new SoapParam($toggle, 'NewEnable'));
            break;
        }
      }
      $services = 0;
      echo '<SnomIPPhoneMenu><Title icon="5">Services</Title>';
      if($fb_ab !='') {
        $am = $x_tam->GetInfo(new SoapParam($fb_ab, 'NewIndex'));
        $checksign = '';
        if($am['NewEnable'] == 1) { 
          $toggle = '0';
          $abstatus = '[ein]';
          if(isset($_GET['sipdect'])) { $checksign = '+'; }
        } else { 
          $toggle = '1';
          $abstatus = '[aus]';
          if(isset($_GET['sipdect'])) { $checksign = '-'; }
        }
        echo '<MenuItem name="Anrufbeantworter: '.$am["NewName"].' '.$abstatus.'"><URL>https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=services&amp;service=am&amp;toggle='.$toggle.'</URL></MenuItem>';
        $services = 1;
      }
      if($fb_deflection != '') {
        //foreach (json_decode($fb_deflection, true) as $deflection) {
          $result = $x_contact->GetDeflection(new SoapParam($fb_deflection, 'NewDeflectionId'));
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
          if($result['NewEnable'] == 1) { 
            $defstatus = '[aus]';
            $toggle = '0';
            if(isset($_GET['sipdect'])) { $checksign = '+'; }
          } else { 
            $devstatus = '[ein]';
            $toggle = '1';
            if(isset($_GET['sipdect'])) { $checksign = '-'; }
          }
          echo '<MenuItem name="'.$result["NewNumber"].' =&gt; '.$result["NewDeflectionToNumber"].' ['.$type.'] '.$defstatus.'"><URL>https://'.$cfg['cnf']['fqdn'].'/web/xml_snom.php?nst='.$nst.'&amp;action=services&amp;service=def&amp;id='.$deflection.'&amp;toggle='.$toggle.'</URL></MenuItem>';
          $services =1;
        //}
      }
      if($services == 0) {
        echo '<MenuItem name="keine Services" />';
      }
      echo '</SnomIPPhoneMenu>';
      break;
  }
}
?>
