<!DOCTYPE html>
<html lang="de">
<head>
<title>CalloraCTI</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="all" />
<link rel="stylesheet" href="css/w3.css" />
<link rel="stylesheet" href="css/fonts.css" />
<link rel="stylesheet" href="css/fontawesome.css" />
<link rel="stylesheet" href="css/brands.css" />
<link rel="stylesheet" href="css/solid.css" />
<link rel="shortcut icon" href="img/favicon.ico" />
<link rel="icon" type="image/png" href="img/favicon.png" sizes="32x32" />
<link rel="icon" type="image/png" href="img/favicon.png" sizes="96x96" />
<script src="js/jquery.min.js" type="text/javascript"></script>
<script>
  function executePHP(command) {
  $.get("call.php?nst=<?php echo $_SESSION['nst']; ?>", { command: command }, function(command){}, "json");
  }
  function hangup(nst) {
  $.get("hangup.php", { nst: nst }, function(nst){}, "json");
  }
  function answer(nst) {
  $.get("answer.php", { nst: nst }, function(nst){}, "json");
  }
  function pickup(nst) {
  $.get("pickup.php", { nst: nst }, function(nst){}, "json");
  }
$(document).ready(function() {
  setInterval(showUser, 500);
});
function showUser(str) {
  if (str == "") {
    document.getElementById("txtHint").innerHTML = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("txtHint").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET","monitor.php?nst=<?php echo $_SESSION['nst']; ?>",true);
    xmlhttp.send();
  }
}
function showAudio(str) {
  if (str == "") {
    document.getElementById("txtAudio").innerHTML = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("txtAudio").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET","play.php?ab=<?php echo us('fb_ab'); ?>&p="+str,true);
    xmlhttp.send();
  }
}
function webcam() {
  window.open('webcam.php', 'camwindow', 'innerWidth=1300,innerHeight=760,status:no,locatoin:no,menubar:no,resizable:no,status:no,toolbar:no');
}
</script>
</head>

