<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Laedt optionale Frontend-Styles des Blogs-Verzeichnisses.
 */
function blogs_directory_enqueue_frontend_assets() {
	if ( is_admin() ) {
		return;
	}

	$style_rel_path = 'includes/core/assets/blogs-directory-frontend.css';
	$style_path = BLOGS_DIRECTORY_PLUGIN_DIR . $style_rel_path;
	$style_url = plugins_url( $style_rel_path, BLOGS_DIRECTORY_PLUGIN_FILE );
	$style_version = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';

	wp_enqueue_style(
		'blogs-directory-frontend',
		$style_url,
		array(),
		$style_version
	);
}

/**
 * Zentraler Output-Dispatcher für die Verzeichnisseite.
 */
function blogs_directory_output($content) {
	global $post, $blogs_directory_base;

	if ( empty( $post ) ) {
		return $content;
	}

	if ( $post->post_name == $blogs_directory_base ) {
		$blogs_directory = blogs_directory_url_parse();
		$settings = blogs_directory_get_output_settings();

		if ( $blogs_directory['page_type'] == 'landing' ) {
			$content = blogs_directory_render_landing_content( $content, $blogs_directory, $settings );
		} else if ( $blogs_directory['page_type'] == 'search' ) {
			$content = blogs_directory_render_search_content( $content, $blogs_directory, $settings );
		} else {
			$content = __('Ungültige Seite.','blogs-directory');
		}
	}

	return $content;
}

/**
 * Lädt Verzeichnisoptionen zentral, damit der Output-Dispatcher schlank bleibt.
 */
function blogs_directory_get_output_settings() {
	$background = sanitize_hex_color( get_site_option('blogs_directory_background_color', '#F2F2EA') );
	$alternate_background = sanitize_hex_color( get_site_option('blogs_directory_alternate_background_color', '#FFFFFF') );
	$background_title = sanitize_hex_color( get_site_option('blogs_directory_background_title_color', '#2B261F') );
	$background_text = sanitize_hex_color( get_site_option('blogs_directory_background_text_color', '#5A5A5A') );
	$background_link = sanitize_hex_color( get_site_option('blogs_directory_background_link_color', '#1F4F7B') );
	$alternate_title = sanitize_hex_color( get_site_option('blogs_directory_alternate_title_color', '#2B261F') );
	$alternate_text = sanitize_hex_color( get_site_option('blogs_directory_alternate_text_color', '#5A5A5A') );
	$alternate_link = sanitize_hex_color( get_site_option('blogs_directory_alternate_link_color', '#1F4F7B') );
	$border = sanitize_hex_color( get_site_option('blogs_directory_border_color', '#CFD0CB') );

	return array(
		'blogs_directory_sort_by'                    => get_site_option('blogs_directory_sort_by', 'alphabetically'),
		'blogs_directory_per_page'                   => get_site_option('blogs_directory_per_page', '10'),
		'blogs_directory_background_color'           => $background ? $background : '#F2F2EA',
		'blogs_directory_alternate_background_color' => $alternate_background ? $alternate_background : '#FFFFFF',
		'blogs_directory_background_title_color'     => $background_title ? $background_title : '#2B261F',
		'blogs_directory_background_text_color'      => $background_text ? $background_text : '#5A5A5A',
		'blogs_directory_background_link_color'      => $background_link ? $background_link : '#1F4F7B',
		'blogs_directory_alternate_title_color'      => $alternate_title ? $alternate_title : '#2B261F',
		'blogs_directory_alternate_text_color'       => $alternate_text ? $alternate_text : '#5A5A5A',
		'blogs_directory_alternate_link_color'       => $alternate_link ? $alternate_link : '#1F4F7B',
		'blogs_directory_border_color'               => $border ? $border : '#CFD0CB',
		'blogs_directory_title_blogs_page'           => get_site_option('blogs_directory_title_blogs_page'),
		'blogs_directory_show_description'           => get_site_option('blogs_directory_show_description'),
		'blogs_directory_avatar_fallback_order'      => get_site_option('blogs_directory_avatar_fallback_order', 'site_icon_logo'),
		'blogs_directory_layout_mode'                => get_site_option('blogs_directory_layout_mode', 'list'),
		'blogs_directory_show_site_reviews'          => (int) get_site_option('blogs_directory_show_site_reviews', 0),
		'blogs_directory_show_recent_posts'          => (int) get_site_option('blogs_directory_show_recent_posts', 0),
		'blogs_directory_recent_posts_number'        => (int) get_site_option('blogs_directory_recent_posts_number', 3),
		'blogs_directory_recent_posts_title_chars'   => (int) get_site_option('blogs_directory_recent_posts_title_chars', 80),
		'blogs_directory_recent_posts_content_chars' => (int) get_site_option('blogs_directory_recent_posts_content_chars', 0),
		'blogs_directory_recent_posts_show_avatars'  => (int) get_site_option('blogs_directory_recent_posts_show_avatars', 0),
		'blogs_directory_recent_posts_avatar_size'   => (int) get_site_option('blogs_directory_recent_posts_avatar_size', 24),
		'blogs_directory_recent_posts_post_type'     => get_site_option('blogs_directory_recent_posts_post_type', 'post'),
		'blogs_directory_include_main_site'          => (int) get_site_option('blogs_directory_include_main_site', 1),
		'blogs_directory_hide_blogs'                 => get_site_option( 'blogs_directory_hide_blogs' ),
	);
}

/**
	* Liest Werte bevorzugt aus dem Object Cache und faellt auf Site-Transients zurueck.
	*/
function blogs_directory_cache_get_generation( $scope = 'global' ) {
	$scope = sanitize_key( (string) $scope );
	if ( '' === $scope ) {
		$scope = 'global';
	}

	$option_name = 'blogs_directory_cache_gen_' . $scope;
	$generation = (int) get_site_option( $option_name, 1 );

	return max( 1, $generation );
}

/**
	* Erhoeht eine Cache-Generation und invalidiert damit zugehoerige Keys.
	*/
function blogs_directory_cache_bump_generation( $scope = 'global' ) {
	$scope = sanitize_key( (string) $scope );
	if ( '' === $scope ) {
		$scope = 'global';
	}

	$option_name = 'blogs_directory_cache_gen_' . $scope;
	$current = (int) get_site_option( $option_name, 1 );
	$next = $current + 1;
	if ( $next < 1 ) {
		$next = 1;
	}

	update_site_option( $option_name, $next );

	return $next;
}

