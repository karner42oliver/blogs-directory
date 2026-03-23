<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX-Handler für die Suchleiste auf der blogs-directory Seite
 * Sucht AUSSCHLIESSLICH im ps-postindexer network_posts Index
 */
function blogs_directory_search_form_ajax_handler() {
	// Prüfe ob AJAX-Request von blogs-directory
	if ( ! isset( $_GET['bd_ajax_search'] ) || $_GET['bd_ajax_search'] != '1' ) {
		return;
	}

	// Verhindere Caching
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$phrase = isset( $_GET['phrase'] ) ? sanitize_text_field( $_GET['phrase'] ) : '';

	if ( empty( $phrase ) || strlen( $phrase ) < 2 ) {
		echo '';
		exit;
	}

	global $wpdb;

	// Hole ps-postindexer Einstellungen
	$limit = (int) get_site_option( 'global_site_search_per_page', 10 );
	$post_type = get_site_option( 'global_site_search_post_type', 'post' );
	$like = '%' . $wpdb->esc_like( $phrase ) . '%';

	// Query gegen network_posts Index
	if ( $post_type === 'all' ) {
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
			$post_type,
			$limit
		);
	}

	$results = $wpdb->get_results( $query );

	// Ausgabe HTML
	if ( ! empty( $results ) ) {
		echo '<ul class="bd-search-results-posts">';
		foreach ( $results as $post ) {
			echo '<li>';
			echo '<a href="' . esc_url( $post->guid ) . '" class="bd-search-post-link">';
			echo esc_html( $post->post_title );
			echo '</a>';
			echo '</li>';
		}
		echo '</ul>';
	} else {
		echo '<div class="bd-search-no-results">' . esc_html__( 'Keine Ergebnisse gefunden.', 'blogs-directory' ) . '</div>';
	}

	exit;
}

// Hook - muss FRÜH registriert werden
add_action( 'init', 'blogs_directory_search_form_ajax_handler', 1 );
