<?php

function global_site_search_roundup( $value, $dp ) {
	return ceil( $value * pow( 10, $dp ) ) / pow( 10, $dp );
}

function global_site_search_get_allowed_blogs() {
	if ( !defined( 'GLOBAL_SITE_SEARCH_BLOG' ) ) {
		define( 'GLOBAL_SITE_SEARCH_BLOG', 1 );
	}

	$site_search_blog = GLOBAL_SITE_SEARCH_BLOG;
	if ( is_string( $site_search_blog ) ) {
		$site_search_blog = array_filter( array_map( 'absint', explode( ',', $site_search_blog ) ) );
	}

	return apply_filters( 'global_site_search_allowed_blogs', (array)$site_search_blog );
}

function global_site_search_locate_template( $template_name, $template_path = 'global-site-search' ) {
	// Look within passed path within the theme - this is priority
	$template = locate_template( array(
		trailingslashit( $template_path ) . $template_name,
		$template_name
	) );

	// Get default template
	if ( !$template ) {
		$template = implode( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), 'templates', $template_name ) );
	}

	// Return what we found
	return apply_filters( 'global_site_search_locate_template', $template, $template_name, $template_path );
}

function global_site_search_form() {
	include global_site_search_locate_template( 'global-site-search-form.php' );
}

function global_site_search_get_search_base() {
	global $global_site_search;
	return $global_site_search->global_site_search_base;
}

function global_site_search_get_phrase() {
	global $wp_query;

	$phrase = isset( $wp_query->query_vars['search'] ) ? urldecode( $wp_query->query_vars['search'] ) : '';
	if ( empty( $phrase ) && isset( $_REQUEST['phrase'] ) ) {
		$phrase = sanitize_text_field( wp_unslash( $_REQUEST['phrase'] ) );
	}

	return trim( preg_replace( '/\s+/u', ' ', $phrase ) );
}

function global_site_search_get_pagination( $mainlink = '' ) {
	global $network_query, $current_site;
	if ( absint( $network_query->max_num_pages ) <= 1 ) {
		return '';
	}

	if ( empty( $mainlink ) ) {
		$mainlink = $current_site->path . global_site_search_get_search_base() . '/' . urlencode( global_site_search_get_phrase() );
	}

	return paginate_links( array(
		'base'      => trailingslashit( $mainlink ) . '%_%',
		'format'    => 'page/%#%',
		'total'     => $network_query->max_num_pages,
		'current'   => !empty( $network_query->query_vars['paged'] ) ? $network_query->query_vars['paged'] : 1,
		'prev_next' => true,
	) );
}

function global_site_search_get_background_color() {
	return get_site_option( 'global_site_search_background_color', '#F2F2EA' );
}

function global_site_search_get_alt_background_color() {
	return get_site_option( 'global_site_search_alternate_background_color', '#FFFFFF' );
}

function global_site_search_get_border_color() {
	return get_site_option( 'global_site_search_border_color', '#CFD0CB' );
}

/**
	* Liefert den AJAX-Endpunkt fuer die Live-Suche.
	*/
function global_site_search_get_ajax_url() {
	return admin_url( 'admin-ajax.php' );
}

/**
	* Liefert einen Nonce fuer die oeffentliche Live-Suche.
	*/
function global_site_search_get_ajax_nonce() {
	return wp_create_nonce( 'global-site-search-live' );
}

/**
	* Liefert eine stabile Kennung fuer einfaches Public-Throttling.
	*/
