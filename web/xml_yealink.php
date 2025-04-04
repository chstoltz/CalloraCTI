<?php
include('config.php');
include('funktionen.php');

if(isset($_GET['action'])) { 
  $action = $_GET['action'];
  $mac = $_GET['mac'];
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon WHERE mac = '$mac'");
  $array = mysqli_fetch_array($query);
  $nst = $array['nst'];

  $usr_query = mysqli_query($db_conn,"SELECT * FROM usr_einstellungen join adm_benutzer where adm_benutzer.id = usr_einstellungen.user_id and adm_benutzer.nst = '$nst'");
  $usr_array = mysqli_fetch_array($usr_query);
  $fb_ab = $usr_array['fb_ab'];
  $fb_ports = $usr_array['fb_ports'];
  $fb_book = $usr_array['fb_book'];
  $fb_deflection = $usr_array['fb_deflection'];

  if(isset($_GET['regstate'])) { $regstate = $_GET['regstate']; }
  if(isset($_GET['regcode'])) { $regcode = $_GET['regcode']; }
  if(isset($_GET['ip'])) { $ip = $_GET['ip']; }

  if(isset($_GET['remotenumber'])) {
    $remotenumber = explode('@', substr($remotenumber,4));
    $remotenumber = $remotenumber[0];
    $intern = substr($remotenumber, 2);
  }
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
        $ziel = $row['nst'];
        $query = mysqli_query($db_conn,"SELECT usr_telefon.model,model.hersteller FROM usr_telefon JOIN model WHERE usr_telefon.model=model.model and usr_telefon.nst = '$ziel'");
        $array = mysqli_fetch_array($query);
        $hersteller = $array['hersteller'];
        switch($hersteller) {
          case 'mitel': 
            $xml='<AastraIPPhoneExecute><ExecuteItem URI="Led: '.$row['taste'].'=off:green"/></AastraIPPhoneExecute>';
            push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            break;
          case 'yealink':
            $key = $row['taste'];
            $keyno = (int)substr($key, -1);
            $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$keyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
            push2phone($cfg['cnf']['ip'],phone_ip($ziel),$xml);
            break;
          case 'snom':
            break;
        }
      }
      $query_own_keys = mysqli_query($db_conn,"SELECT * FROM tasten WHERE nst='$nst' AND type='blf'");
      while($own_keys = mysqli_fetch_array($query_own_keys)) {
	      $ziel = $own_keys['ziel'];
	      $query_state = mysqli_query($db_conn,"SELECT * FROM callstate WHERE nst='$ziel'");
	      $state = mysqli_fetch_array($query_state);
        $ownkey = $own_keys['taste'];
        $ownkeyno = (int)substr($ownkey, -1);
	      switch($state['state']) {
	        case 'IDLE':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
	          break;
	        case 'incoming':
	          $xml='<YealinkIPPhoneExecute Beep="no"<ExecuteItem URI="Led:LINE'.$ownkeyno.'_ORANGE=fastflash"/></YealinkIPPhoneExecute>';
	          break;
	        case 'connected':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_RED=on"/></YealinkIPPhoneExecute>';
	          break;
	        case 'outgoing':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_RED=on"/></YealinkIPPhoneExecute>';
	          break;
	        case 'disconnected':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
	          break;
	        case 'onhook':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_GREEN=off"/></YealinkIPPhoneExecute>';
	          break;
	        case 'offhook':
	          $xml='<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Led:LINE'.$ownkeyno.'_RED=on"/></YealinkIPPhoneExecute>';
	          break;
        }
	      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      }
      echo '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI=""/></YealinkIPPhoneExecute>';
      break;
    case 'call':
      $callstate = mysqli_query($db_conn,"SELECT state FROM callstate WHERE nst='$intern'");
      $state = mysqli_fetch_array($callstate);
      $ownstate = mysqli_query($db_conn,"SELECT state FROM callstate WHERE nst='$nst'");
      $ostate = mysqli_fetch_array($ownstate);
      if($state['state'] == 'incoming') {
        $xml = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Dial:*09" interruptCall="yes" title="Test"/></YealinkIPPhoneExecute>';
      } elseif($ostate['state'] == 'connected') {
	      $xml = '<YealinkIPPhoneExecute Beep="no">
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
        $xml .= '</YealinkIPPhoneExecute>';
      } else {
        $xml = '<YealinkIPPhoneExecute Beep="no"><ExecuteItem URI="Dial:'.$remotenumber.'" interruptCall="yes" title="Test"/></YealinkIPPhoneExecute>';
      }
      push2phone($cfg['cnf']['ip'],phone_ip($nst),$xml);
      break;
    case 'voicemail':
      if(isset($_GET['del'])) {
	    $s = $_GET['selection'];
        $s = (int)$s;
        $delete = $x_tam->DeleteMessage(
          new SoapParam($fb_ab, 'NewIndex'),
          new SoapParam($s, 'NewMessageIndex')
        );
        echo '<YealinkIPPhoneTextMenu Beep="no"><Title>Voicemail</Title>';
        echo '<MenuItem><Prompt>keine Nachrichten</Prompt><URI>Sofkey:Exit</URI></MenuItem>';
        echo '<SoftKey index="3"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>';
        echo '</YealinkIPPhoneTextMenu>';
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
            $ffmpeg_command = '/usr/bin/ffmpeg -i '.$cfg['cnf']['path'].'/web/tmp/'.$filename.'.tmp -codec:a pcm_alaw -ar 8000 -ac 1 '.$cfg['cnf']['path'].'/web/tmp/'.$filename.'.wav';
            shell_exec($ffmpeg_command);
            echo '<YealinkIPPhoneExecute Beep="no">';
            echo '<ExecuteItem URI="Wav.Play:https://'.$cfg['cnf']['fqdn'].'/web/tmp/'.$filename.'.wav"/>';
            echo '</YealinkIPPhoneExecute>';
            $status = $x_tam->MarkMessage(
              new SoapParam($fb_ab, 'NewIndex'),
              new SoapParam($s, 'NewMessageIndex'),
              new SoapParam(1, 'NewMarkedAsRead')
              );
          }
	      }
      } else {
        echo '<YealinkIPPhoneTextMenu Beep="no"><Title>Voicemail</Title>';
        if(count($xml->Message) > 0) {
          foreach($xml->Message as $message) {
            if($message->New == 1) { $icon_in = 1;} else { $icon_in = 2; }
            //echo '<MenuItem icon="'.$icon_in.'"><Prompt>'.$message->Number.', '.$message->Date.'</Prompt><URI>SoftKey:Dial2</URI><Dial>'.$message->Number.'</Dial><Selection>'.$message->Index.'</Selection></MenuItem>';
            echo '<MenuItem icon="'.$icon_in.'"><Prompt>'.$message->Number.', '.$message->Date.'</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=voicemail</URI><Dial>'.$message->Number.'</Dial><Selection>'.$message->Index.'</Selection></MenuItem>';
            
	        }
          echo '<IconList><Icon index="1">Icon:Envelope</Icon>';
          if(isset($_GET['sipdect'])) {
            echo '<Icon index="2">Icon:EnvelopeOpened</Icon></IconList>';
          } else {
            echo '<Icon index="2">Icon:EnvelopeOpen</Icon></IconList>';
          }
          echo '<SoftKey index="1"><Label>Abspielen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=voicemail</URI></SoftKey>
                <SoftKey index="2"><Label>Löschen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=voicemail&amp;del=1</URI></SoftKey>
                <SoftKey index="3"><Label>Anrufen</Label><URI>SoftKey:Dial</URI></SoftKey>';
	      } else {
          echo '<MenuItem><Prompt>keine Nachrichten</Prompt><URI>Sofkey:Exit</URI></MenuItem>';
	      }
        echo '<SoftKey index="4"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey></YealinkIPPhoneTextMenu>';
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
        $YealinkIPPhoneTextMenu = new SimpleXMLElement('<YealinkIPPhoneTextMenu/>');
        $YealinkIPPhoneTextMenu->addAttribute('Beep', 'no');
        $TopTitle = $YealinkIPPhoneTextMenu->addChild('Title',$title);
        foreach ($xml->Call as $call) {
          if(in_array($call->Port, json_decode($fb_ports, true))) {
            if($call->Type == $type) {
              switch ($type) {
	              case 1:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
                  if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
                  $Prompt = $MenuItem->addChild('Prompt', $name.$call->Caller.', '.$call->Date);
                  //$URI = $MenuItem->addChild('URI', 'Dial:'.$call->Caller);
                  $Dial = $MenuItem->addChild('Dial', $call->Caller);
                  break;
                case 3:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Called, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
	                if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
                  $Prompt = $MenuItem->addChild('Prompt', $name.$call->Called.', '.$call->Date);
                  //$URI = $MenuItem->addChild('URI', 'Dial:'.$call->Called);
                  $Dial = $MenuItem->addChild('Dial', $call->Called);
	                break;
	              case 2:
                  foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($call->Caller, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
	                if($call->Name != '') { $name = htmlspecialchars($call->Name). ', '; } else { $name = '';}
                  $MenuItem = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
                  $Prompt = $MenuItem->addChild('Prompt', $name.$call->Caller.', '.$call->Date);
                  //$URI = $MenuItem->addChild('URI', 'Dial:'.$call->Caller);
                  $Dial = $MenuItem->addChild('Dial', $call->Caller);
	                break;
	            }
	          }
	        }
	      }
        $SoftKey1 = $YealinkIPPhoneTextMenu->addChild('SoftKey', null);
        $SoftKey1->addAttribute('index','1');
        $SoftKey1->addChild('Label','Anrufen');
        $SoftKey1->addChild('URI','SoftKey:Dial2');
        $SoftKey3 = $YealinkIPPhoneTextMenu->addChild('SoftKey', null);
        $SoftKey3->addAttribute('index','3');
        $SoftKey3->addChild('Label','Verlassen');
        $SoftKey3->addChild('URI','SoftKey:Exit');
        echo $YealinkIPPhoneTextMenu->asXML();
      } else {
        $YealinkIPPhoneTextMenu = new SimpleXMLElement('<YealinkIPPhoneTextMenu/>');
        $YealinkIPPhoneTextMenu->addAttribute('Beep', 'no');
        $TopTitle = $YealinkIPPhoneTextMenu->addChild('Title', 'Anruflisten');
        $MenuItem1 = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
        $MenuItem1->addAttribute('icon', '1');
        $MenuItem1->addChild('Prompt','ankommende Anrufe');
        $MenuItem1->addChild('URI', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=anrufliste&amp;l=e');
        $MenuItem3 = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
        $MenuItem3->addAttribute('icon', '3');
        $MenuItem3->addChild('Prompt','ausgehende Anrufe');
        $MenuItem3->addChild('URI', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=anrufliste&amp;l=a');
        $MenuItem2 = $YealinkIPPhoneTextMenu->addChild('MenuItem', null);
        $MenuItem2->addAttribute('icon', '2');
        $MenuItem2->addChild('Prompt','verpasste Anrufe');
        $MenuItem2->addChild('URI', 'https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=anrufliste&amp;l=v');
        $IconList = $YealinkIPPhoneTextMenu->addChild('IconList', null);
        $Icon1 = $IconList->addChild('Icon','Icon:Incoming')->addAttribute('index','1');
        $Icon2 = $IconList->addChild('Icon','Icon:IncomingMissed')->addAttribute('index','2');
        $Icon3 = $IconList->addChild('Icon','Icon:Outgoing')->addAttribute('index','3');
        $SoftKey = $YealinkIPPhoneTextMenu->addChild('SoftKey', null);
        $SoftKey->addAttribute('index','3');
        $SoftKey->addChild('Label','Verlassen');
        $SoftKey->addChild('URI','SoftKey:Exit');
        echo $YealinkIPPhoneTextMenu->asXML();
      }
      break;
    case 'telefonbuch':
      $result = $x_contact->GetPhoneBook(new SoapParam($fb_book, 'NewPhoneBookID'));
      $xml = simplexml_load_file($result['NewPhonebookURL']);
      if(isset($_GET['suche']) AND ($_GET['suche'] == 1)) {
        $YealinkIPPhoneTextMenu = new SimpleXMLElement('<YealinkIPPhoneTextMenu/>');
        $YealinkIPPhoneTextMenu->addAttribute('Beep', 'no');
        $YealinkIPPhoneTextMenu->addChild('Title', 'Suche');
        $YealinkIPPhoneTextMenu->addChild('Prompt', 'Namen suchen');
        $YealinkIPPhoneTextMenu->addChild("URL", "https://{$cfg['cnf']['fqdn']}/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch");
        $YealinkIPPhoneTextMenu->addChild('Parameter', 'suche');
        $YealinkIPPhoneTextMenu->addChild('Default', NULL);
        $SoftKey1 = $YealinkIPPhoneTextMenu->addChild('SoftKey', NULL);
        $SoftKey1->addAttribute('index', '1');
        $SoftKey1->addChild('Label', 'Suchen');
        $SoftKey1->addChild('URI', 'SoftKey:Submit');
        $SoftKey2 = $YealinkIPPhoneTextMenu->addChild('SoftKey', NULL);
        $SoftKey2->addAttribute('index', '2');
        $SoftKey2->addChild('Label', NULL);
        $SoftKey2->addChild('URI', 'SoftKey:ChangeMode');
        $SoftKey3 = $YealinkIPPhoneTextMenu->addChild('SoftKey', NULL);
        $SoftKey3->addAttribute('index', '3');
        $SoftKey3->addChild('Label', 'Löschen');
        $SoftKey3->addChild('URI', 'SoftKey:BackSpace');
        $SoftKey4 = $YealinkIPPhoneTextMenu->addChild('SoftKey', NULL);
        $SoftKey4->addAttribute('index', '4');
        $SoftKey4->addChild('Label', 'Verlassen');
        $SoftKey4->addChild('URI', 'SoftKey:Exit');
        echo $YealinkIPPhoneTextMenu->asXML();
        
        /*echo '<YealinkIPPhoneTextMenu Beep="no">
              <Title>Suche</Title>
              <Prompt>Namen suchen</Prompt>
              <URL>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch</URL>
              <Parameter>suche</Parameter>
              <Default></Default>
              <SoftKey index="1"><Label>Suchen</Label><URI>SoftKey:Submit</URI></SoftKey>
              <SoftKey index="2"><Label></Label><URI>SoftKey:ChangeMode</URI></SoftKey>
              <SoftKey index="3"><Label>Löschen</Label><URI>SoftKey:BackSpace</URI></SoftKey>
              <SoftKey index="4"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>
              </YealinkIPPhoneTextMenu>';*/
        exit;
      }
      if(isset($_GET['suche'])) {
        echo '<YealinkIPPhoneTextMenu Beep="no"><Title>Ergebnis</Title>';
        $suche = $_GET['suche'];
        $c = 0;
        foreach($xml->phonebook->contact as $contact) {
          $result = strpos(strtolower($contact->person->realName), strtolower($suche));
          if($result !== false) {
            echo '<MenuItem><Prompt>'.$contact->person->realName.'</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;selection='.$contact->uniqueid.'</URI><Selection>'.$contact->uniqueid.'</Selection></MenuItem>';
            $c++;
	        }
	      }
        if($c == 0) {
          echo '<MenuItem><Prompt>kein Ergebnis</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch</URI></MenuItem>';
	      } else {
          echo '<SoftKey index="2"><Label>Anzeigen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch</URI></SoftKey>';
	      }
        echo '<SoftKey index="3"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>
              </YealinkIPPhoneTextMenu>';
        exit;
      }
      if(isset($_GET['selection']) AND ($_GET['limit'])) {
        $limit = $_GET['limit'];
        $limit = (int)$limit;
        echo '<YealinkIPPhoneTextMenu Beep="no"><Title>Telefonbuch</Title>';
        foreach($xml->phonebook->contact as $contact) {
          if($contact->uniqueid >= 100) { // ich gehe hier davon aus, dass es sich um ein CardDav Adressbuch handelt und das interne Telefonbuch (Nebenstellen, Anrufbeantworter, etc.) in den ersten 100 IDs zu finden sind
            $contacts[] = $contact;
	        }
	      }
        for($c=$limit; $c<$limit+30; $c++) {
          if(!is_array($contacts[$c]->telephony->number)) {
            $number = $contacts[$c]->telephony->number;
	        } else {
            $number = $contacts[$c]->telephony->number[0];
	        }
          echo '<MenuItem><Prompt>'.htmlspecialchars($contacts[$c]->person->realName).'</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;selection='.$contacts[$c]->uniqueid.'</URI><Dial>'.$number.'</Dial><Selection>'.$contacts[$c]->uniqueid.'</Selection></MenuItem>';
        }
        echo '<SoftKey index="1"><Label>Anrufen</Label><URI>SoftKey:Dial2</URI></SoftKey>
              <SoftKey index="2"><Label>Anzeigen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch</URI></SoftKey>
	            <SoftKey index="3"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>';
        if($limit == 0) {
          echo '<SoftKey index="4"><Label>Suchen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;suche=1</URI></SoftKey>';
        } else {
          echo '<SoftKey index="4"><Label>&lt; zurück</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;limit='.$limit-30 .'</URI></SoftKey>';
        }
        echo '<SoftKey index="5"><Label>Weiter &gt;</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;limit='.$limit+30 .'</URI></SoftKey>
              </YealinkIPPhoneTextMenu>';
      } elseif(isset($_GET['selection'])) {
        $id = $_GET['selection'];
        $id = (int)$id;
        foreach($xml->phonebook->contact as $contact) {
          if($contact->uniqueid == $id) {
            echo '<YealinkIPPhoneTextMenu Beep="no"><Title>'.$contact->person->realName.'</Title>';
            if(count($contact->telephony->number) == 1) {
              if(!is_numeric($contact->telephony->number)) {
                $phone = preg_replace("/[^0-9]/", "", $contact->telephony->number);
                $phone = '0'.substr($phone, 2);
              } else {
                $phone = $contact->telephony->number;
              }
              foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($phone, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
              echo '<MenuItem icon="'.$pr_icon.'"><Prompt>'.$contact->telephony->number.'</Prompt><URI>Dial:'.$phone.'</URI><Dial>'.$phone.'</Dial></MenuItem>';
            } else {
              foreach($contact->telephony->number as $number) {
                if(!is_numeric($number)) {
                  $phone = preg_replace("/[^0-9]/", "", $number);
                  $phone = '0'.substr($phone, 2);
                } else {
                  $phone = $contact->telephony->number;
                }
                foreach(json_decode($cfg['cnf']['cell_prefix'], true) as $prefix) { if(str_starts_with($phone, $prefix)) { $pr_icon = 1; break; } else { $pr_icon = 2; } }
                echo '<MenuItem icon="'.$pr_icon.'"><Prompt>'.$number.'</Prompt><URI>Dial:'.$phone.'</URI><Dial>'.$phone.'</Dial></MenuItem>';
              }
            }
          }
        }
        echo '<SoftKey index="1"><Label>Anrufen</Label><URI>SoftKey:Dial2</URI></SoftKey>
              <SoftKey index="3"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>
              <IconList>
                <Icon index="1">Icon:CellPhone</Icon>
                <Icon index="2">Icon:Home</Icon>
              </IconList>
              </YealinkIPPhoneTextMenu>';
      } else {
        if(isset($_GET['limit'])) {
          $limit = $_GET['limit'];
          $limit = (int)$limit;
        } else {
          $limit = 0;
        }
        echo '<YealinkIPPhoneTextMenu Beep="no"><Title>Telefonbuch</Title>';
        foreach($xml->phonebook->contact as $contact) {
          if($contact->uniqueid >= 100) {
            $contacts[] = $contact;
          }
        }
        for($c=$limit; $c<$limit+30; $c++) {
          if(!is_array($contacts[$c]->telephony->number)) {
            $number = $contacts[$c]->telephony->number;
          } else {
            $number = $contacts[$c]->telephony->number[0];
          }
          echo '<MenuItem><Prompt>'.htmlspecialchars($contacts[$c]->person->realName).'</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;selection='.$contacts[$c]->uniqueid.'</URI><Dial>'.$number.'</Dial><Selection>'.$contacts[$c]->uniqueid.'</Selection></MenuItem>';
	      }
        echo '<SoftKey index="1"><Label>Anrufen</Label><URI>SoftKey:Dial2</URI></SoftKey>
              <SoftKey index="2"><Label>Anzeigen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch</URI></SoftKey>
              <SoftKey index="3"><Label>Verlassen</Label><URI>SoftKey:Exit</URI></SoftKey>';
        if($limit == 0) {
          echo '<SoftKey index="4"><Label>Suchen</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;suche=1</URI></SoftKey>';
	      } else {
          echo '<SoftKey index="4"><Label>&lt; zurück</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;limit='.$limit-30 .'</URI></SoftKey>';
	      }
        echo '<SoftKey index="5"><Label>Weiter &gt;</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?mac=$mac&amp;action=telefonbuch&amp;limit='.$limit+30 .'</URI></SoftKey>
              </YealinkIPPhoneTextMenu>';
      }
      break;
    case 'webcam':
      $query = mysqli_query($db_conn,"SELECT * FROM usr_tuerklingel");
      $array = mysqli_fetch_array($query);
      $wc_query = mysqli_query($db_conn,"SELECT model FROM usr_telefon WHERE displayname = '$nst'");
      $wc_array = mysqli_fetch_array($wc_query);
      $model = $wc_array['model'];
      if(($model == 'T57W') || ($model == 'T48U')) {
        $wc_url = $array['wc_url_l'];
        $width = 800;
        $height = 372;
      } elseif(($model == 'T54W') || ($model == 'T46U')) {
        $wc_url = $array['wc_url_m'];
        $width = 480;
        $height = 192;
      } else {
        $wc_url = $array['wc_url_s'];
        $width = 320;
        $height = 184;
      }
      echo '<YealinkIPPhoneImageScreen Beep="no">
            <Title icon="1" Color="yellow">Türklingel</Title>
	          <IconList>
	          <Icon index="1">Icon:Home</Icon>
	          </IconList>
	          <Image height="'.$height.'" width="'.$width.'">'.$wc_url.'</Image>
            <SoftKey index="1"><Label>Beenden</Label><URI>SoftKey:Exit</URI></SoftKey>
            <SoftKey index="2"><Label>Neu laden</Label><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?nst=$$DISPLAYNAME$$&amp;action=webcam</URI></SoftKey>
            </YealinkIPPhoneImageScreen>';
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
      echo '<YealinkIPPhoneTextMenu Beep="no"><Title icon="5">Services</Title>';
      if($fb_ab !='') {
        $am = $x_tam->GetInfo(new SoapParam($fb_ab, 'NewIndex'));
        $checksign = '';
        if($am['NewEnable'] == 1) { 
          $ab_icon = 1;
          $toggle = '0';
          if(isset($_GET['sipdect'])) { $checksign = '+'; }
        } else { 
          $ab_icon = 2;
          $toggle = '1';
          if(isset($_GET['sipdect'])) { $checksign = '-'; }
        }
        if(isset($_GET['sipdect'])) {
          $display_icon = '';
          $sipdect = '&sipdect=1';
        } else {
          $display_icon = 'icon="3"';
          $sipdect = '';
        }
        echo '<MenuItem '.$display_icon.' iconr1="'.$ab_icon.'"><Prompt>'.$checksign.' Anrufbeantworter: '.$am["NewName"].'</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?nst=$$DISPLAYNAME$$&amp;action=services&amp;service=am&amp;toggle='.$toggle.$sipdect.'</URI></MenuItem>';
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
            $def_icon = 1; 
            $toggle = '0';
            if(isset($_GET['sipdect'])) { $checksign = '+'; }
          } else { 
            $def_icon = 2; 
            $toggle = '1';
            if(isset($_GET['sipdect'])) { $checksign = '-'; }
          }
          if(isset($_GET['sipdect'])) {
            $display_icon = '';
            $sipdect = '&sipdect=1';
          } else {
            $display_icon = 'icon="4"';
            $sipdect = '';
          }
          echo '<MenuItem '.$display_icon.' iconr1="'.$def_icon.'"><Prompt>'.$checksign.$result["NewNumber"].' =&gt; '.$result["NewDeflectionToNumber"].' ['.$type.']</Prompt><URI>https://'.$cfg['cnf']['fqdn'].'/web/xml_yealink.php?nst=$$DISPLAYNAME$$&amp;action=services&amp;service=def&amp;id='.$deflection.'&amp;toggle='.$toggle.$sipdect.'</URI></MenuItem>';
          $services =1;
        //}
      }
      if($services == 0) {
        echo '<MenuItem><Prompt>keine Services</Prompt><URI></URI></MenuItem>';
      }
      echo '<IconList>
              <Icon index="1">Icon:CircleGreen</Icon>
              <Icon index="2">Icon:CircleRed</Icon>
              <Icon index="3">Icon:Envelope</Icon>
              <Icon index="4">Icon:CallFwd</Icon>
              <Icon index="5">Icon:Settings</Icon>
            </IconList>
            </YealinkIPPhoneTextMenu>';
      break;
  }
}
?>
