# securelink
Simple PHP class for create and validate "signed" HTTP links
##### Methods
`SecureLink::generate(&$link, $signatee, $ip, $mask, $expiry, $secret)`
* $link -- template for link, will be instantiated in place
* $signatee -- template which will be signed
* $ip -- ip address in octet form e.g. "127.0.0.1"
* $mask -- mask in octet form e.g. "255.255.0.0"
* $expiry -- when this signature is expired (epoch time in future)
* $secret -- secret to use to sign
Will return md5 signature (binary) based on instantiated $signatee or null on error populating SecureLink::$errno with corresponding error number.

`SecureLink::verify($signatee, $ip, $mask, $expiry, $secret, $signature)`
* $signatee -- template which will be signed
* $ip -- ip address in octet form e.g. "127.0.0.1"
* $mask -- mask in octet form e.g. "255.255.0.0"
* $expiry -- when this signature is expired (epoch time in future)
* $secret -- secret to use to sign
* $signature -- signature to compare with (binary)
Will return true or false on error populating SecureLink::$errno with corresponding error number.

`SecureLink::err()`
Will return a string explaining last error (contents of SecureLink::$errno)

`SecureLink::$errno`
Last error number

##### Allowed substitutes in $link and $signatee:
* {IP} -- ip address in ulong
* {IPS} -- ip address in octets e.g. 127.0.0.1
* {IPMASKED} -- ip address in ulong masked
* {IPSMASKED} -- ip address in octets masked
* {MASK} -- mask in ulong
* {MASKS} -- mask in octets e.g. 255.255.255.0
* {EXP} -- expiration time in epoch
* {SECRET} -- secret (don't use this in $link
* {SIGNBASE64} -- signature MD5 with base64 safe for web encoding (empty in $signatee)

