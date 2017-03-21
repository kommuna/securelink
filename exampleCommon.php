<?php

define('LINK_TEMPLATE', '{URI}?st={SIGNBASE64}&e={EXP}&mask={MASK}&debug={IPMASKED}&a={IP}');
define('SIGN_TEMPLATE', '{SECRET}{URI}{EXP}{IPMASKED}{MASK}');
define('SECRET', 'Secr3t');
define('MASK', '255.255.0.0');
