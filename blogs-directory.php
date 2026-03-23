<?php
/*
Plugin Name: MS-Blogs-Verzeichnis
Plugin URI: https://cp-psource.github.io/blogs-directory/
Description: Dieses Plugin bietet ein paginiertes, vollständig durchsuchbares, Avatar inklusive, automatisches und ziemlich gut aussehendes Verzeichnis aller Blogs auf Deiner ClassicPress Multisite.
Author: PSOURCE
Author URI: https://github.com/Power-Source
Version: 1.0.0
Text Domain: blogs-directory
Network: true
*/

/*
Copyright 2019-2026 PSOURCE (https://github.com/Power-Source)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if ( ! defined( 'BLOGS_DIRECTORY_PLUGIN_FILE' ) ) {
	define( 'BLOGS_DIRECTORY_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'BLOGS_DIRECTORY_PLUGIN_DIR' ) ) {
	define( 'BLOGS_DIRECTORY_PLUGIN_DIR', plugin_dir_path( BLOGS_DIRECTORY_PLUGIN_FILE ) );
}

/**
 * Liefert den Netzwerk-Modus fuer das eingebettete Site-Reviews-Modul.
 *
 * Moegliche Werte: off | allow | force
 */
function blogs_directory_get_site_reviews_network_mode() {
	$mode = (string) get_site_option( 'blogs_directory_site_reviews_mode', 'force' );
	if ( ! in_array( $mode, array( 'off', 'allow', 'force' ), true ) ) {
		return 'force';
	}

	return $mode;
}

/**
 * Prueft, ob Site Reviews auf der aktuellen Site aktiv sein soll.
 */
function blogs_directory_is_site_reviews_module_enabled() {
	$mode = blogs_directory_get_site_reviews_network_mode();

	if ( 'off' === $mode ) {
		return false;
	}

	if ( 'force' === $mode ) {
		return true;
	}

	if ( is_network_admin() ) {
		return false;
	}

	return 1 === (int) get_option( 'blogs_directory_site_reviews_enabled', 0 );
}

if ( blogs_directory_is_site_reviews_module_enabled() && ! function_exists( 'glsr_app' ) && file_exists( BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/modules/site-reviews/site-reviews.php' ) ) {
	require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/modules/site-reviews/site-reviews.php';
}

$blogs_widget_file = BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/modules/blogs-widget/widget-blogs.php';
if ( file_exists( $blogs_widget_file ) ) {
	require_once $blogs_widget_file;
}
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/admin/settings-page.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/admin/blog-avatar-page.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/core/routing.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/core/navigation.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/core/render/title.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/core/render/output.php';
require_once BLOGS_DIRECTORY_PLUGIN_DIR . 'includes/core/search-form-ajax.php';
//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//


if (defined('BLOGS_DIRECTORY_SLUG')) {
	$blogs_directory_base = BLOGS_DIRECTORY_SLUG;
} else {
	$blogs_directory_base = 'blogs'; //domain.tld/BASE/ Ex: domain.tld/user/
}

load_plugin_textdomain( 'blogs-directory', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//

if ( isset($current_blog) && ($current_blog->domain . $current_blog->path == $current_site->domain . $current_site->path )) {
	add_filter('rewrite_rules_array','blogs_directory_rewrite');
	add_filter('the_content', 'blogs_directory_output', 20);
	add_filter('the_title', 'blogs_directory_title_output', 99, 2);
	add_action('wp_enqueue_scripts', 'blogs_directory_enqueue_frontend_assets');
	add_action('admin_footer', 'blogs_directory_page_setup');
	add_action('init', 'blogs_directory_flush_rewrite_rules');
}

add_action('network_admin_menu', 'blogs_directory_admin_page');
add_action('admin_init', 'blogs_directory_save_options');
add_action('admin_init', 'blogs_directory_register_site_reviews_discussion_setting');
add_action('admin_enqueue_scripts', 'blogs_directory_settings_enqueue_assets');
add_action('admin_menu', 'blogs_directory_blog_avatar_admin_page');
add_action('admin_enqueue_scripts', 'blogs_directory_blog_avatar_enqueue_assets');
add_action('admin_init', 'blogs_directory_blog_avatar_handle_actions');
add_action('wp_ajax_blogs_directory_blog_avatar', 'blogs_directory_blog_avatar_ajax');

//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//