/**
	* Invalidiert Caches gezielt oder global.
	*/
function blogs_directory_cache_bust( $scope = 'all' ) {
	$scope = sanitize_key( (string) $scope );

	if ( '' === $scope || 'all' === $scope ) {
		blogs_directory_cache_bump_generation( 'global' );
		return;
	}

	blogs_directory_cache_bump_generation( $scope );
}

/**
	* Baut den realen Cache-Key aus Scope-Generationen.
	*/
function blogs_directory_cache_build_key( $cache_key, $scope = 'global' ) {
	$scope = sanitize_key( (string) $scope );
	if ( '' === $scope ) {
		$scope = 'global';
	}

	$global_generation = blogs_directory_cache_get_generation( 'global' );
	$scope_generation = blogs_directory_cache_get_generation( $scope );

	return 'g' . $global_generation . '-s' . $scope_generation . ':' . $cache_key;
}

/**
	* Liest Werte bevorzugt aus dem Object Cache und faellt auf Site-Transients zurueck.
	*/
function blogs_directory_cache_get( $cache_key, $group = 'blogs-directory', $scope = 'global' ) {
	static $runtime_cache = array();
	$effective_key = blogs_directory_cache_build_key( $cache_key, $scope );

	$runtime_key = $group . ':' . $effective_key;
	if ( array_key_exists( $runtime_key, $runtime_cache ) ) {
		return $runtime_cache[ $runtime_key ];
	}

	$payload = wp_cache_get( $effective_key, $group );
	if ( is_array( $payload ) && array_key_exists( 'value', $payload ) ) {
		$runtime_cache[ $runtime_key ] = $payload['value'];
		return $payload['value'];
	}

	$payload = get_site_transient( 'bd_cache_' . md5( $runtime_key ) );
	if ( is_array( $payload ) && array_key_exists( 'value', $payload ) ) {
		wp_cache_set( $effective_key, $payload, $group, 300 );
		$runtime_cache[ $runtime_key ] = $payload['value'];
		return $payload['value'];
	}

	return false;
}

/**
	* Schreibt Werte parallel in Runtime-, Object- und Site-Transient-Cache.
	*/
function blogs_directory_cache_set( $cache_key, $value, $expiration = 300, $group = 'blogs-directory', $scope = 'global' ) {
	$effective_key = blogs_directory_cache_build_key( $cache_key, $scope );
	$payload = array( 'value' => $value );
	wp_cache_set( $effective_key, $payload, $group, $expiration );
	set_site_transient( 'bd_cache_' . md5( $group . ':' . $effective_key ), $payload, $expiration );

	return $value;
}

/**
	* Liefert Domain-Mapping-Overrides fuer mehrere Blogs in einer Query.
	*/
function blogs_directory_get_domain_map_lookup( array $blog_ids ) {
	global $wpdb;

	$blog_ids = array_values( array_unique( array_filter( array_map( 'absint', $blog_ids ) ) ) );
	if ( empty( $blog_ids ) || ! defined( 'DOMAINMAP_TABLE_MAP' ) ) {
		return array();
	}

	sort( $blog_ids );
	$cache_key = 'domain-map:' . md5( wp_json_encode( $blog_ids ) );
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-domain-map', 'domain-map' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$placeholders = implode( ',', array_fill( 0, count( $blog_ids ), '%d' ) );
	$query = $wpdb->prepare(
		"SELECT blog_id, domain FROM " . DOMAINMAP_TABLE_MAP . " WHERE blog_id IN ($placeholders) ORDER BY blog_id ASC, id ASC",
		$blog_ids
	);
	$rows = $wpdb->get_results( $query, ARRAY_A );
	$lookup = array();

	foreach ( $rows as $row ) {
		$blog_id = isset( $row['blog_id'] ) ? absint( $row['blog_id'] ) : 0;
		if ( $blog_id > 0 && ! isset( $lookup[ $blog_id ] ) && ! empty( $row['domain'] ) ) {
			$lookup[ $blog_id ] = array(
				'domain' => (string) $row['domain'],
				'path'   => '/',
			);
		}
	}

	return blogs_directory_cache_set( $cache_key, $lookup, 300, 'blogs-directory-domain-map', 'domain-map' );
}

/**
	* Baut einen gecachten Suchindex fuer Blogname und Beschreibung.
	*/
function blogs_directory_get_blog_search_index() {
	global $wpdb;

	$cache_key = 'blog-search-index-v1';
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-search', 'search' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$blogs = $wpdb->get_results(
		"SELECT blog_id, domain, path, public, archived, mature, spam, deleted, registered, last_updated
		 FROM {$wpdb->base_prefix}blogs
		 WHERE spam = 0 AND deleted = 0 AND archived = '0'",
		ARRAY_A
	);

	if ( empty( $blogs ) ) {
		return blogs_directory_cache_set( $cache_key, array(), 300, 'blogs-directory-search', 'search' );
	}

	$domain_map_lookup = blogs_directory_get_domain_map_lookup( wp_list_pluck( $blogs, 'blog_id' ) );
	$index = array();

	foreach ( $blogs as $blog ) {
		$blog_id = isset( $blog['blog_id'] ) ? absint( $blog['blog_id'] ) : 0;
		if ( $blog_id < 1 ) {
			continue;
		}

		if ( isset( $domain_map_lookup[ $blog_id ] ) ) {
			$blog['domain'] = $domain_map_lookup[ $blog_id ]['domain'];
			$blog['path'] = $domain_map_lookup[ $blog_id ]['path'];
		}

		$blog_name = get_blog_option( $blog_id, 'blogname', $blog['domain'] . $blog['path'] );
		$blog_description = get_blog_option( $blog_id, 'blogdescription', '' );
		$blog_public_option = (int) get_blog_option( $blog_id, 'blog_public', $blog['public'] );
		$search_blob = strtolower( remove_accents( trim( $blog_name . ' ' . $blog_description . ' ' . $blog['domain'] . ' ' . $blog['path'] ) ) );

		$index[] = array(
			'blog_id'            => $blog_id,
			'domain'             => (string) $blog['domain'],
			'path'               => (string) $blog['path'],
			'public'             => isset( $blog['public'] ) ? (int) $blog['public'] : 1,
			'last_updated'       => isset( $blog['last_updated'] ) ? (string) $blog['last_updated'] : '',
			'blogname'           => (string) $blog_name,
			'blogdescription'    => (string) $blog_description,
			'blog_public_option' => $blog_public_option,
			'search_blob'        => $search_blob,
		);
	}

	return blogs_directory_cache_set( $cache_key, $index, 15 * MINUTE_IN_SECONDS, 'blogs-directory-search', 'search' );
}

