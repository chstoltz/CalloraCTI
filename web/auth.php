<?php

if (!isset($_SESSION['signon']) || !$_SESSION['signon']) {

  header('Location: login.php');
  exit;

}

?>