function global_site_search_get_client_id() {
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
	* Einfache Drossel fuer oeffentliche Live-Suche.
	*/
function global_site_search_is_rate_limited( $bucket, $limit = 6, $window = 10 ) {
	$bucket = sanitize_key( $bucket );
	$limit = max( 1, absint( $limit ) );
	$window = max( 1, absint( $window ) );
	$cache_key = 'global_site_search_rate_' . md5( $bucket . ':' . global_site_search_get_client_id() );
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
	* Liest Treffer fuer die Live-Suche mit hartem Ergebnislimit.
	*/
function global_site_search_query_live_results( $phrase, $limit = 10 ) {
	global $wpdb;

	$phrase = trim( preg_replace( '/\s+/u', ' ', sanitize_text_field( (string) $phrase ) ) );
	if ( '' === $phrase ) {
		return array();
	}

	$limit = max( 1, min( absint( $limit ), 20 ) );
	$post_type = (string) get_site_option( 'global_site_search_post_type', 'post' );
	$like = '%' . $wpdb->esc_like( $phrase ) . '%';

	if ( 'all' === $post_type ) {
		$query = $wpdb->prepare(
			"SELECT ID, BLOG_ID, post_title, guid FROM {$wpdb->base_prefix}network_posts
			 WHERE post_title LIKE %s AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT %d",
			$like,
			$limit
		);
	} else {
		$query = $wpdb->prepare(
			"SELECT ID, BLOG_ID, post_title, guid FROM {$wpdb->base_prefix}network_posts
			 WHERE post_title LIKE %s AND post_type = %s AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT %d",
			$like,
			sanitize_key( $post_type ),
			$limit
		);
	}

	return $wpdb->get_results( $query );
}

/**
	* Rendert HTML fuer Live-Suchergebnisse.
	*/
function global_site_search_render_live_results( $phrase, $limit = 10, $include_more_link = false ) {
	$results = global_site_search_query_live_results( $phrase, $limit );

	if ( empty( $results ) ) {
		return '<div style="color:#888;">' . esc_html__( 'Keine Treffer gefunden.', 'postindexer' ) . '</div>';
	}

	$html = '<ul class="gss-widget-results">';
	foreach ( $results as $row ) {
		$html .= '<li><a href="' . esc_url( $row->guid ) . '">' . esc_html( $row->post_title ) . '</a></li>';
	}
	$html .= '</ul>';

	if ( $include_more_link ) {
		$main_site_url = network_home_url( global_site_search_get_search_base() . '/' . urlencode( $phrase ) . '/' );
		$html .= '<div style="margin-top:0.7em;"><a href="' . esc_url( $main_site_url ) . '" style="font-weight:bold;">' . esc_html__( 'Weitere Treffer anzeigen', 'postindexer' ) . '</a></div>';
	}

	return $html;
}

/**
	* Gemeinsamer AJAX-Handler fuer Live-Suchvorschau.
	*/
function global_site_search_ajax_handler( $include_more_link = false ) {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$nonce = isset( $_REQUEST['nonce'] ) ? wp_unslash( $_REQUEST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'global-site-search-live' ) ) {
		status_header( 403 );
		echo '<div style="color:#888;">' . esc_html__( 'Sicherheitspruefung fehlgeschlagen.', 'postindexer' ) . '</div>';
		wp_die();
	}

	if ( global_site_search_is_rate_limited( $include_more_link ? 'widget-search' : 'live-search', 6, 10 ) ) {
		status_header( 429 );
		echo '<div style="color:#888;">' . esc_html__( 'Zu viele Suchanfragen. Bitte kurz warten.', 'postindexer' ) . '</div>';
		wp_die();
	}

	$phrase = isset( $_REQUEST['phrase'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['phrase'] ) ) : '';
	$phrase = trim( preg_replace( '/\s+/u', ' ', $phrase ) );
	if ( '' === $phrase || strlen( $phrase ) < 2 ) {
		echo '';
		wp_die();
	}

	echo global_site_search_render_live_results( $phrase, $include_more_link ? 5 : get_site_option( 'global_site_search_per_page', 10 ), $include_more_link );
	wp_die();
}

function global_site_search_ajax_results_handler() {
	global_site_search_ajax_handler( false );
}

function global_site_search_ajax_widget_handler() {
	global_site_search_ajax_handler( true );
}

add_action( 'wp_ajax_global_site_search_live', 'global_site_search_ajax_results_handler' );
add_action( 'wp_ajax_nopriv_global_site_search_live', 'global_site_search_ajax_results_handler' );
add_action( 'wp_ajax_global_site_search_widget_live', 'global_site_search_ajax_widget_handler' );
add_action( 'wp_ajax_nopriv_global_site_search_widget_live', 'global_site_search_ajax_widget_handler' );