/**
	* Prueft, ob ein indexierter Blog aufgrund aktueller Einstellungen verborgen werden soll.
	*/
function blogs_directory_should_hide_indexed_blog( array $blog, array $settings ) {
	$main_site_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) ? (int) BLOG_ID_CURRENT_SITE : ( function_exists( 'get_main_site_id' ) ? (int) get_main_site_id() : 1 );
	if ( empty( $settings['blogs_directory_include_main_site'] ) && (int) $blog['blog_id'] === $main_site_blog_id ) {
		return true;
	}

	if ( isset( $settings['blogs_directory_hide_blogs']['private'] ) && 1 == $settings['blogs_directory_hide_blogs']['private'] && 1 !== (int) $blog['blog_public_option'] ) {
		return true;
	}

	if ( isset( $settings['blogs_directory_hide_blogs']['pro_site'] ) && 1 == $settings['blogs_directory_hide_blogs']['pro_site'] ) {
		global $ProSites_Module_PayToBlog, $psts;
		if ( is_object( $ProSites_Module_PayToBlog ) && is_object( $psts ) && $psts->get_setting( 'ptb_front_disable' ) && function_exists( 'is_pro_site' ) && ! is_pro_site( $blog['blog_id'], 1 ) ) {
			return true;
		}
	}

	return false;
}

/**
	* Durchsucht den gecachten Blogindex und liefert relevante Treffer.
	*/
function blogs_directory_search_blogs_indexed( $phrase, array $settings, $limit = 0 ) {
	$phrase = sanitize_text_field( (string) $phrase );
	$phrase = trim( preg_replace( '/\s+/u', ' ', $phrase ) );
	if ( '' === $phrase ) {
		return array();
	}

	$terms = array_values(
		array_filter(
			array_map(
				static function ( $term ) {
					return strtolower( remove_accents( sanitize_text_field( $term ) ) );
				},
				preg_split( '/\s+/u', $phrase )
			)
		)
	);

	if ( empty( $terms ) ) {
		return array();
	}

	$results = array();
	foreach ( blogs_directory_get_blog_search_index() as $blog ) {
		if ( blogs_directory_should_hide_indexed_blog( $blog, $settings ) ) {
			continue;
		}

		$title_blob = strtolower( remove_accents( $blog['blogname'] ) );
		$description_blob = strtolower( remove_accents( $blog['blogdescription'] ) );
		$search_blob = isset( $blog['search_blob'] ) ? $blog['search_blob'] : $title_blob . ' ' . $description_blob;
		$score = 0;

		foreach ( $terms as $term ) {
			if ( false !== strpos( $title_blob, $term ) ) {
				$score += 2;
			}
			if ( false !== strpos( $description_blob, $term ) ) {
				$score += 1;
			}
			if ( false === strpos( $search_blob, $term ) ) {
				$score = 0;
				break;
			}
		}

		if ( $score > 0 ) {
			$blog['percent'] = $score;
			$results[] = $blog;
		}
	}

	if ( count( $results ) > 1 ) {
		usort(
			$results,
			static function ( $left, $right ) {
				if ( $left['percent'] === $right['percent'] ) {
					return strcasecmp( $left['blogname'], $right['blogname'] );
				}

				return ( $left['percent'] > $right['percent'] ) ? -1 : 1;
			}
		);
	}

	if ( $limit > 0 ) {
		return array_slice( $results, 0, absint( $limit ) );
	}

	return $results;
}

/**
 * Liefert die Farbpalette fuer eine Verzeichniszeile.
 */
function blogs_directory_get_row_palette( $use_alternate, array $settings ) {
	if ( $use_alternate ) {
		return array(
			'background' => $settings['blogs_directory_alternate_background_color'],
			'title'      => $settings['blogs_directory_alternate_title_color'],
			'text'       => $settings['blogs_directory_alternate_text_color'],
			'link'       => $settings['blogs_directory_alternate_link_color'],
		);
	}

	return array(
		'background' => $settings['blogs_directory_background_color'],
		'title'      => $settings['blogs_directory_background_title_color'],
		'text'       => $settings['blogs_directory_background_text_color'],
		'link'       => $settings['blogs_directory_background_link_color'],
	);
}

/**
 * Baut die CSS-Variablen fuer eine Verzeichniszeile.
 */
function blogs_directory_get_row_style_attr( array $palette, $extra = '' ) {
	$style = 'background-color:' . $palette['background'] . ';';
	$style .= '--bd-row-bg:' . $palette['background'] . ';';
	$style .= '--bd-row-title:' . $palette['title'] . ';';
	$style .= '--bd-row-text:' . $palette['text'] . ';';
	$style .= '--bd-row-link:' . $palette['link'] . ';';
	$style .= 'color:' . $palette['text'] . ';';

	if ( '' !== $extra ) {
		$style .= $extra;
	}

	return esc_attr( $style );
}

/**
 * Liefert den konfigurierten Layout-Modus des Verzeichnisses.
 */
function blogs_directory_get_layout_mode( array $settings ) {
	$layout_mode = isset( $settings['blogs_directory_layout_mode'] ) ? (string) $settings['blogs_directory_layout_mode'] : 'list';

	return in_array( $layout_mode, array( 'list', 'grid' ), true ) ? $layout_mode : 'list';
}

/**
 * Rendert einen Blog-Eintrag als Grid-Karte.
 */
