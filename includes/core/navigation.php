<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function blogs_directory_search_form_output($content, $phrase) {
	global $wpdb, $current_site, $blogs_directory_base;
	$phrase = sanitize_text_field( (string) $phrase );
	
	// Check if ps-postindexer Global Site Search is active
	if ( class_exists('Postindexer_Extensions_Admin') ) {
		global $postindexer_extensions_admin;
		if ( isset($postindexer_extensions_admin) && $postindexer_extensions_admin->is_extension_active_for_site('global_site_search') ) {
			// Use Global Site Search form if ps-postindexer is active
			if ( function_exists('global_site_search_form') ) {
				ob_start();
				global_site_search_form();
				$content .= ob_get_clean();
				return $content;
			}
		}
	}
	
	// Fallback to blogs-directory search form
	$search_base_url = home_url( trailingslashit( $blogs_directory_base ) . 'search/' );

	if ( !empty( $phrase ) ) {
		$content .= '<form action="' . esc_url( $search_base_url . rawurlencode( $phrase ) . '/' ) . '" method="post">';
	} else {
		$content .= '<form action="' . esc_url( $search_base_url ) . '" method="post">';
	}
		$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor=""  class="blogs_directory_search_table">';
		$content .= '<tr>';
		    $content .= '<td style="font-size:12px; text-align:left;" width="80%">';
				$content .= '<input name="phrase" style="width: 100%;" type="text" value="' . esc_attr( $phrase ) . '">';
			$content .= '</td>';
			$content .= '<td style="font-size:12px; text-align:right;" width="20%">';
				$content .= '<input name="Submit" value="' . __('Suche','blogs-directory') . '" type="submit">';
			$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= wp_nonce_field( 'search-sites', '_wp_nonce', true, false );
	$content .= '</form>';
	return $content;
}

function blogs_directory_search_navigation_output($content, $per_page, $page, $phrase, $next){
	global $wpdb, $current_site, $blogs_directory_base;
	$phrase = sanitize_text_field( (string) $phrase );
	$page = max( 1, absint( $page ) );
	$search_base_url = home_url( trailingslashit( $blogs_directory_base ) . 'search/' );
	$include_main_site = (int) get_site_option( 'blogs_directory_include_main_site', 1 );
	$main_blog_id = (int) $current_site->id;
	if ( is_subdomain_install() ) {
		$query = "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE ( domain LIKE %s ) AND spam != 1 AND deleted != 1";
		$params = array( '%' . $wpdb->esc_like( $phrase ) . '%' );
		if ( ! $include_main_site ) {
			$query .= " AND blog_id != %d";
			$params[] = $main_blog_id;
		}
		$blog_count = $wpdb->get_var( $wpdb->prepare( $query, $params ) );
	} else {
		$query = "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE ( path LIKE %s ) AND spam != 1 AND deleted != 1";
		$params = array( '%' . $wpdb->esc_like( $phrase ) . '%' );
		if ( ! $include_main_site ) {
			$query .= " AND blog_id != %d";
			$params[] = $main_blog_id;
		}
		$blog_count = $wpdb->get_var( $wpdb->prepare( $query, $params ) );
	}
	$blog_count = apply_filters( 'blogs_directory_blogs_count', $blog_count - 1 );

	//generate page div
	//============================================================================//
	$total_pages = blogs_directory_roundup($blog_count / $per_page, 0);
	$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_nav_table">';
	$content .= '<tr>';
	$showing_low = ($page * $per_page) - ($per_page - 1);
	if ($total_pages == $page){
		$showing_high = $blog_count;
	} else {
		$showing_high = $page * $per_page;
	}

    $content .= '<td style="font-size:12px; text-align:left;" width="50%">';
	if ($blog_count > $per_page){
	//============================================================================//
		if ($page == '' || $page == '1'){
			//$content .= __('Previous','blogs-directory');
		} else {
		$previous_page = $page - 1;
		$content .= '<a style="text-decoration:none;" href="' . esc_url( $search_base_url . rawurlencode( $phrase ) . '/' . $previous_page . '/' ) . '">&laquo; ' . esc_html__( 'Zurück', 'blogs-directory' ) . '</a>';
		}
	//============================================================================//
	}
	$content .= '</td>';
    $content .= '<td style="font-size:12px; text-align:right;" width="50%">';
	if ($blog_count > $per_page){
	//============================================================================//
		if ( $next != 'no' ) {
			if ($page == $total_pages){
				//$content .= __('Next','blogs-directory');
			} else {
				if ($total_pages == 1){
					//$content .= __('Next','blogs-directory');
				} else {
					$next_page = $page + 1;
					$content .= '<a style="text-decoration:none;" href="' . esc_url( $search_base_url . rawurlencode( $phrase ) . '/' . $next_page . '/' ) . '">' . esc_html__( 'Weiter', 'blogs-directory' ) . ' &raquo;</a>';
				}
			}
		}
	//============================================================================//
	}
    $content .= '</td>';
	$content .= '</tr>';
    $content .= '</table>';
	return $content;
}

