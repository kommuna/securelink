<?php


class SecureLink {

	const FORMAT_UNKNOWN_VAR = 0x3F;
	const VERIFY_EXPIRED = 0x40;
	const VERIFY_BAD_SIGNATURE = 0x41;

	public $errno;

	public function __construct($linkTemplate, $signateeTemplate, $secret, $ttl=86400) {
		$this->linkTemplate=$linkTemplate;
		$this->signateeTemplate=$signateeTemplate;
		$this->secret=$secret;
		$this->ttl=$ttl;
		$this->signature=null;
		$this->signatureLine=null;
		$this->link=null;
	}

	public function generate($uri, $ip = '0.0.0.0', $mask = '0.0.0.0') {
		$this->uri = $uri;
		$this->ipl = ip2long($ip);
		$this->maskl = ip2long($mask);
		$this->expiry = time() + $this->ttl;
		$this->errno = 0;
		$this->signatureLine = preg_replace_callback('/{[A-Z0-9_]+}/', array($this, 'substitute'), $this->signateeTemplate);
		if ($this->errno) {
			return false;
		}
		$this->signature = md5($this->signatureLine, true);
		$this->link = preg_replace_callback('/{[A-Z0-9_]+}/', array($this, 'substitute'), $this->linkTemplate);
		if ($this->errno) {
			return false;
		}

		return true;
	}

	public function verify($signature, $expiry, $uri, $ip = '0.0.0.0', $mask = '0.0.0.0') {
		$this->uri = $uri;
		$this->ipl = ip2long($ip);
		$this->maskl = ip2long($mask);
		$this->expiry = $expiry;
		$this->errno = 0;
		$this->signatureLine = preg_replace_callback('/{[A-Z0-9_]+}/', array($this, 'substitute'), $this->signateeTemplate);
		if ($this->errno) {
			return false;
		}
		$this->signature = md5($this->signatureLine, true);
		if ($this->signature != $signature) {
			$this->errno = self::VERIFY_BAD_SIGNATURE;
			return false;
		}
		if ($expiry < time()) {
			$this->errno = self::VERIFY_EXPIRED;
			return false;
		}
		return true;
	}

	public function err() {
		switch ($this->errno) {
			case self::FORMAT_UNKNOWN_VAR:
				return "Wrong variable in template";
			case self::VERIFY_EXPIRED:
				return "Signature expired";
			case self::VERIFY_BAD_SIGNATURE:
				return "Signature incorrect";
		}
	}

	public function getSignature() {
		return $this->signature;
	}

	public function getLink() {
		return $this->link;
	}

	public function getExpiry() {
		return $this->expiry;
	}

	public function setLinkTemplate($linkTemplate) {
		$this->linkTemplate=$linkTemplate;
		$this->signature=null;
		$this->signatureLine=null;
		$this->link=null;
	}

	public function getLinkTemplate() {
		return $this->linkTemplate;
	}

	public function setSignateeTemplate($signateeTemplate) {
		$this->signateeTemplate=$signateeTemplate;
		$this->signature=null;
		$this->signatureLine=null;
		$this->link=null;
	}

	public function getSignateeTemplate() {
		return $this->signateeTemplate;
	}

	public function setSecret($secret) {
		$this->secret=$secret;
		$this->signature=null;
		$this->signatureLine=null;
		$this->link=null;
	}

	public function getSecret() {
		return $this->secret;
	}

	public function setTtl($ttl) {
		$this->ttl=$ttl;
		$this->signature=null;
		$this->signatureLine=null;
		$this->link=null;
	}

	public function getTtl() {
		return $this->ttl;
	}

	public function debugSignatureLine() {
		return $this->signatureLine;
	}

	public static function deBase64($str) {
		return base64_decode(strtr($str, '-_', '+/'));
	}

	private $linkTemplate;
	private $signateeTemplate;
	private $secret;
	private $expiry;
	private $ttl;
	private $link;
	private $signature;
	private $signatureLine;
	private $ipl;
	private $maskl;
	private $uri;

	private function substitute($matches) {
		switch ($matches[0]) {
				case '{IP}':
					return $this->ipl;
				case '{IPS}':
					return long2ip($this->ipl);
				case '{IPMASKED}':
					return $this->ipl & $this->maskl;
				case '{IPSMASKED}':
					return long2ip($this->ipl & $this->maskl);
				case '{MASK}':
					return $this->maskl;
				case '{MASKS}':
					return long2ip($this->maskl);
				case '{EXP}':
					return $this->expiry;
				case '{SECRET}':
					return $this->secret;
				case '{URI}':
					return $this->uri;
				case '{SIGNBASE64}':
					return str_replace('=', '', strtr(base64_encode($this->signature), '+/', '-_'));
				default:
					$this->errno = self::FORMAT_UNKNOWN_VAR;
					return '';
			}

	} 


}
