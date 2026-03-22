<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendert den dynamischen Seitentitel für Verzeichnisseiten.
 */
function blogs_directory_title_output($title, $post_ID = '') {
	global $wpdb, $current_site, $post, $blogs_directory_base;

	if ( in_the_loop() && !empty( $post ) && $post->post_name == $blogs_directory_base && $post_ID == $post->ID) {
		$blogs_directory = blogs_directory_url_parse();
		$base_url = home_url( trailingslashit( $blogs_directory_base ) );
		$safe_post_title = esc_html( $post->post_title );
		if ( $blogs_directory['page_type'] == 'landing' ) {
			if ( $blogs_directory['page'] > 1 ) {
				$title = '<a href="' . esc_url( $base_url ) . '">' . $safe_post_title . '</a> &raquo; ' . '<a href="' . esc_url( $base_url . absint( $blogs_directory['page'] ) . '/' ) . '">' . absint( $blogs_directory['page'] ) . '</a>';
			} else {
				$title = '<a href="' . esc_url( $base_url ) . '">' . $safe_post_title . '</a>';
			}
		} else if ( $blogs_directory['page_type'] == 'search' ) {
			if ( $blogs_directory['page'] > 1 ) {
				$title = '<a href="' . esc_url( $base_url ) . '">' . $safe_post_title . '</a> &raquo; <a href="' . esc_url( $base_url . 'search/' ) . '">' . esc_html__( 'Suchen', 'blogs-directory' ) . '</a> &raquo; ' . '<a href="' . esc_url( $base_url . 'search/' . rawurlencode( (string) $blogs_directory['phrase'] ) . '/' . absint( $blogs_directory['page'] ) . '/' ) . '">' . absint( $blogs_directory['page'] ) . '</a>';
			} else {
				$title = '<a href="' . esc_url( $base_url ) . '">' . $safe_post_title . '</a> &raquo; <a href="' . esc_url( $base_url . 'search/' ) . '">' . esc_html__( 'Suchen', 'blogs-directory' ) . '</a>';
			}
		}
	}
	return $title;
}
