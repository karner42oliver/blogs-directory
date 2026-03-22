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
		if ( $blogs_directory['page_type'] == 'landing' ) {
			if ( $blogs_directory['page'] > 1 ) {
				$title = '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/">' . $post->post_title . '</a> &raquo; ' . '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/' . $blogs_directory['page'] . '/">' . $blogs_directory['page'] . '</a>';
			} else {
				$title = '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/">' . $post->post_title . '</a>';
			}
		} else if ( $blogs_directory['page_type'] == 'search' ) {
			if ( $blogs_directory['page'] > 1 ) {
				$title = '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/">' . $post->post_title . '</a> &raquo; <a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/search/">' . __('Suchen','blogs-directory') . '</a> &raquo; ' . '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/search/' . urlencode($blogs_directory['phrase']) .  '/' . $blogs_directory['page'] . '/">' . $blogs_directory['page'] . '</a>';
			} else {
				$title = '<a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/">' . $post->post_title . '</a> &raquo; <a href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/search/">' . __('Suchen','blogs-directory') . '</a>';
			}
		}
	}
	return $title;
}