function blogs_directory_render_grid_item( array $args ) {
	$palette = $args['palette'];
	$style = blogs_directory_get_row_style_attr(
		$palette,
		'border:1px solid ' . $args['border_color'] . ';border-radius:12px;padding:18px;'
	);

	$html = '<article class="blogs_directory_grid_item" style="' . $style . '">';
	$html .= '<div class="blogs_directory_grid_header">';

	if ( '' !== $args['avatar_html'] ) {
		$html .= '<div class="blogs_directory_grid_avatar"><a style="text-decoration:none; color:' . esc_attr( $palette['link'] ) . ';" href="' . esc_url( $args['blog_url'] ) . '">' . $args['avatar_html'] . '</a></div>';
	}

	$html .= '<div class="blogs_directory_grid_body">';
	$html .= '<a class="blogs_directory_grid_title" style="color:' . esc_attr( $palette['title'] ) . ';" href="' . esc_url( $args['blog_url'] ) . '">' . esc_html( $args['blog_title'] ) . '</a>';

	if ( '' !== $args['site_reviews_html'] ) {
		$html .= '<div class="blogs_directory_grid_reviews">' . $args['site_reviews_html'] . '</div>';
	}

	if ( ! empty( $args['show_description'] ) && '' !== $args['blog_description'] ) {
		$html .= '<div class="blogs_directory_grid_description" style="color:' . esc_attr( $palette['text'] ) . ';">' . esc_html( $args['blog_description'] ) . '</div>';
	}

	if ( '' !== $args['recent_posts_html'] ) {
		$html .= $args['recent_posts_html'];
	}

	$html .= '</div>';
	$html .= '</div>';
	$html .= '</article>';

	return $html;
}

/**
 * Mapped eine Groesse auf die in Avatars verwendeten Zielgroessen.
 */
function blogs_directory_map_avatar_size( $size ) {
	$size = absint( $size );
	if ( in_array( $size, array( 16, 32, 48, 96, 128, 192, 256 ), true ) ) {
		return $size;
	}

	if ( $size < 25 ) {
		return 16;
	}
	if ( $size < 41 ) {
		return 32;
	}
	if ( $size < 73 ) {
		return 48;
	}
	if ( $size < 113 ) {
		return 96;
	}
	if ( $size < 153 ) {
		return 128;
	}
	if ( $size < 213 ) {
		return 192;
	}

	return 256;
}

/**
 * Ermittelt den zentralen Upload-Pfad wie im Avatars-Plugin.
 */
function blogs_directory_get_avatars_upload_dir() {
	static $upload_dir = null;

	if ( null !== $upload_dir ) {
		return $upload_dir;
	}

	$main_blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) ? (int) BLOG_ID_CURRENT_SITE : 1;
	switch_to_blog( $main_blog_id );
	$upload_dir = wp_upload_dir();
	restore_current_blog();

	if ( ! empty( $upload_dir['basedir'] ) && preg_match( '/blogs.dir.*$/', $upload_dir['basedir'] ) ) {
		$upload_dir['basedir'] = preg_replace( '/blogs.dir.*$/', 'uploads', $upload_dir['basedir'] );
	}

	if ( ! empty( $upload_dir['baseurl'] ) && preg_match( '/blogs.dir.*$/', $upload_dir['baseurl'] ) ) {
		$upload_dir['baseurl'] = preg_replace( '/blogs.dir.*$/', 'uploads', $upload_dir['baseurl'] );
	}

	return $upload_dir;
}

/**
 * Liefert den absoluten Ordnerpfad fuer Blog-Avatare einer Site.
 */
function blogs_directory_get_blog_avatar_dir_path( $blog_id ) {
	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return '';
	}

	$upload_dir = blogs_directory_get_avatars_upload_dir();
	if ( empty( $upload_dir['basedir'] ) ) {
		return '';
	}

	$folder = substr( md5( (string) $blog_id ), 0, 3 );

	return trailingslashit( $upload_dir['basedir'] ) . 'avatars/blog/' . $folder . '/';
}

/**
 * Liefert die URL eines gesetzten Blog-Avatars (ohne Gravatar-Default).
 */
function blogs_directory_get_custom_blog_avatar_url( $blog_id, $size ) {
	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return '';
	}

	$size = blogs_directory_map_avatar_size( $size );
	$cache_key = 'custom-avatar-url:' . $blog_id . ':' . $size;
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-branding', 'branding' );
	if ( false !== $cached ) {
		return (string) $cached;
	}

	$upload_dir = blogs_directory_get_avatars_upload_dir();
	if ( empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) {
		return '';
	}

	$avatar_dir = blogs_directory_get_blog_avatar_dir_path( $blog_id );
	if ( '' === $avatar_dir ) {
		return '';
	}

	$folder = substr( md5( (string) $blog_id ), 0, 3 );
	$sizes = array( $size, 16, 32, 48, 96, 128, 192, 256 );
	$sizes = array_values( array_unique( array_map( 'absint', $sizes ) ) );
	$extensions = array( 'png', 'jpg', 'jpeg', 'gif', 'webp' );

	foreach ( $sizes as $candidate_size ) {
		foreach ( $extensions as $extension ) {
			$filename = 'blog-' . $blog_id . '-' . $candidate_size . '.' . $extension;
			$file_path = $avatar_dir . $filename;

			if ( is_file( $file_path ) ) {
				return blogs_directory_cache_set( $cache_key, trailingslashit( $upload_dir['baseurl'] ) . 'avatars/blog/' . $folder . '/' . $filename, 15 * MINUTE_IN_SECONDS, 'blogs-directory-branding', 'branding' );
			}
		}
	}

	return blogs_directory_cache_set( $cache_key, '', 5 * MINUTE_IN_SECONDS, 'blogs-directory-branding', 'branding' );
}

/**
 * Liefert Site-Icon und faellt auf Custom Logo der jeweiligen Site zurueck.
 */
function blogs_directory_get_blog_branding_url( $blog_id, $size, $fallback_order = 'site_icon_logo' ) {
	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return '';
	}

	$size = absint( $size );
	$fallback_order = in_array( $fallback_order, array( 'site_icon_logo', 'logo_site_icon' ), true ) ? $fallback_order : 'site_icon_logo';
	$cache_key = 'branding-url:' . $blog_id . ':' . $size . ':' . $fallback_order;
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-branding', 'branding' );
	if ( false !== $cached ) {
		return (string) $cached;
	}

	switch_to_blog( $blog_id );

	$site_icon_url = get_site_icon_url( $size );
	if ( empty( $site_icon_url ) ) {
		$site_icon_id = absint( get_option( 'site_icon' ) );
		if ( $site_icon_id > 0 ) {
			$site_icon_url = wp_get_attachment_image_url( $site_icon_id, array( $size, $size ) );
		}
	}

	$logo_url = '';
	if ( function_exists( 'get_theme_mod' ) ) {
		$custom_logo_id = absint( get_theme_mod( 'custom_logo' ) );
		if ( $custom_logo_id > 0 ) {
			$custom_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'thumbnail' );
			if ( ! empty( $custom_logo_url ) ) {
				$logo_url = $custom_logo_url;
			}
		}
	}

	if ( 'logo_site_icon' === $fallback_order ) {
		if ( ! empty( $logo_url ) ) {
			$branding_url = $logo_url;
		} elseif ( ! empty( $site_icon_url ) ) {
			$branding_url = $site_icon_url;
		} else {
			$branding_url = '';
		}
	} else {
		if ( ! empty( $site_icon_url ) ) {
			$branding_url = $site_icon_url;
		} elseif ( ! empty( $logo_url ) ) {
			$branding_url = $logo_url;
		} else {
			$branding_url = '';
		}
	}

	restore_current_blog();

	return blogs_directory_cache_set( $cache_key, $branding_url, 15 * MINUTE_IN_SECONDS, 'blogs-directory-branding', 'branding' );
}

