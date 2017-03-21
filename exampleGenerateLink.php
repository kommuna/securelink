<?php
require "SecureLink.php";
require "exampleCommon.php";

$slink = new SecureLink(LINK_TEMPLATE, SIGN_TEMPLATE, SECRET, 3600);


$SERVER['REMOTE_ADDR'] = '127.0.1.8';

if (!$slink->generate('/pm/20170320/1489995043_465442_371700.flv.mp4', $SERVER['REMOTE_ADDR'], MASK)) {
	echo "Error: ".$slink->err()."\n";
	exit();
}
echo "http://foobar.com".$slink->getLink()."\n";

