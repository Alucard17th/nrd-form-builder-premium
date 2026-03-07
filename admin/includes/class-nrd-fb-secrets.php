<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nrd_FB_Secrets {
	private static function key() {
		return hash( 'sha256', AUTH_KEY . SECURE_AUTH_KEY, true );
	}
	public static function encrypt( string $plain ): string {
		$iv     = random_bytes( 16 );
		$cipher = openssl_encrypt( $plain, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv );
		return base64_encode( $iv . $cipher );
	}
	public static function decrypt( string $blob ): string {
		$raw = base64_decode( $blob, true );
		if ( $raw === false || strlen( $raw ) < 17 ) {
			return '';
		}
		$iv     = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		$plain  = openssl_decrypt( $cipher, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv );
		return $plain ?: '';
	}
}
