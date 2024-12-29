<?php

  $query = mysqli_query($db_conn,"SELECT * FROM adm_einstellungen");
  $array = mysqli_fetch_array($query);
  $domain = $array['ws_fqdn'];
  $ip = $array['ws_ip'];

  header('Content-Type: text/html; charset=utf-8');
   
  $lifetime = 86400;
  session_set_cookie_params($lifetime,"/; samesite=lax","",false,TRUE);
  session_start();
  $cookie_options = array (
    'expires' => time()+$lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'lax'
  );
  setcookie(session_name(),session_id(),$cookie_options);

?>
