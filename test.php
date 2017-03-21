<?php
require "SecureLink.php";

$link = '/pm/20170320/1489995043_465442_371700.flv.mp4?st={SIGNBASE64}&e={EXP}&mask={MASK}&debug={IP}&a={IP}';
$exp = time()+3600;
$signature = SecureLink::generate($link, '{SECRET}/pm/20170320/1489995043_465442_371700.flv.mp4{EXP}{IPMASKED}{MASK}', '127.0.0.1', '255.255.0.0', $exp, 'Secr3t');
if (!$signature) {
	echo "Error: ".SecureLink::err()."\n";
	exit();
}
echo $link."\n";

if (SecureLink::verify('{SECRET}/pm/20170320/1489995043_465442_371700.flv.mp4{EXP}{IPMASKED}{MASK}', '127.0.0.1', '255.255.0.0', $exp, 'Secr3t', $signature)) {
	echo "OKEY\n";
} else {
	echo "Error: ".SecureLink::err()."\n";
}
echo "\n";
