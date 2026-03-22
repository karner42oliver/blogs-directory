<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//hide some blogs from result
function blogs_directory_hide_some_blogs( $blog_id ) {
    $blogs_directory_hide_blogs = get_site_option( 'blogs_directory_hide_blogs');

    /*Hide Pro Site blogs */
    if ( isset( $blogs_directory_hide_blogs['pro_site'] ) && 1 == $blogs_directory_hide_blogs['pro_site'] ) {
        global $ProSites_Module_PayToBlog, $psts;
        //don't show unpaid blogs
        if ( is_object( $ProSites_Module_PayToBlog ) && $psts->get_setting( 'ptb_front_disable' ) && !is_pro_site( $blog_id, 1 ) )
            return true;
    }

    /*Hide Private blogs */
    if ( isset( $blogs_directory_hide_blogs['private'] ) && 1 == $blogs_directory_hide_blogs['private'] ) {
        //don't show private blogs
        $privacy = get_blog_option( $blog_id, 'blog_public' );
        if ( is_numeric( $privacy ) && 1 != $privacy )
            return true;
    }

    return false;
}

//update rewrite rules
function blogs_directory_flush_rewrite_rules() {
    global $blogs_directory_base;
    $rules = get_option( 'rewrite_rules' );
    if ( !isset( $rules[$blogs_directory_base . '/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'] ) ) {
        flush_rewrite_rules( false );
    }
}

function blogs_directory_page_setup() {
	global $wpdb, $user_ID, $blogs_directory_base;
	if ( get_site_option('blogs_directory_page_setup') != 'complete'.$blogs_directory_base && is_super_admin() ) {
		$page_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_name = '" . $blogs_directory_base . "' AND post_type = 'page'");
		if ( $page_count < 1 ) {
			$wpdb->query( "INSERT INTO " . $wpdb->posts . " ( post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count ) VALUES ( '" . $user_ID . "', '" . current_time( 'mysql' ) . "', '" . current_time( 'mysql' ) . "', '', '" . __('Sites') . "', '', 'publish', 'closed', 'closed', '', '" . $blogs_directory_base . "', '', '', '" . current_time( 'mysql' ) . "', '" . current_time( 'mysql' ) . "', '', 0, '', 0, 'page', '', 0 )" );
		}
		update_site_option('blogs_directory_page_setup', 'complete'.$blogs_directory_base);
	}
}

function blogs_directory_rewrite($wp_rewrite){
	global $blogs_directory_base;
    $blogs_directory_rules = array(
        $blogs_directory_base . '/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'   => 'index.php?pagename=' . $blogs_directory_base,
        $blogs_directory_base . '/([^/]+)/([^/]+)/([^/]+)/?$'           => 'index.php?pagename=' . $blogs_directory_base,
        $blogs_directory_base . '/([^/]+)/([^/]+)/?$'                   => 'index.php?pagename=' . $blogs_directory_base,
        $blogs_directory_base . '/([^/]+)/?$'                           => 'index.php?pagename=' . $blogs_directory_base
    );
    $wp_rewrite = $blogs_directory_rules + $wp_rewrite;
	return $wp_rewrite;
}

function blogs_directory_url_parse(){
	global $wpdb, $current_site, $blogs_directory_base;
	$blogs_directory_url = $_SERVER['REQUEST_URI'];
	if ( $current_site->path != '/' ) {
		$blogs_directory_url = str_replace('/' . $current_site->path . '/', '', $blogs_directory_url);
		$blogs_directory_url = str_replace($current_site->path . '/', '', $blogs_directory_url);
		$blogs_directory_url = str_replace($current_site->path, '', $blogs_directory_url);
	}
	$blogs_directory_url = ltrim($blogs_directory_url, "/");
	$blogs_directory_url = rtrim($blogs_directory_url, "/");
	$blogs_directory_url = ltrim($blogs_directory_url, $blogs_directory_base);
	$blogs_directory_url = ltrim($blogs_directory_url, "/");

	$blogs_directory_1 = $blogs_directory_2 = $blogs_directory_3 = $blogs_directory_4 = '';
	if( !empty( $blogs_directory_url ) ) {
		$blogs_directory_array = explode("/", $blogs_directory_url);
		for( $i = 1, $j = count( $blogs_directory_array ); $i <= $j ; $i++ ) {
			$blogs_directory_var = "blogs_directory_$i";
			${$blogs_directory_var} = $blogs_directory_array[$i-1];
		}
	}

	$page_type = '';
	$page_subtype = '';
	$page = '';
	$blog = '';
	$phrase = '';
	if ( empty( $blogs_directory_1 ) || is_numeric( $blogs_directory_1 ) ) {
		//landing
		$page_type = 'landing';
		$page = $blogs_directory_1;
		if ( empty( $page ) ) {
			$page = 1;
		}
	} else if ( $blogs_directory_1 == 'search' ) {
		//search
		if (wp_verify_nonce($_POST['_wp_nonce'], 'search-sites')) {
				$page_type = 'search';
				$phrase = isset( $_POST['phrase'] ) ? $_POST['phrase'] : '';
				if ( empty( $phrase ) ) {
					$phrase = $blogs_directory_2;
					$page = $blogs_directory_3;
					if ( empty( $page ) ) {
						$page = 1;
					}
				} else {
					$page = $blogs_directory_3;
					if ( empty( $page ) ) {
						$page = 1;
					}
				}
				$phrase = urldecode( $phrase );
		}
	}

	$blogs_directory['page_type'] = $page_type;
	$blogs_directory['page'] = $page;
	$blogs_directory['phrase'] = $phrase;

	return $blogs_directory;
}