/**
 * Liefert den nativen anonymen Default-Avatar als letzten Fallback.
 */
function blogs_directory_get_default_avatar_url( $size ) {
	$size = blogs_directory_map_avatar_size( $size );

	if ( function_exists( 'get_avatar_url' ) ) {
		$avatar_url = get_avatar_url(
			0,
			array(
				'size'          => $size,
				'default'       => 'mystery',
				'force_default' => true,
			)
		);

		if ( ! empty( $avatar_url ) ) {
			return $avatar_url;
		}
	}

	return includes_url( 'images/blank.gif' );
}

/**
 * Rendert das Avatar-HTML fuer die Liste: Blog-Avatar -> Site-Icon/Logo.
 */
function blogs_directory_get_blog_avatar_html( $blog_id, $size, $alt, $fallback_order = 'site_icon_logo' ) {
	$avatar_url = blogs_directory_get_custom_blog_avatar_url( $blog_id, $size );

	if ( empty( $avatar_url ) ) {
		$avatar_url = blogs_directory_get_blog_branding_url( $blog_id, $size, $fallback_order );
	}

	if ( empty( $avatar_url ) ) {
		$avatar_url = blogs_directory_get_default_avatar_url( $size );
	}

	$alt = '' !== $alt ? $alt : __( 'Blog-Avatar', 'blogs-directory' );

	return '<img src="' . esc_url( $avatar_url ) . '" alt="' . esc_attr( $alt ) . '" class="avatar avatar-' . (int) $size . ' photo" height="' . (int) $size . '" width="' . (int) $size . '" />';
}

/**
 * Holt Bewertungsdurchschnitt und Anzahl aus Site Reviews pro Blog.
 */
function blogs_directory_get_site_reviews_summary( $blog_id ) {
	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return array( 'count' => 0, 'average' => 0.0 );
	}

	$cache_key = 'site-reviews:' . $blog_id;
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-reviews', 'reviews' );
	if ( is_array( $cached ) && isset( $cached['count'] ) && isset( $cached['average'] ) ) {
		return $cached;
	}

	switch_to_blog( $blog_id );

	global $wpdb;
	// Direktes SQL – zuverlässig nach switch_to_blog da $wpdb->posts/$wpdb->postmeta aktualisiert werden.
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT p.ID) AS review_count,
			        AVG(CAST(pm.meta_value AS DECIMAL(10,2))) AS review_average
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = %s
			   AND p.post_status = %s
			   AND pm.meta_key = %s
			   AND CAST(pm.meta_value AS DECIMAL(10,2)) BETWEEN 1 AND 5",
			'site-review',
			'publish',
			'rating'
		)
	);

	restore_current_blog();

	$count   = isset( $row->review_count ) ? absint( $row->review_count ) : 0;
	$average = isset( $row->review_average ) ? (float) $row->review_average : 0.0;

	$result = array(
		'count'   => $count,
		'average' => $average,
	);

	$ttl = $count > 0 ? 15 * MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS;

	return blogs_directory_cache_set( $cache_key, $result, $ttl, 'blogs-directory-reviews', 'reviews' );
}

/**
	* Erzeugt einen leichtgewichtigen Auszug ohne the_content-Filter.
	*/
function blogs_directory_get_recent_post_excerpt( $post_id, $content_chars ) {
	$content_chars = absint( $content_chars );
	if ( $content_chars < 1 ) {
		return '';
	}

	$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $post_id ) );
	if ( '' === $excerpt ) {
		$raw = (string) get_post_field( 'post_content', $post_id );
		$raw = preg_replace( '/<!--more[^>]*-->.*$/s', '', $raw );
		$raw = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $raw );
		$raw = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $raw );
		$raw = strip_shortcodes( $raw );
		$excerpt = wp_strip_all_tags( $raw, true );
	}

	$excerpt = trim( preg_replace( '/\s+/u', ' ', $excerpt ) );
	if ( '' === $excerpt ) {
		return '';
	}

	if ( function_exists( 'mb_strimwidth' ) ) {
		return mb_strimwidth( $excerpt, 0, $content_chars, '...' );
	}

	return substr( $excerpt, 0, $content_chars );
}

/**
 * Rendert Bewertungs-Text fuer eine Site-Zeile.
 */
function blogs_directory_get_site_reviews_html( $blog_id, $text_color = '#5A5A5A' ) {
	$summary = blogs_directory_get_site_reviews_summary( $blog_id );

	if ( $summary['count'] < 1 ) {
		return '';
	}

	$rating_text = sprintf(
		/* translators: 1: average rating, 2: number of reviews */
		__( 'Bewertung: %1$s/5 (%2$d)', 'blogs-directory' ),
		number_format_i18n( $summary['average'], 1 ),
		$summary['count']
	);

	return '<span class="blogs_dir_site_reviews" style="font-size: 12px; color: ' . esc_attr( $text_color ) . '; font-weight: 600;">' . esc_html( $rating_text ) . '</span>';
}

/**
 * Rendert aktuelle Beitraege fuer ein Blog innerhalb des Verzeichniseintrags.
 */