function blogs_directory_landing_navigation_output($content, $per_page, $page){
	global $wpdb, $current_site, $blogs_directory_base;
	$page = max( 1, absint( $page ) );
	$base_url = home_url( trailingslashit( $blogs_directory_base ) );

	$blogs_directory_hide_blogs = get_site_option( 'blogs_directory_hide_blogs');
	$include_main_site = (int) get_site_option( 'blogs_directory_include_main_site', 1 );

	$query = "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE spam = 0 AND deleted = 0 AND archived = '0'";
	if ( ! $include_main_site ) {
		$query .= $wpdb->prepare( " AND blog_id != %d", (int) $current_site->id );
	}
	if ( isset( $blogs_directory_hide_blogs['private'] ) && 1 == $blogs_directory_hide_blogs['private'] ) {
		$query .= " AND public = 1";
	}
	$blog_count = $wpdb->get_var($query);
	$blog_count = apply_filters( 'blogs_directory_blogs_count', $blog_count );

	//generate page div
	//============================================================================//
	$total_pages = blogs_directory_roundup($blog_count / $per_page, 0);
	$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_nav_table">';
	$content .= '<tr>';
	$showing_low = ($page * $per_page) - ($per_page - 1);
	if ($total_pages == $page){
		//last page...
		//$showing_high = $blog_count - (($total_pages - 1) * $per_page);
		$showing_high = $blog_count;
	} else {
		$showing_high = $page * $per_page;
	}

    $content .= '<td style="font-size:12px; text-align:left;" width="50%">';
	if ($blog_count > $per_page){
	//============================================================================//
		if ($page == '' || $page == '1'){
			//$content .= __('Previous','blogs-directory');
		} else {
		$previous_page = $page - 1;
		$content .= '<a style="text-decoration:none;" href="' . esc_url( $base_url . $previous_page . '/' ) . '">&laquo; ' . esc_html__( 'Zurück', 'blogs-directory' ) . '</a>';
		}
	//============================================================================//
	}
	$content .= '</td>';
    $content .= '<td style="font-size:12px; text-align:right;" width="50%">';
	if ($blog_count > $per_page){
	//============================================================================//
		if ($page == $total_pages){
			//$content .= __('Next','blogs-directory');
		} else {
			if ($total_pages == 1){
				//$content .= __('Next','blogs-directory');
			} else {
				$next_page = $page + 1;
			$content .= '<a style="text-decoration:none;" href="' . esc_url( $base_url . $next_page . '/' ) . '">' . esc_html__( 'Weiter', 'blogs-directory' ) . ' &raquo;</a>';
			}
		}
	//============================================================================//
	}
    $content .= '</td>';
	$content .= '</tr>';
    $content .= '</table>';
	return $content;
}

function blogs_directory_roundup($value, $dp){
    return ceil($value*pow(10, $dp))/pow(10, $dp);
}
