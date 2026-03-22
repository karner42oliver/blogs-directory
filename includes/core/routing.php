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
		$page_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s",
				$blogs_directory_base,
				'page'
			)
		);
		if ( $page_count < 1 ) {
			wp_insert_post(
				array(
					'post_author'      => (int) $user_ID,
					'post_date'        => current_time( 'mysql' ),
					'post_date_gmt'    => current_time( 'mysql', true ),
					'post_title'       => __( 'Sites', 'blogs-directory' ),
					'post_status'      => 'publish',
					'comment_status'   => 'closed',
					'ping_status'      => 'closed',
					'post_name'        => $blogs_directory_base,
					'post_modified'    => current_time( 'mysql' ),
					'post_modified_gmt'=> current_time( 'mysql', true ),
					'post_type'        => 'page',
				),
				true
			);
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
	$blogs_directory_url = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
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
		$nonce = isset( $_POST['_wp_nonce'] ) ? wp_unslash( $_POST['_wp_nonce'] ) : '';
		if ( wp_verify_nonce( $nonce, 'search-sites' ) ) {
				$page_type = 'search';
				$phrase = isset( $_POST['phrase'] ) ? sanitize_text_field( wp_unslash( $_POST['phrase'] ) ) : '';
				if ( empty( $phrase ) ) {
					$phrase = sanitize_text_field( urldecode( $blogs_directory_2 ) );
					$page = absint( $blogs_directory_3 );
					if ( empty( $page ) ) {
						$page = 1;
					}
				} else {
					$page = absint( $blogs_directory_3 );
					if ( empty( $page ) ) {
						$page = 1;
					}
				}
				$phrase = sanitize_text_field( urldecode( $phrase ) );
		}
	}

	$blogs_directory['page_type'] = $page_type;
	$blogs_directory['page'] = $page;
	$blogs_directory['phrase'] = $phrase;

	return $blogs_directory;
}