function blogs_directory_get_recent_posts_html( $blog_id, array $settings ) {
	if ( empty( $settings['blogs_directory_show_recent_posts'] ) ) {
		return '';
	}

	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return '';
	}

	$post_type = isset( $settings['blogs_directory_recent_posts_post_type'] ) ? sanitize_key( (string) $settings['blogs_directory_recent_posts_post_type'] ) : 'post';
	if ( '' === $post_type ) {
		$post_type = 'post';
	}

	$number = isset( $settings['blogs_directory_recent_posts_number'] ) ? absint( $settings['blogs_directory_recent_posts_number'] ) : 3;
	$number = max( 1, min( 10, $number ) );

	$title_chars = isset( $settings['blogs_directory_recent_posts_title_chars'] ) ? absint( $settings['blogs_directory_recent_posts_title_chars'] ) : 80;
	$title_chars = min( 200, $title_chars );

	$content_chars = isset( $settings['blogs_directory_recent_posts_content_chars'] ) ? absint( $settings['blogs_directory_recent_posts_content_chars'] ) : 0;
	$content_chars = min( 500, $content_chars );

	$show_avatars = ! empty( $settings['blogs_directory_recent_posts_show_avatars'] );
	$avatar_size = isset( $settings['blogs_directory_recent_posts_avatar_size'] ) ? absint( $settings['blogs_directory_recent_posts_avatar_size'] ) : 24;
	$avatar_size = max( 16, min( 96, $avatar_size ) );
	$cache_key = 'recent-posts:' . $blog_id . ':' . md5( wp_json_encode( array( $post_type, $number, $title_chars, $content_chars, $show_avatars, $avatar_size ) ) );
	$cached = blogs_directory_cache_get( $cache_key, 'blogs-directory-recent-posts', 'recent-posts' );
	if ( false !== $cached ) {
		return (string) $cached;
	}

	switch_to_blog( $blog_id );

	$query = new WP_Query(
		array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $number,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		restore_current_blog();
		return blogs_directory_cache_set( $cache_key, '', 5 * MINUTE_IN_SECONDS, 'blogs-directory-recent-posts', 'recent-posts' );
	}

	$html = '<div class="blogs_dir_recent_posts">';
	$html .= '<span class="blogs_dir_recent_posts_title">' . esc_html__( 'hat zuletzt veröffentlicht:', 'blogs-directory' ) . '</span>';
	$html .= '<ul class="blogs_dir_recent_posts_list">';

	while ( $query->have_posts() ) {
		$query->the_post();
		$permalink = get_permalink();

		$title = get_the_title();
		if ( '' === $title ) {
			$title = esc_html__( '(Ohne Titel)', 'blogs-directory' );
		}

		if ( $title_chars > 0 && function_exists( 'mb_strimwidth' ) ) {
			$title = mb_strimwidth( $title, 0, $title_chars, '...' );
		} elseif ( $title_chars > 0 ) {
			$title = substr( $title, 0, $title_chars );
		}

		$item = '<li class="blogs_dir_recent_posts_item">';

		if ( $show_avatars ) {
			$item .= get_avatar( (int) get_the_author_meta( 'ID' ), $avatar_size ) . ' ';
		}

		$item .= '<a href="' . esc_url( $permalink ) . '" class="blogs_dir_recent_posts_link">' . esc_html( $title ) . '</a>';

		if ( $content_chars > 0 ) {
			$excerpt = blogs_directory_get_recent_post_excerpt( get_the_ID(), $content_chars );

			if ( '' !== $excerpt ) {
				$item .= '<br /><span class="blogs_dir_recent_posts_excerpt">' . esc_html( $excerpt ) . '</span>';
			}
		}

		$item .= ' <a href="' . esc_url( $permalink ) . '" class="blogs_dir_recent_posts_more">' . esc_html__( 'Zum vollen Beitrag', 'blogs-directory' ) . '</a>';

		$item .= '</li>';
		$html .= $item;
	}

	$html .= '</ul></div>';

	wp_reset_postdata();
	restore_current_blog();

	return blogs_directory_cache_set( $cache_key, $html, 5 * MINUTE_IN_SECONDS, 'blogs-directory-recent-posts', 'recent-posts' );
}

/**
	* Reagiert auf Post-Aenderungen und invalidiert betroffene Caches.
	*/
function blogs_directory_cache_invalidate_on_post_change( $post_id, $post = null ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		blogs_directory_cache_bust( 'recent-posts' );
		return;
	}

	if ( 'site-review' === $post->post_type ) {
		blogs_directory_cache_bust( 'reviews' );
	}

	blogs_directory_cache_bust( 'recent-posts' );
}

/**
	* Invalidation fuer relevante Option-Updates pro Blog.
	*/
function blogs_directory_cache_invalidate_on_option_update( $option, $old_value, $value ) {
	$option = (string) $option;

	if ( in_array( $option, array( 'blogname', 'blogdescription', 'blog_public' ), true ) ) {
		blogs_directory_cache_bust( 'search' );
		return;
	}

	if ( 'site_icon' === $option || 0 === strpos( $option, 'theme_mods_' ) ) {
		blogs_directory_cache_bust( 'branding' );
	}
}

/**
	* Invalidation fuer Netzwerk-Optionen des Plugins.
	*/
function blogs_directory_cache_invalidate_on_site_option_update( $option, $value, $old_value ) {
	if ( 0 === strpos( (string) $option, 'blogs_directory_' ) ) {
		blogs_directory_cache_bust( 'all' );
	}
}

/**
	* Invalidation bei Site-Lifecycle-Events im Netzwerk.
	*/
function blogs_directory_cache_invalidate_on_site_lifecycle() {
	blogs_directory_cache_bust( 'search' );
	blogs_directory_cache_bust( 'domain-map' );
}

