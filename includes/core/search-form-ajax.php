<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
	* Baut die SQL-Teile fuer die Netzwerksuche auf.
 */
function blogs_directory_get_network_posts_results( $phrase, $limit = 10 ) {
	global $wpdb;

	$limit = max( 1, min( absint( $limit ), 20 ) );
	$post_type = (string) get_site_option( 'global_site_search_post_type', 'post' );
	$like = '%' . $wpdb->esc_like( $phrase ) . '%';

	if ( 'all' === $post_type ) {
		$query = $wpdb->prepare(
			"SELECT ID, BLOG_ID, post_title, guid FROM {$wpdb->base_prefix}network_posts
			 WHERE (post_title LIKE %s OR post_content LIKE %s) AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT %d",
			$like,
			$like,
			$limit
		);
	} else {
		$query = $wpdb->prepare(
			"SELECT ID, BLOG_ID, post_title, guid FROM {$wpdb->base_prefix}network_posts
			 WHERE (post_title LIKE %s OR post_content LIKE %s) AND post_type = %s AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT %d",
			$like,
			$like,
			sanitize_key( $post_type ),
			$limit
		);
	}

	return $wpdb->get_results( $query );
}

/**
	* Rendert die Trefferliste fuer Netzwerk-Beitraege.
	*/
function blogs_directory_get_network_posts_results_html( $phrase, $limit = 10 ) {
	$results = blogs_directory_get_network_posts_results( $phrase, $limit );

	if ( empty( $results ) ) {
		return '<div class="bd-search-no-results">' . esc_html__( 'Keine Ergebnisse gefunden.', 'blogs-directory' ) . '</div>';
	}

	$html = '<ul class="bd-search-results-posts">';
	foreach ( $results as $post ) {
		$html .= '<li>';
		$html .= '<a href="' . esc_url( $post->guid ) . '" class="bd-search-post-link">';
		$html .= esc_html( $post->post_title );
		$html .= '</a>';
		$html .= '</li>';
	}
	$html .= '</ul>';

	return $html;
}

/**
	* admin-ajax Endpunkt fuer Netzwerk-Beitragssuche.
	*/
function blogs_directory_search_form_ajax_handler() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$nonce = isset( $_REQUEST['nonce'] ) ? wp_unslash( $_REQUEST['nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'blogs-directory-post-search' ) ) {
		status_header( 403 );
		echo '<div class="bd-search-no-results">' . esc_html__( 'Sicherheitspruefung fehlgeschlagen.', 'blogs-directory' ) . '</div>';
		wp_die();
	}

	if ( blogs_directory_is_search_rate_limited( 'network-post-search', 6, 10 ) ) {
		status_header( 429 );
		echo '<div class="bd-search-no-results">' . esc_html__( 'Zu viele Suchanfragen. Bitte kurz warten.', 'blogs-directory' ) . '</div>';
		wp_die();
	}

	$phrase = blogs_directory_get_ajax_search_phrase();
	if ( '' === $phrase || strlen( $phrase ) < 2 ) {
		echo '';
		wp_die();
	}

	$limit = (int) get_site_option( 'global_site_search_per_page', 10 );
	echo blogs_directory_get_network_posts_results_html( $phrase, $limit );
	wp_die();
}

add_action( 'wp_ajax_blogs_directory_network_post_search', 'blogs_directory_search_form_ajax_handler' );
add_action( 'wp_ajax_nopriv_blogs_directory_network_post_search', 'blogs_directory_search_form_ajax_handler' );
