<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function blogs_directory_search_form_output($content, $phrase) {
	global $wpdb, $current_site, $blogs_directory_base;

	if ( !empty( $phrase ) ) {
		$content .= '<form action="' . $current_site->path . $blogs_directory_base . '/search/' . urlencode( $phrase ) . '/" method="post">';
	} else {
		$content .= '<form action="' . $current_site->path . $blogs_directory_base . '/search/" method="post">';
	}
		$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor=""  class="blogs_directory_search_table">';
		$content .= '<tr>';
		    $content .= '<td style="font-size:12px; text-align:left;" width="80%">';
				$content .= '<input name="phrase" style="width: 100%;" type="text" value="' . $phrase . '">';
			$content .= '</td>';
			$content .= '<td style="font-size:12px; text-align:right;" width="20%">';
				$content .= '<input name="Submit" value="' . __('Suche','blogs-directory') . '" type="submit">';
			$content .= '</td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= wp_nonce_field('search-sites','_wp_nonce', $_SERVER['PHP_SELF'], false);
	$content .= '</form>';
	return $content;
}

function blogs_directory_search_navigation_output($content, $per_page, $page, $phrase, $next){
	global $wpdb, $current_site, $blogs_directory_base;
	if ( is_subdomain_install() ) {
		$blog_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE ( domain LIKE '%%%s%%' ) AND spam != 1 AND deleted != 1 AND blog_id != 1", $phrase) );
	} else {
		$blog_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE ( path LIKE '%%%s%%' ) AND spam != 1 AND deleted != 1 AND blog_id != 1", $phrase) );
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
		$content .= '<a style="text-decoration:none;" href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/search/' . urlencode( $phrase ) . '/' . $previous_page . '/">&laquo; ' . __('Zurück','blogs-directory') . '</a>';
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
				$content .= '<a style="text-decoration:none;" href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/search/' . urlencode( $phrase ) . '/' . $next_page . '/">' . __('Weiter','blogs-directory') . ' &raquo;</a>';
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

	$blogs_directory_hide_blogs = get_site_option( 'blogs_directory_hide_blogs');

	$query = "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "blogs WHERE spam = 0 AND deleted = 0 AND archived = '0' AND blog_id != 1";
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
		$content .= '<a style="text-decoration:none;" href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/' . $previous_page . '/">&laquo; ' . __('Zurück','blogs-directory') . '</a>';
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
			$content .= '<a style="text-decoration:none;" href="http://' . $current_site->domain . $current_site->path . $blogs_directory_base . '/' . $next_page . '/">' . __('Weiter','blogs-directory') . ' &raquo;</a>';
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