add_action( 'save_post', 'blogs_directory_cache_invalidate_on_post_change', 10, 2 );
add_action( 'deleted_post', 'blogs_directory_cache_invalidate_on_post_change', 10, 2 );
add_action( 'trashed_post', 'blogs_directory_cache_invalidate_on_post_change', 10, 2 );
add_action( 'untrashed_post', 'blogs_directory_cache_invalidate_on_post_change', 10, 2 );
add_action( 'updated_option', 'blogs_directory_cache_invalidate_on_option_update', 10, 3 );
add_action( 'updated_site_option', 'blogs_directory_cache_invalidate_on_site_option_update', 10, 3 );
add_action( 'wpmu_new_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );
add_action( 'archive_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );
add_action( 'unarchive_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );
add_action( 'make_spam_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );
add_action( 'make_ham_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );
add_action( 'delete_blog', 'blogs_directory_cache_invalidate_on_site_lifecycle' );

/**
 * Rendert die Landing-Seite des Blogs-Verzeichnisses.
 */
function blogs_directory_render_landing_content( $content, $blogs_directory, $settings ) {
	global $wpdb, $current_site;
	$layout_mode = blogs_directory_get_layout_mode( $settings );

	$search_form_content = blogs_directory_search_form_output('', $blogs_directory['phrase']);
	$navigation_content = blogs_directory_landing_navigation_output('', $settings['blogs_directory_per_page'], $blogs_directory['page']);
	$content .= $search_form_content;
	$content .= '<br />';
	$content .= $navigation_content;
	$content .= '<div style="float:left; width:100%">';
	if ( 'grid' === $layout_mode ) {
		$content .= '<div class="blogs_directory_grid_wrap">';
		$content .= '<div class="blogs_directory_grid_heading" style="background-color:' . esc_attr( $settings['blogs_directory_background_color'] ) . '; border:1px solid ' . esc_attr( $settings['blogs_directory_border_color'] ) . ';"><h2>' . esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</h2></div>';
		$content .= '<div class="blogs_directory_grid">';
	} else {
		$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_table">';
		$content .= '<tr>';
			$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="10%"> </th>';
			$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="90%"><center><h2>' .  esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</h2></center></th>';
		$content .= '</tr>';
	}
		//=================================//
		$tic_toc = 'toc';
		//=================================//
		if ($blogs_directory['page'] == 1){
			$start = 0;
		} else {
			$math = $blogs_directory['page'] - 1;
			$math = $settings['blogs_directory_per_page'] * $math;
			$start = $math;
		}

		$query = "SELECT * FROM " . $wpdb->base_prefix . "blogs WHERE spam = 0 AND deleted = 0 AND archived = '0'";
		if ( empty( $settings['blogs_directory_include_main_site'] ) ) {
			$query .= $wpdb->prepare( " AND blog_id != %d", (int) $current_site->id );
		}
		if ( isset( $settings['blogs_directory_hide_blogs']['private'] ) && 1 == $settings['blogs_directory_hide_blogs']['private'] ) {
			$query .= " AND public = 1";
		}
		if ( $settings['blogs_directory_sort_by'] == 'alphabetically' ) {
			if ( is_subdomain_install() ) {
				$query .= " ORDER BY domain ASC";
			} else {
				$query .= " ORDER BY path ASC";
			}
		} else if ( $settings['blogs_directory_sort_by'] == 'latest' ) {
			$query .= " ORDER BY blog_id DESC";
		} else {
			$query .= " ORDER BY last_updated DESC";
		}
		$query .= " LIMIT " . intval( $start ) . ", " . intval( $settings['blogs_directory_per_page'] );
		$blogs = $wpdb->get_results( $query, ARRAY_A );
		$blogs = apply_filters( 'blogs_directory_blogs_list', $blogs );
		if ( count($blogs) > 0 ) {
			$domain_map_lookup = blogs_directory_get_domain_map_lookup( wp_list_pluck( $blogs, 'blog_id' ) );
			//=================================//
			foreach ($blogs as $blog){
				if ( isset( $domain_map_lookup[ $blog['blog_id'] ] ) ) {
					$blog['domain'] = $domain_map_lookup[ $blog['blog_id'] ]['domain'];
					$blog['path'] = $domain_map_lookup[ $blog['blog_id'] ]['path'];
				}

                //Hide some blogs
                if ( blogs_directory_hide_some_blogs( $blog['blog_id'] ) )
                    continue;

				//=============================//
				$blog_title         = get_blog_option( $blog['blog_id'], 'blogname', $blog['domain'] . $blog['path'] );
				$safe_blog_title    = esc_html( $blog_title );

				if ($tic_toc == 'toc'){
					$tic_toc = 'tic';
				} else {
					$tic_toc = 'toc';
				}
				$row_palette = blogs_directory_get_row_palette( 'tic' === $tic_toc, $settings );
				//=============================//
				$blog_url = set_url_scheme( 'http://' . $blog['domain'] . $blog['path'] );
				$avatar_html = blogs_directory_get_blog_avatar_html( $blog['blog_id'], 32, $blog_title, $settings['blogs_directory_avatar_fallback_order'] );
				$site_reviews_html = ( ! empty( $settings['blogs_directory_show_site_reviews'] ) ) ? blogs_directory_get_site_reviews_html( $blog['blog_id'], $row_palette['text'] ) : '';
				$blog_description = get_blog_option( $blog['blog_id'], 'blogdescription', $blog['domain'] . $blog['path'] );
				$recent_posts_html = blogs_directory_get_recent_posts_html( $blog['blog_id'], $settings );

				if ( 'grid' === $layout_mode ) {
					$content .= blogs_directory_render_grid_item(
						array(
							'palette'          => $row_palette,
							'border_color'     => $settings['blogs_directory_border_color'],
							'avatar_html'      => $avatar_html,
							'blog_url'         => $blog_url,
							'blog_title'       => $blog_title,
							'blog_description' => $blog_description,
							'show_description' => 1 == $settings['blogs_directory_show_description'],
							'site_reviews_html'=> $site_reviews_html,
							'recent_posts_html'=> $recent_posts_html,
						)
					);
				} else {
					$content .= '<tr>';
						if ( '' !== $avatar_html ) {
							$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette, 'padding-top:10px;' ) . '" valign="top" width="10%"><center><a style="text-decoration:none; color:' . esc_attr( $row_palette['link'] ) . ';" href="' . esc_url( $blog_url ) . '">' . $avatar_html . '</a></center></td>';
						} else {
							$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette, 'padding-top:10px;' ) . '" valign="top" width="10%"></td>';
						}
						$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette ) . '" width="90%">';
						$content .= '<a style="text-decoration:none; font-size:1.5em; margin-left:20px; color:' . esc_attr( $row_palette['title'] ) . ';" href="' . esc_url( $blog_url ) . '">' . $safe_blog_title . '</a><br />';
						if ( '' !== $site_reviews_html ) {
							$content .= $site_reviews_html . '<br />';
						}

                    //show description for blog
                    if ( 1 == $settings['blogs_directory_show_description'] ) {
							$content .= '<span class="blogs_dir_search_blog_description" style="font-size: 12px; color: ' . esc_attr( $row_palette['text'] ) . ';" >' . esc_html( $blog_description ) . '</span>';
                    }

						if ( '' !== $recent_posts_html ) {
							$content .= $recent_posts_html;
						}

						$content .= '</td>';
					$content .= '</tr>';
				}
			}
			//=================================//
		}
	if ( 'grid' === $layout_mode ) {
		$content .= '</div>';
		$content .= '</div>';
	} else {
		$content .= '</table>';
	}
	$content .= '</div>';
	$content .= $navigation_content;

	return $content;
}

/**
 * Rendert die Suchseite des Blogs-Verzeichnisses.
 */
function blogs_directory_render_search_content( $content, $blogs_directory, $settings ) {
	global $current_site;
	$layout_mode = blogs_directory_get_layout_mode( $settings );
	$blogs = blogs_directory_search_blogs_indexed( $blogs_directory['phrase'], $settings );

	$blogs_total = count( $blogs );
	$per_page = max( 1, absint( $settings['blogs_directory_per_page'] ) );
	$current_page = max( 1, absint( $blogs_directory['page'] ) );
	$start_index = ( $current_page - 1 ) * $per_page;
	$blogs = array_slice( $blogs, $start_index, $per_page );

	//=====================================//
	$search_form_content = blogs_directory_search_form_output('', $blogs_directory['phrase']);
	if ( !empty( $blogs ) ) {
		$has_next = $blogs_total > ( $start_index + count( $blogs ) );
		$next = $has_next ? 'yes' : 'no';
		$navigation_content = blogs_directory_search_navigation_output('', $settings['blogs_directory_per_page'], $blogs_directory['page'], $blogs_directory['phrase'], $next);
	}
	$content .= $search_form_content;
	$content .= '<br />';
	if ( !empty( $blogs ) ) {
		$content .= $navigation_content;
	}
	$content .= '<div style="float:left; width:100%">';
	if ( 'grid' === $layout_mode ) {
		$content .= '<div class="blogs_directory_grid_wrap">';
		$content .= '<div class="blogs_directory_grid_heading" style="background-color:' . esc_attr( $settings['blogs_directory_background_color'] ) . '; border:1px solid ' . esc_attr( $settings['blogs_directory_border_color'] ) . ';"><strong>' . esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</strong></div>';
		$content .= '<div class="blogs_directory_grid">';
	} else {
		$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_search_table">';
		$content .= '<tr>';
		$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="10%"> </td>';
		$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="90%"><center><strong>' .  esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</strong></center></td>';
		$content .= '</tr>';
	}
	//=================================//
	$tic_toc = 'toc';
	//=================================//
	if ( !empty( $blogs ) ) {
		foreach ($blogs as $blog){
			$blog_url = set_url_scheme( 'http://' . $blog['domain'] . $blog['path'] );
			$avatar_html = blogs_directory_get_blog_avatar_html( $blog['blog_id'], 32, $blog['blogname'], $settings['blogs_directory_avatar_fallback_order'] );
			$blog_description = isset( $blog['blogdescription'] ) ? $blog['blogdescription'] : '';
			$recent_posts_html = blogs_directory_get_recent_posts_html( $blog['blog_id'], $settings );

			//=============================//
			if ($tic_toc == 'toc'){
				$tic_toc = 'tic';
			} else {
				$tic_toc = 'toc';
			}
			$row_palette = blogs_directory_get_row_palette( 'tic' === $tic_toc, $settings );
			$site_reviews_html = ( ! empty( $settings['blogs_directory_show_site_reviews'] ) ) ? blogs_directory_get_site_reviews_html( $blog['blog_id'], $row_palette['text'] ) : '';
			//=============================//
			if ( 'grid' === $layout_mode ) {
				$content .= blogs_directory_render_grid_item(
					array(
						'palette'          => $row_palette,
						'border_color'     => $settings['blogs_directory_border_color'],
						'avatar_html'      => $avatar_html,
						'blog_url'         => $blog_url,
						'blog_title'       => $blog['blogname'],
						'blog_description' => $blog_description,
						'show_description' => true,
						'site_reviews_html'=> $site_reviews_html,
						'recent_posts_html'=> $recent_posts_html,
					)
				);
			} else {
				$content .= '<tr>';
				if ( '' !== $avatar_html ) {
					$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette, 'padding-top:10px;' ) . '" valign="top" width="10%"><center><a style="text-decoration:none; color:' . esc_attr( $row_palette['link'] ) . ';" href="' . esc_url( $blog_url ) . '">' . $avatar_html . '</a></center></td>';
				} else {
					$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette, 'padding-top:10px;' ) . '" valign="top" width="10%"></td>';
				}
				$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette ) . '" width="90%">';
				$content .= '<a style="text-decoration:none; font-size:1.5em; margin-left:20px; color:' . esc_attr( $row_palette['title'] ) . ';" href="' . esc_url( $blog_url ) . '">' . esc_html( $blog['blogname'] ) . '</a><br />';
				if ( '' !== $site_reviews_html ) {
					$content .= $site_reviews_html . '<br />';
				}
				$content .= '<span class="blogs_dir_search_blog_description" style="font-size: 12px; color: ' . esc_attr( $row_palette['text'] ) . ';" >' . esc_html( $blog_description ) . '</span>';
				if ( '' !== $recent_posts_html ) {
					$content .= $recent_posts_html;
				}
				$content .= '</td>';
				$content .= '</tr>';
			}
		}
	} else {
		$row_palette = blogs_directory_get_row_palette( false, $settings );
		if ( 'grid' === $layout_mode ) {
			$content .= '<div class="blogs_directory_grid_empty" style="' . blogs_directory_get_row_style_attr( $row_palette, 'border:1px solid ' . $settings['blogs_directory_border_color'] . ';border-radius:12px;padding:18px;' ) . '">' . __('Keine Ergebnisse...','blogs-directory') . '</div>';
		} else {
			$content .= '<tr>';
			$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette, 'padding-top:10px;' ) . '" valign="top" width="10%"></td>';
			$content .= '<td style="' . blogs_directory_get_row_style_attr( $row_palette ) . '" width="90%">' . __('Keine Ergebnisse...','blogs-directory') . '</td>';
			$content .= '</tr>';
		}
	}
	//=================================//
	if ( 'grid' === $layout_mode ) {
		$content .= '</div>';
		$content .= '</div>';
	} else {
		$content .= '</table>';
	}
	$content .= '</div>';
	if ( !empty( $blogs ) ) {
		$content .= $navigation_content;
	}

	return $content;
}
