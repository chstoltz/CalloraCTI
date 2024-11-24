<?php
  if ($_SESSION['level'] < 9)  {
    header('Location: anrufliste.php');
    exit;
  }
?>
