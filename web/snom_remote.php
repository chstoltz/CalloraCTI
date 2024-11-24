<?php

$ledno = $_GET['led'];
$value = $_GET['value'];
$color = @$_GET['color'];

$SnomIPPhoneText = new SimpleXMLElement('<SnomIPPhoneText/>');
$led = $SnomIPPhoneText->addChild('Led', $value);
$led->addAttribute('number', $ledno);
$led->addAttribute('color', $color);
$Fetch = $SnomIPPhoneText->addChild('Fetch', 'snom://mb_exit');
$Fetch->addAttribute('mil', '1');

echo $SnomIPPhoneText->asXML();

?>
