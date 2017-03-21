<?php
require "SecureLink.php";

$slink = new SecureLink('{URI}?st={SIGNBASE64}&e={EXP}&mask={MASK}&debug={IP}&a={IP}', '{SECRET}{URI}{EXP}{IPMASKED}{MASK}', 'Secr3t', 3600);

if (!$slink->generate('/pm/20170320/1489995043_465442_371700.flv.mp4', '127.0.0.1', '255.255.0.0')) {
	echo "Error: ".$slink->err()."\n";
	exit();
}
echo $slink->getLink()."\n";

$exp = $slink->getExpiry();
$signature = $slink->getSignature();

if ($slink->verify($signature, $exp, '/pm/20170320/1489995043_465442_371700.flv.mp4', '127.0.0.1', '255.255.0.0')) {
	echo "OKEY\n";
} else {
	echo "Error: ".$slink->err()."\n";
}
echo "\n";
