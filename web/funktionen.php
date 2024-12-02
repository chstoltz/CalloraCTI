<?php

//=====================================================================//
// Dinge, die wir öfter brauchen in $vars schreiben.

$query = mysqli_query($db_conn,"SELECT * FROM adm_fritzbox");
if(mysqli_num_rows($query)==1) {
  $array = mysqli_fetch_array($query);

  $cfg['fb']['host'] = $array['fb_url'];
  $cfg['fb']['ip'] = $array['fb_ip'];
  $cfg['fb']['user'] = $array['fb_user'];
  $cfg['fb']['pass'] = $array['fb_pass'];
  
}

$query = mysqli_query($db_conn,"SELECT * FROM adm_sipdect");
if(mysqli_num_rows($query)==1) {
  $array = mysqli_fetch_array($query);

  $cfg['omm']['ip'] = $array['omm_ip'];
  $cfg['omm']['registrar'] = $array['registrar_ip'];
  $cfg['omm']['proxy'] = $array['proxy_ip'];
  $cfg['omm']['user'] = 'omm';
  $cfg['omm']['pass'] = $array['omm_password'];
  $cfg['omm']['root'] = $array['root_password'];
  $cfg['omm']['systemname'] = $array['system_name'];

}

$query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen");
if(mysqli_num_rows($query)==1) {
  $array = mysqli_fetch_array($query);

  $cfg['cnf']['fqdn'] = $array['ws_fqdn'];
  $cfg['cnf']['ip'] = $array['ws_ip'];
  $cfg['cnf']['path'] = $array['ws_path'];
  $cfg['cnf']['cell_prefix'] = $array['cell_prefix'];
  $cfg['cnf']['polling'] = $array['polling'];
  $cfg['cnf']['dectsystem'] = $array['dectsystem'];
  $cfg['cnf']['protocol'] = $array['protocol'];
  
}

//=====================================================================//

function push2phone($server,$phone,$data) {
  $xml = "xml=".$data;
  $post = "POST / HTTP/1.1\r\n";
  $post .= "Host: $phone\r\n";
  $post .= "Referer: $server\r\n";
  $post .= "Connection: Keep-Alive\r\n";
  $post .= "Content-Type: text/xml\r\n";
  $post .= "Content-Length: ".strlen($xml)."\r\n\r\n";
  $fp = @fsockopen ( $phone, 80, $errno, $errstr, 5);
  if($fp) {
    fputs($fp, $post.$xml);
    flush();
    fclose($fp);
  }
}

function phone_ip($nst) {
  global $db_conn;
  $query = mysqli_query($db_conn,"SELECT ip FROM regevent WHERE nst='$nst'");
  $array = mysqli_fetch_array($query);
  $ip = $array['ip'];
  return($ip);  
}

function db_cell($cell,$table,$nst) {
  global $db_conn;
  $query = mysqli_query($db_conn,"SELECT $cell FROM $table WHERE nst = '$nst'");
  if(mysqli_num_rows($query) == 1) {
    $array = mysqli_fetch_array($query);
    return($array[$cell]);
  } else {
    return('l_e_e_r');
  }
}

function cell($cell,$table) {
  global $db_conn;
  $query = mysqli_query($db_conn,"SELECT $cell FROM $table");
  if(mysqli_num_rows($query) == 1) {
    $array = mysqli_fetch_array($query);
    return($array[$cell]);
  } else {
    return('l_e_e_r');
  }
}

function us($cell) {
  global $db_conn;
  $user_id = $_SESSION['id'];
  $query = mysqli_query($db_conn,"SELECT $cell FROM usr_einstellungen WHERE user_id = '$user_id'");
  if(mysqli_num_rows($query) == 1) {
    $array = mysqli_fetch_array($query);
    return($array[$cell]);
  } else {
    return('l_e_e_r');
  }
}

function provision($nst) {
  global $cfg;
  global $db_conn;
  $query = mysqli_query($db_conn,"SELECT * FROM usr_telefon JOIN model WHERE usr_telefon.model = model.model AND usr_telefon.nst = '$nst'");
  $array = mysqli_fetch_array($query);
  extract($array,EXTR_OVERWRITE);
  switch($hersteller) {
    case 'snom':
      exec("php {$cfg['cnf']['path']}/web/prov_gen_snom.php nst=$nst & > /dev/null 2>&1");
      break;
    case 'mitel':
      exec("php {$cfg['cnf']['path']}/web/prov_gen_mitel.php nst=$nst & > /dev/null 2>&1");
      break;
    case 'yealink':
      exec("php {$cfg['cnf']['path']}/web/prov_gen_yealink.php nst=$nst & > /dev/null 2>&1");
      break;
  }
}

function sipnotify($nst) {
  global $cfg;
  exec("php {$cfg['cnf']['path']}/web/sipnotify.php nst=$nst reboot=false & > /dev/null 2>&1");
}

function reboot($nst) {
  global $cfg;
  exec("php {$cfg['cnf']['path']}/web/sipnotify.php nst=$nst reboot=true & > /dev/null 2>&1");
}

