<?php


class SecureLink {
/*******************************************************************************
 Allowed substitutes in $link and $signatee:
 {IP} -- ip address in ulong
 {IPS} -- ip address in octets e.g. 127.0.0.1
 {IPMASKED} -- ip address in ulong masked
 {IPSMASKED} -- ip address in octets masked
 {MASK} -- mask in ulong
 {MASKS} -- mask in octets e.g. 255.255.255.0
 {EXP} -- expiration time in epoch
 {SECRET} -- secret
 {SIGNBASE64} -- signature MD5 with base64 safe for web encoding (empty in $signatee)

*******************************************************************************/

	const FORMAT_UNKNOWN_VAR = 0x3F;
	const VERIFY_EXPIRED = 0x40;
	const VERIFY_BAD_SIGNATURE = 0x41;

	public static $errno;

	public static function generate(&$link, $signatee, $ip, $mask, $expiry, $secret) {
		$ipl = ip2long($ip);
		$maskl = ip2long($mask);
		$parseError = 0;
		$signature = '';

		$subst = function($matches) use (&$ipl, &$ip, &$maskl, &$mask, &$expiry, &$secret, &$signature, &$parseError) {
			switch ($matches[0]) {
				case '{IP}':
					return $ipl;
				case '{IPS}':
					return $ip;
				case '{IPMASKED}':
					return $ipl & $maskl;
				case '{IPSMASKED}':
					return long2ip($ipl & $maskl);
				case '{MASK}':
					return $maskl;
				case '{MASKS}':
					return $mask;
				case '{EXP}':
					return $expiry;
				case '{SECRET}':
					return $secret;
				case '{SIGNBASE64}':
					return str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));
				default:
					$parseError = self::FORMAT_UNKNOWN_VAR;
					return '';
			}
		};
		$signature = preg_replace_callback('/{[A-Z0-9_]+}/', $subst, $signatee);
		if ($parseError) {
			self::$errno = $parseError;
			return null;
		}
		$signature = md5($signature, true);
		$link = preg_replace_callback('/{[A-Z0-9_]+}/', $subst, $link);
		if ($parseError) {
			self::$errno = $parseError;
			return null;
		}

		return $signature;
	}

	public static function verify($signatee, $ip, $mask, $expiry, $secret, $signature) {
		$ipl = ip2long($ip);
		$maskl = ip2long($mask);
		$l = '';
		$s = self::generate($l, $signatee, $ip, $mask, $expiry, $secret);
		if (!$s) return false;
		if ($s != $signature) {
			self::$errno = self::VERIFY_BAD_SIGNATURE;
			return false;
		}
		if ($expiry < time()) {
			self::$errno = self::VERIFY_EXPIRED;
			return false;
		}
		return true;
	}

	public static function err() {
		switch (self::$errno) {
			case self::FORMAT_UNKNOWN_VAR:
				return "Wrong variable in template";
			case self::VERIFY_EXPIRED:
				return "Signature expired";
			case self::VERIFY_BAD_SIGNATURE:
				return "Signature incorrect";
		}
	}
}
