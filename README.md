# securelink
Simple PHP class for create and validate "signed" HTTP links
### Usage
#### Generation
First create an object

`$slink = new SecureLink(LINK_TEMPLATE, SIGN_TEMPLATE, SECRET, 3600);`

`LINK_TEMPLATE` here is a template which will be used to generate a link (duh), which can be used later in code. This is not strictly necessary, but makes life easier. E.g. `{URI}?st={SIGNBASE64}&e={EXP}&mask={MASK}&debug={IPMASKED}&a={IP}`

`SIGN_TEMPLATE` is a templates used to generate a line, which will be used for signature. An example of that like can be `{SECRET}{URI}{EXP}{IPMASKED}{MASK}`

`SECRET` is a secret word for signature. E.g. `Secr3t`

`3600` is a link validation time in seconds. In this case it's one hour. This parameter is optional and default value is 86400, i.e. 1 day.

Now it can be used to generate a link for a specific URI and IP address

`$slink->generate('/pm/20170320/1489995043_465442_371700.flv.mp4', $_SERVER['REMOTE_ADDR'], MASK)`

`/pm/20170320/1489995043_465442_371700.flv.mp4` is the URI to be signed. It doesn't contain scheme and domain because in this case (and most cases too) it's not necessary to sign those. However you can add it here if you want to.

`$_SERVER['REMOTE_ADDR']` is obviously clients ip address. The link will be valid only for this ip address (see MASK below).

`MASK` is used when client is prone to migrate to other ip address within his subnet. It can be set to something like `255.255.0.0` so that covers client's provider dynamic address pool.

This method returns true on success and false on error.

Now secure link can be used as

`echo "http://foobar.com".$slink->getLink()."\n";`

If you need only signature you can use

`$slink->getSignature();`

It will return signature as a binary string (raw, not base64)

All that will generate a link something like

`http://foobar.com/pm/20170320/1489995043_465442_371700.flv.mp4?st=77WmwiNzzQNPmazIR5qa4Q&e=1490113992&mask=4294901760&debug=2130706432&a=2130706696`

#### Verification
Again, need to create an object first

`$slink = new SecureLink(LINK_TEMPLATE, SIGN_TEMPLATE, SECRET);`

Here we don't really care about TTL, so we can omit it.

Now lets verify if client with `$_SERVER['REMOTE_ADDR']` address has a correct signature in `$_GET['st']` for a page URI in `$current_page_url`, expiration time in `$_GET['e']` and mask in `$_GET['mask']`:

`$slink->verify(SecureLink::deBase64($_GET['st']), $_GET['e'], $current_page_url, $_SERVER['REMOTE_ADDR'], long2ip($_GET['mask']))`

will return true on success and false on error.

##### Methods
`SecureLink::__construct($linkTemplate, $signateeTemplate, $secret, $ttl=86400)`
* $linkTemplate -- template for link
* $signateeTemplate -- template which will be signed
* $secret -- secret to use to sign
* $ttl -- number of seconds this link will be alive


`SecureLink::generate($uri, $ip = '0.0.0.0', $mask = '0.0.0.0')`
* $uri -- URI of the page to sign
* $ip -- ip address in octet form e.g. "127.0.0.1"
* $mask -- mask in octet form e.g. "255.255.0.0"

Will return true on success and false on error populating SecureLink::$errno with corresponding error number.


`SecureLink::getSignature()`

Will return signature in binary. Should be called after `generate()` or `verify()`

`SecureLink::getLink()`

Will return instantiated link. Should be called after `generate()`

`SecureLink::verify($signature, $expiry, $uri, $ip = '0.0.0.0', $mask = '0.0.0.0')`
* $signature -- signature to compare with (binary)
* $expiry -- when this signature is expired (epoch time in future)
* $uri -- URI of the page to sign
* $ip -- ip address in octet form e.g. "127.0.0.1"
* $mask -- mask in octet form e.g. "255.255.0.0"

Will return true or false on error populating SecureLink::$errno with corresponding error number.

`SecureLink::err()`

Will return a string explaining last error (contents of SecureLink::$errno)

`SecureLink::$errno`

Last error number

`SecureLink::debugSignatureLine()`

Will return line which was used to make a signature. Useful for debugging. Should be called after `generate()` or `verify()`

`static SecureLink::deBase64($str)`

Decodes base64 encoded string. It's very similar to base64_decode, however this class uses web-safe base64, which is a bit different.

The rest is self-explanatory:

`SecureLink::getExpiry()`
`SecureLink::setLinkTemplate($linkTemplate)`
`SecureLink::getLinkTemplate()`
`SecureLink::setSignateeTemplate($signateeTemplate)`
`SecureLink::getSignateeTemplate()`
`SecureLink::setSecret($secret)`
`SecureLink::getSecret()`
`SecureLink::setTtl($ttl)`
`SecureLink::getTtl()`

##### Allowed substitutes in $link and $signatee:
* {IP} -- ip address in ulong
* {IPS} -- ip address in octets e.g. 127.0.0.1
* {IPMASKED} -- ip address in ulong masked
* {IPSMASKED} -- ip address in octets masked
* {MASK} -- mask in ulong
* {MASKS} -- mask in octets e.g. 255.255.255.0
* {EXP} -- expiration time in epoch
* {SECRET} -- secret (don't use this in $link)
* {URI} -- uri to sign
* {SIGNBASE64} -- signature MD5 with base64 safe for web encoding (empty in $signatee)

### Examples

Examples can be found in exampleGenerateLink.php and exampleVerifyLink.php

```
$ php exampleGenerateLink.php 
http://foobar.com/pm/20170320/1489995043_465442_371700.flv.mp4?st=tgWJ5YEtkJG7SKd7u5MFBQ&e=1490115288&mask=4294901760&debug=2130706432&a=2130706696
$
$
$ php exampleVerifyLink.php 'http://foobar.com/pm/20170320/1489995043_465442_371700.flv.mp4?st=tgWJ5YEtkJG7SKd7u5MFBQ&e=1490115288&mask=4294901760&debug=2130706432&a=2130706696'
Verifying for 127.0.12.30
OKEY

Verifying for 127.1.12.30
Error: Signature incorrect
Debug: Secr3t/pm/20170320/1489995043_465442_371700.flv.mp4149011528821307719684294901760

$ 
```