function axi_login($omm_ip,$omm_user,$omm_pass) {
  $login = "<Open protocolVersion=\"54\" username=\"$omm_user\" password=\"$omm_pass\" OMPClient=\"1\" />\0";
  $context = stream_context_create([
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    ]
  ]);
  $hostname = "tls://$omm_ip:12622";
  $s = stream_socket_client($hostname, $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);
  fwrite($s, $login);
  $answer = fread($s, 4096);
  return($answer);

}

function axi($omm_ip,$omm_user,$omm_pass,$command) {

  $login = "<Open protocolVersion=\"54\" username=\"$omm_user\" password=\"$omm_pass\" OMPClient=\"1\" />\0";
  $context = stream_context_create([
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    ]
  ]);
  $command = $command."\0";
  $hostname = "tls://$omm_ip:12622";
  $s = stream_socket_client($hostname, $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);
  fwrite($s, $login);
  fread($s, 4096);
  fwrite($s,$command);
  $answer = fread($s, 4096);
  return($answer);

}

function mitelheader() {
  $user_agent=$_SERVER["HTTP_USER_AGENT"];
  if(stristr($user_agent,"Aastra")) {
    $value=preg_split("/ MAC:/",$user_agent);
    $end=preg_split("/ /",$value[1]);
    $value[1]=preg_replace("/\-/","",$end[0]);
    $value[2]=preg_replace("/V:/","",$end[1]);
  } else {
    $value[0]="Unknown";
    $value[1]="NA";
    $value[2]="NA";
  }
    $mitel['model']=$value[0];
    $mitel['mac']=$value[1];
    $mitel['firmware']=$value[2];
    return($mitel);
}

function deleteCache($dir, $extensions = ['.tmp', '.wav']) {
  if(!is_dir($dir)) {
    echo "Das Verzeichnis existiert nicht.";
    return;
  }

  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
  );

  foreach($iterator as $file) {
    if($file->isFile()) {
      $filePath = $file->getRealPath();
      $fileExt = strtolower($file->getExtension());
      if(in_array('.' . $fileExt, $extensions)) {
        unlink($filePath);
      }
    }
  }
}

function cticlient($ip,$command) {
  
  $s = fsockopen($ip, 22222, $errno, $errstr, 10);
  if (!$s) {
      echo "Verbindung fehlgeschlagen: $errstr ($errno)\n";
  } else {
    fwrite($s, $command."\n");
    fclose($s);
  }
}

//=====================================================================//

$xmlcontext = stream_context_create(
  array('ssl'=>array(
    'verify_peer' => false,
    'verify_peer_name' => false,
  ))
);
libxml_set_streams_context($xmlcontext);

if($cfg['cnf']['protocol'] == 'https') {

$x_contact = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/x_contact",
    'uri'      => "urn:dslforum-org:service:X_AVM-DE_OnTel:1",
    'noroot'   => False,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);
  
$x_tam = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/x_tam",
    'uri'      => "urn:dslforum-org:service:X_AVM-DE_TAM:1",
    'noroot'   => False,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);

$x_voip = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/x_voip",
    'uri'      => "urn:dslforum-org:service:X_VoIP:1",
    'noroot'   => TRUE,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'trace'    => TRUE,
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);
  
$deviceconfig = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/deviceconfig",
    'uri'      => "urn:dslforum-org:service:DeviceConfig:1",
    'noroot'   => False,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);

$x_auth = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/x_auth",
    'uri'      => "urn:dslforum-org:service:X_AVM-DE_Auth:1",
    'noroot'   => False,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'trace'    => TRUE,
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);

$x_dect = new SoapClient(
  null,
  array(
    'location' => "https://{$cfg['fb']['host']}:49443/upnp/control/x_dect",
    'uri'      => "urn:dslforum-org:service:X_AVM-DE_Dect:1",
    'noroot'   => False,
    'login'    => $cfg['fb']['user'],
    'password' => $cfg['fb']['pass'],
    'trace'    => TRUE,
    'stream_context' => stream_context_create([
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        ],
    ]),
  )
);

} else {

  $x_contact = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/x_contact",
      'uri'      => "urn:dslforum-org:service:X_AVM-DE_OnTel:1",
      'noroot'   => False,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );
    
  $x_tam = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/x_tam",
      'uri'      => "urn:dslforum-org:service:X_AVM-DE_TAM:1",
      'noroot'   => False,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );
  
  $x_voip = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/x_voip",
      'uri'      => "urn:dslforum-org:service:X_VoIP:1",
      'noroot'   => TRUE,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );
    
  $deviceconfig = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/deviceconfig",
      'uri'      => "urn:dslforum-org:service:DeviceConfig:1",
      'noroot'   => False,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );
  
  $x_auth = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/x_auth",
      'uri'      => "urn:dslforum-org:service:X_AVM-DE_Auth:1",
      'noroot'   => False,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );
  
  $x_dect = new SoapClient(
    null,
    array(
      'location' => "http://{$cfg['fb']['host']}:49000/upnp/control/x_dect",
      'uri'      => "urn:dslforum-org:service:X_AVM-DE_Dect:1",
      'noroot'   => False,
      'login'    => $cfg['fb']['user'],
      'password' => $cfg['fb']['pass']
    )
  );

}

?>
