<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
	* Liefert eine stabile Client-Kennung fuer einfaches Public-Throttling.
 */
function blogs_directory_get_ajax_client_id() {
	if ( is_user_logged_in() ) {
		return 'user:' . get_current_user_id();
	}

	$forwarded_for = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? wp_unslash( (string) $_SERVER['HTTP_X_FORWARDED_FOR'] ) : '';
	if ( '' !== $forwarded_for ) {
		$parts = explode( ',', $forwarded_for );
		$forwarded_for = trim( (string) $parts[0] );
	}

	$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) : '';
	$client_ip = '' !== $forwarded_for ? $forwarded_for : $remote_addr;

	return 'guest:' . md5( $client_ip );
}

/**
	* Prueft, ob ein oeffentlicher Such-Endpunkt zu oft aufgerufen wurde.
	*/
function blogs_directory_is_search_rate_limited( $bucket, $limit = 6, $window = 10 ) {
	$bucket = sanitize_key( $bucket );
	$limit = max( 1, absint( $limit ) );
	$window = max( 1, absint( $window ) );
	$cache_key = 'blogs_directory_rate_' . md5( $bucket . ':' . blogs_directory_get_ajax_client_id() );
	$hits = get_site_transient( $cache_key );
	$hits = is_array( $hits ) ? $hits : array();
	$now = time();
	$valid_after = $now - $window;

	$hits = array_values(
		array_filter(
			$hits,
			static function ( $timestamp ) use ( $valid_after ) {
				return is_numeric( $timestamp ) && (int) $timestamp >= $valid_after;
			}
		)
	);

	if ( count( $hits ) >= $limit ) {
		return true;
	}

	$hits[] = $now;
	set_site_transient( $cache_key, $hits, $window );

	return false;
}

/**
	* Liest und validiert die Suchphrase fuer AJAX-Endpunkte.
	*/
function blogs_directory_get_ajax_search_phrase() {
	$phrase = isset( $_REQUEST['phrase'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['phrase'] ) ) : '';
	$phrase = trim( preg_replace( '/\s+/u', ' ', $phrase ) );

	return $phrase;
}

/**
	* Liefert HTML fuer die Blog-Suchvorschau.
	*/
function blogs_directory_get_blog_search_results_html( $phrase, $limit = 5 ) {
	$settings = blogs_directory_get_output_settings();
	$limit = max( 1, min( absint( $limit ), 10 ) );
	$results = blogs_directory_search_blogs_indexed( $phrase, $settings, $limit );

	if ( empty( $results ) ) {
		return '<div class="bd-search-no-results">' . esc_html__( 'Keine Ergebnisse gefunden.', 'blogs-directory' ) . '</div>';
	}

	$html = '<ul class="bd-search-results-list">';

	foreach ( array_values( $results ) as $row_index => $result ) {
		$use_alternate = ( $row_index % 2 ) === 1;
		$row_palette = blogs_directory_get_row_palette( $use_alternate, $settings );
		$blog_url = set_url_scheme( 'http://' . $result['domain'] . $result['path'] );
		$html .= '<li class="bd-search-result-item" style="background-color:' . esc_attr( $row_palette['background'] ) . '">';
		$html .= '<div class="bd-search-result-content">';
		$html .= '<a href="' . esc_url( $blog_url ) . '" class="bd-search-result-title" style="color:' . esc_attr( $row_palette['title'] ) . '">' . esc_html( $result['blogname'] ) . '</a>';

		if ( ! empty( $result['blogdescription'] ) ) {
			$html .= '<p class="bd-search-result-description" style="color:' . esc_attr( $row_palette['text'] ) . '">' . esc_html( wp_trim_words( $result['blogdescription'], 15 ) ) . '</p>';
		}

		$html .= '</div>';
		$html .= '</li>';
	}

	$html .= '</ul>';

	return $html;
}

/**
	* admin-ajax Endpunkt fuer die Blog-Suchvorschau.
	*/
function blogs_directory_search_ajax_handler() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$nonce = isset( $_REQUEST['nonce'] ) ? wp_unslash( $_REQUEST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'blogs-directory-search' ) ) {
		status_header( 403 );
		echo '<div class="bd-search-no-results">' . esc_html__( 'Sicherheitspruefung fehlgeschlagen.', 'blogs-directory' ) . '</div>';
		wp_die();
	}

	if ( blogs_directory_is_search_rate_limited( 'blog-search-preview', 6, 10 ) ) {
		status_header( 429 );
		echo '<div class="bd-search-no-results">' . esc_html__( 'Zu viele Suchanfragen. Bitte kurz warten.', 'blogs-directory' ) . '</div>';
		wp_die();
	}

	$phrase = blogs_directory_get_ajax_search_phrase();
	if ( '' === $phrase || strlen( $phrase ) < 2 ) {
		echo '';
		wp_die();
	}

	echo blogs_directory_get_blog_search_results_html( $phrase, 5 );
	wp_die();
}

add_action( 'wp_ajax_blogs_directory_search_preview', 'blogs_directory_search_ajax_handler' );
add_action( 'wp_ajax_nopriv_blogs_directory_search_preview', 'blogs_directory_search_ajax_handler' );
