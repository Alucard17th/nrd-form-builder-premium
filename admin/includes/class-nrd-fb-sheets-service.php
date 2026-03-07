<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nrd_FB_GSA_Token {
	private $client_email;
	private $private_key;

	public function __construct( array $sa ) {
		$this->client_email = $sa['client_email'] ?? '';
		$this->private_key  = $sa['private_key'] ?? '';
	}
	public function fetch_access_token(): array {
		$now    = time();
		$header = array(
			'alg' => 'RS256',
			'typ' => 'JWT',
		);
		$claim  = array(
			'iss'   => $this->client_email,
			'scope' => 'https://www.googleapis.com/auth/spreadsheets',
			'aud'   => 'https://oauth2.googleapis.com/token',
			'exp'   => $now + 3600,
			'iat'   => $now,
		);
		$jwt    = $this->jwt( $header, $claim );
		if ( ! $jwt ) {
			return array( 'error' => 'jwt_sign_failed' );
		}

		$resp = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			array(
				'timeout' => 20,
				'body'    => array(
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'assertion'  => $jwt,
				),
			)
		);
		if ( is_wp_error( $resp ) ) {
			return array( 'error' => $resp->get_error_message() );
		}
		$code = wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		return ( $code === 200 && ! empty( $body['access_token'] ) ) ? $body : array( 'error' => $body['error_description'] ?? 'token_error' );
	}
	private function b64url( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}
	private function jwt( $header, $claim ) {
		$segments     = array( $this->b64url( json_encode( $header ) ), $this->b64url( json_encode( $claim ) ) );
		$signingInput = implode( '.', $segments );
		$ok           = openssl_sign( $signingInput, $sig, $this->private_key, 'sha256WithRSAEncryption' );
		if ( ! $ok ) {
			return null;
		}
		$segments[] = $this->b64url( $sig );
		return implode( '.', $segments );
	}
}

class Nrd_FB_Sheets_Service {
	const OPT_JSON_ENC  = 'nrd_fb_sa_json_enc';
	const OPT_SHEET_ID  = 'nrd_fb_default_sheet_id';
	const OPT_SHEET_TAB = 'nrd_fb_default_sheet_tab';

	private function load_sa(): array|string {
		$enc = get_option( self::OPT_JSON_ENC, '' );
		if ( ! $enc ) {
			return 'Missing Service Account JSON. Save it in settings.';
		}
		$plain = Nrd_FB_Secrets::decrypt( $enc );
		if ( ! $plain ) {
			return 'Failed to decrypt Service Account JSON.';
		}
		$sa = json_decode( $plain, true );
		if ( ! is_array( $sa ) || empty( $sa['client_email'] ) || empty( $sa['private_key'] ) ) {
			return 'Invalid Service Account JSON.';
		}
		return $sa;
	}
	private function get_token( array $sa ) {
		$cache_key = 'nrd_sa_token_' . md5( $sa['client_email'] );
		$tok       = get_transient( $cache_key );
		if ( $tok && ! empty( $tok['access_token'] ) ) {
			return $tok;
		}
		$tok = ( new Nrd_FB_GSA_Token( $sa ) )->fetch_access_token();
		if ( ! empty( $tok['error'] ) ) {
			return $tok;
		}
		set_transient( $cache_key, $tok, max( 60, (int) $tok['expires_in'] - 60 ) );
		return $tok;
	}

	/** Append a row, auto-creating headers if missing. */
	public function append_row( array $leadData, string $sheetId, string $sheetName, bool $ensure_headers = true ) {
		$sa = $this->load_sa();
		if ( is_string( $sa ) ) {
			return $sa;
		}

		$tok = $this->get_token( $sa );
		if ( ! empty( $tok['error'] ) ) {
			return 'Token error: ' . $tok['error'];
		}
		$bearer = 'Bearer ' . $tok['access_token'];

		// Flatten values
		$flat = array();
		foreach ( $leadData as $k => $v ) {
			$flat[ $k ] = is_scalar( $v ) ? (string) $v : wp_json_encode( $v );
		}
		$headers = array_keys( $flat );
		$values  = array_values( $flat );

		if ( $ensure_headers ) {
			$range      = rawurlencode( $sheetName . '!1:1' );
			$existing   = wp_remote_get(
				"https://sheets.googleapis.com/v4/spreadsheets/$sheetId/values/$range",
				array(
					'headers' => array( 'Authorization' => $bearer ),
					'timeout' => 20,
				)
			);
			$hasHeaders = false;
			if ( ! is_wp_error( $existing ) && wp_remote_retrieve_response_code( $existing ) === 200 ) {
				$body       = json_decode( wp_remote_retrieve_body( $existing ), true );
				$hasHeaders = ! empty( $body['values'][0] );
			}
			if ( ! $hasHeaders ) {
				$payload = array( 'values' => array( $headers ) );
				$resp    = wp_remote_post(
					"https://sheets.googleapis.com/v4/spreadsheets/$sheetId/values/" . rawurlencode( "$sheetName!A1" ) . ':append?valueInputOption=RAW',
					array(
						'headers' => array(
							'Authorization' => $bearer,
							'Content-Type'  => 'application/json',
						),
						'body'    => wp_json_encode( $payload ),
						'timeout' => 20,
					)
				);
				if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) >= 300 ) {
						return 'Failed to write headers.';
				}
			}
		}

		$payload = array( 'values' => array( $values ) );
		$resp    = wp_remote_post(
			"https://sheets.googleapis.com/v4/spreadsheets/$sheetId/values/" . rawurlencode( $sheetName ) . '!A1:append?valueInputOption=RAW',
			array(
				'headers' => array(
					'Authorization' => $bearer,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 20,
			)
		);
		if ( is_wp_error( $resp ) ) {
			return $resp->get_error_message();
		}
		$code = wp_remote_retrieve_response_code( $resp );
		return ( $code >= 200 && $code < 300 ) ? true : 'Append failed (HTTP ' . $code . ').';
	}
}
