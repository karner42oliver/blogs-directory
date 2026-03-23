<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX-Handler für blogs-directory Live-Suche
 * Wird aufgerufen bei: ?bd_search_ajax=1&phrase=...
 */
function blogs_directory_search_ajax_handler() {
	global $wpdb, $current_site;

	// Prüfe ob AJAX-Request
	if ( ! isset( $_GET['bd_search_ajax'] ) || $_GET['bd_search_ajax'] != '1' ) {
		return;
	}

	// Kein Cachen für AJAX-Requests
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$phrase = isset( $_GET['phrase'] ) ? sanitize_text_field( $_GET['phrase'] ) : '';
	
	if ( empty( $phrase ) ) {
		echo '';
		exit;
	}

	// Hole Einstellungen
	$settings = blogs_directory_get_output_settings();
	$include_main_site = (int) get_site_option( 'blogs_directory_include_main_site', 1 );
	$main_blog_id = (int) $current_site->id;
	$per_page = 5; // Für Vorschau nur 5 Ergebnisse

	// Query Blogs
	if ( is_subdomain_install() ) {
		$query = "SELECT blog_id, domain, public, archived, mature, spam, deleted FROM " . $wpdb->base_prefix . "blogs 
				WHERE ( domain LIKE %s OR path LIKE %s ) AND spam != 1 AND deleted != 1 AND public = 1 
				ORDER BY registered DESC LIMIT %d";
		$results = $wpdb->get_results( $wpdb->prepare( 
			$query, 
			'%' . $wpdb->esc_like( $phrase ) . '%',
			'%' . $wpdb->esc_like( $phrase ) . '%',
			$per_page 
		) );
	} else {
		$query = "SELECT blog_id, domain, path, public, archived, mature, spam, deleted FROM " . $wpdb->base_prefix . "blogs 
				WHERE ( path LIKE %s ) AND spam != 1 AND deleted != 1 AND public = 1 
				ORDER BY registered DESC LIMIT %d";
		$results = $wpdb->get_results( $wpdb->prepare( 
			$query, 
			'%' . $wpdb->esc_like( $phrase ) . '%',
			$per_page 
		) );
	}

	if ( empty( $results ) ) {
		echo '<div class="bd-search-no-results">' . esc_html__( 'Keine Ergebnisse gefunden.', 'blogs-directory' ) . '</div>';
		exit;
	}

	// HTML der Ergebnisse
	echo '<ul class="bd-search-results-list">';

	$row_index = 0;
	foreach ( $results as $result ) {
		$blog_id = (int) $result->blog_id;
		$use_alternate = ( $row_index % 2 ) == 1;
		$row_palette = blogs_directory_get_row_palette( $use_alternate, $settings );

		$args = array(
			'blog_id'       => $blog_id,
			'row_index'     => $row_index,
			'settings'      => $settings,
			'row_palette'   => $row_palette,
			'show_reviews'  => (int) $settings['blogs_directory_show_site_reviews'] === 1,
		);

		// Blog-Details
		switch_to_blog( $blog_id );
		$blog_title = get_bloginfo( 'name' ) ?: get_bloginfo( 'url' );
		$blog_description = get_bloginfo( 'description' ) !== '' ? get_bloginfo( 'description' ) : '';
		$blog_url = get_bloginfo( 'url' );
		restore_current_blog();

		// Render Item
		echo '<li class="bd-search-result-item" style="background-color:' . esc_attr( $row_palette['background'] ) . '">';
		echo '<div class="bd-search-result-content">';
		echo '<a href="' . esc_url( $blog_url ) . '" class="bd-search-result-title" style="color:' . esc_attr( $row_palette['title'] ) . '">' . esc_html( $blog_title ) . '</a>';
		
		if ( ! empty( $blog_description ) ) {
			echo '<p class="bd-search-result-description" style="color:' . esc_attr( $row_palette['text'] ) . '">' . esc_html( wp_trim_words( $blog_description, 15 ) ) . '</p>';
		}

		echo '</div>';
		echo '</li>';

		$row_index++;
	}

	echo '</ul>';
	exit;
}

// Hook
add_action( 'init', 'blogs_directory_search_ajax_handler', 1 );
