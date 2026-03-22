<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	$border = sanitize_hex_color( get_site_option('blogs_directory_border_color', '#CFD0CB') );

	return array(
		'blogs_directory_sort_by'                    => get_site_option('blogs_directory_sort_by', 'alphabetically'),
		'blogs_directory_per_page'                   => get_site_option('blogs_directory_per_page', '10'),
		'blogs_directory_background_color'           => $background ? $background : '#F2F2EA',
		'blogs_directory_alternate_background_color' => $alternate_background ? $alternate_background : '#FFFFFF',
		'blogs_directory_border_color'               => $border ? $border : '#CFD0CB',
		'blogs_directory_title_blogs_page'           => get_site_option('blogs_directory_title_blogs_page'),
		'blogs_directory_show_description'           => get_site_option('blogs_directory_show_description'),
		'blogs_directory_avatar_fallback_order'      => get_site_option('blogs_directory_avatar_fallback_order', 'site_icon_logo'),
		'blogs_directory_show_site_reviews'          => (int) get_site_option('blogs_directory_show_site_reviews', 0),
		'blogs_directory_include_main_site'          => (int) get_site_option('blogs_directory_include_main_site', 1),
		'blogs_directory_hide_blogs'                 => get_site_option( 'blogs_directory_hide_blogs' ),
	);
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
				return trailingslashit( $upload_dir['baseurl'] ) . 'avatars/blog/' . $folder . '/' . $filename;
			}
		}
	}

	return '';
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

	return $branding_url;
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
	static $runtime_cache = array();

	$blog_id = absint( $blog_id );
	if ( $blog_id < 1 ) {
		return array(
			'count' => 0,
			'average' => 0.0,
		);
	}

	if ( isset( $runtime_cache[ $blog_id ] ) ) {
		return $runtime_cache[ $blog_id ];
	}

	$cache_key = 'blogs_directory_reviews_' . $blog_id;
	$cached = get_site_transient( $cache_key );
	if ( is_array( $cached ) && isset( $cached['count'], $cached['average'] ) ) {
		$runtime_cache[ $blog_id ] = $cached;
		return $cached;
	}

	switch_to_blog( $blog_id );

	global $wpdb;
	$summary = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT COUNT(pm.meta_value) AS review_count, AVG(CAST(pm.meta_value AS DECIMAL(10,2))) AS review_average
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = %s
			   AND p.post_status = %s
			   AND pm.meta_key = %s
			   AND CAST(pm.meta_value AS DECIMAL(10,2)) >= 1
			   AND CAST(pm.meta_value AS DECIMAL(10,2)) <= 5",
			'site-review',
			'publish',
			'rating'
		)
	);

	restore_current_blog();

	$result = array(
		'count' => isset( $summary->review_count ) ? absint( $summary->review_count ) : 0,
		'average' => isset( $summary->review_average ) ? (float) $summary->review_average : 0.0,
	);

	set_site_transient( $cache_key, $result, 15 * MINUTE_IN_SECONDS );
	$runtime_cache[ $blog_id ] = $result;

	return $result;
}

/**
 * Rendert Bewertungs-Text fuer eine Site-Zeile.
 */
function blogs_directory_get_site_reviews_html( $blog_id ) {
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

	return '<span class="blogs_dir_site_reviews" style="font-size: 12px; color: #5A5A5A; font-weight: 600;">' . esc_html( $rating_text ) . '</span>';
}

/**
 * Rendert die Landing-Seite des Blogs-Verzeichnisses.
 */
function blogs_directory_render_landing_content( $content, $blogs_directory, $settings ) {
	global $wpdb, $current_site;

	$search_form_content = blogs_directory_search_form_output('', $blogs_directory['phrase']);
	$navigation_content = blogs_directory_landing_navigation_output('', $settings['blogs_directory_per_page'], $blogs_directory['page']);
	$content .= $search_form_content;
	$content .= '<br />';
	$content .= $navigation_content;
	$content .= '<div style="float:left; width:100%">';
	$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_table">';
		$content .= '<tr>';
			$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="10%"> </th>';
			$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="90%"><center><h2>' .  esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</h2></center></th>';
		$content .= '</tr>';
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
			//=================================//
			foreach ($blogs as $blog){
                if(defined('DOMAINMAP_TABLE_MAP')) {
                    $mapped_url_details = $wpdb->get_row($wpdb->prepare( "SELECT * FROM ".DOMAINMAP_TABLE_MAP." WHERE blog_id = %d ORDER BY id ASC LIMIT 1", $blog['blog_id'] ), ARRAY_A);

                    if($mapped_url_details) {
                        $blog['domain'] = $mapped_url_details['domain'];
                        $blog['path'] = '/';
                    }
                }
                else
                    $mapped_url_details = false;

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
				if ($tic_toc == 'tic'){
					$bg_color = $settings['blogs_directory_alternate_background_color'];
				} else {
					$bg_color = $settings['blogs_directory_background_color'];
				}
				//=============================//
				$blog_url = set_url_scheme( 'http://' . $blog['domain'] . $blog['path'] );
				$avatar_html = blogs_directory_get_blog_avatar_html( $blog['blog_id'], 32, $blog_title, $settings['blogs_directory_avatar_fallback_order'] );
				$site_reviews_html = ( ! empty( $settings['blogs_directory_show_site_reviews'] ) ) ? blogs_directory_get_site_reviews_html( $blog['blog_id'] ) : '';

				$content .= '<tr>';
					if ( '' !== $avatar_html ) {
						$content .= '<td style="background-color:' . $bg_color . '; padding-top:10px;" valign="top" width="10%"><center><a style="text-decoration:none;" href="' . esc_url( $blog_url ) . '">' . $avatar_html . '</a></center></td>';
					} else {
						$content .= '<td style="background-color:' . $bg_color . '; padding-top:10px;" valign="top" width="10%"></td>';
					}
					$content .= '<td style="background-color:' . $bg_color . ';" width="90%">';
					$content .= '<a style="text-decoration:none; font-size:1.5em; margin-left:20px;" href="' . esc_url( $blog_url ) . '">' . $safe_blog_title . '</a><br />';
					if ( '' !== $site_reviews_html ) {
						$content .= $site_reviews_html . '<br />';
					}

                    //show description for blog
                    if ( 1 == $settings['blogs_directory_show_description'] ) {
                        $blogdescription    = get_blog_option( $blog['blog_id'], 'blogdescription', $blog['domain'] . $blog['path'] );
						$content .= '<span class="blogs_dir_search_blog_description" style="font-size: 12px; color: #9D88B0" >' . esc_html( $blogdescription ) . '</span>';
                    }

					$content .= '</td>';
				$content .= '</tr>';
			}
			//=================================//
		}
	$content .= '</table>';
	$content .= '</div>';
	$content .= $navigation_content;

	return $content;
}

/**
 * Rendert die Suchseite des Blogs-Verzeichnisses.
 */
function blogs_directory_render_search_content( $content, $blogs_directory, $settings ) {
	global $wpdb, $current_site;

	if ($blogs_directory['page'] == 1){
		$start = 0;
	} else {
		$math = $blogs_directory['page'] - 1;
		$math = $settings['blogs_directory_per_page'] * $math;
		$start = $math;
	}


    //get all blogs
    $query      = "SELECT * FROM " . $wpdb->base_prefix . "blogs";
	if ( isset( $settings['blogs_directory_hide_blogs']['private'] ) && 1 == $settings['blogs_directory_hide_blogs']['private'] ) {
		$query .= " WHERE spam = 0 AND deleted = 0 AND archived = '0' AND public = 1";
	}
    else
        $query .= " WHERE spam = 0 AND deleted = 0 AND archived = '0'";
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
    $temp_blogs = $wpdb->get_results( $query, ARRAY_A );

	$blogs = array();

    //search by
    if ( !empty( $temp_blogs ) ) {
        foreach ( $temp_blogs as $blog ) {

            //Hide some blogs
            if ( blogs_directory_hide_some_blogs( $blog['blog_id'] ) )
                continue;

			if ( ! empty( $settings['blogs_directory_include_main_site'] ) || (int) $current_site->id !== (int) $blog['blog_id'] ) {
				$search_arr = explode( ' ', $blogs_directory['phrase'] );

				$query      = "SELECT option_name FROM {$wpdb->base_prefix}{$blog['blog_id']}_options WHERE option_name IN ('blogname', 'blogdescription')";
				for ($i=0; $i<count($search_arr); $i++) {
					$query .= $wpdb->prepare( " AND option_value LIKE '%%%s%%'", $search_arr[$i]);
				}
				$found_words = $wpdb->get_results( $query, ARRAY_A );

				if (count($found_words) == 0)
					continue;

            $found_word_name = 0;
			$found_word_description = 0;

			foreach ($found_words as $found_word) {
				if ($found_word['option_name'] == 'blogname') {
					$found_word_name++;
				} else if ($found_word['option_name'] == 'blogdescription') {
					$found_word_description++;
				}
			}

            $blogname           = get_blog_option( $blog['blog_id'], 'blogname', $blog['domain'] . $blog['path'] );
            $blogdescription    = get_blog_option( $blog['blog_id'], 'blogdescription', $blog['domain'] . $blog['path'] );
            $percent            = $found_word_name + $found_word_description;

            if ( 0 < $percent ) {
                $blog['blogname']           = $blogname;
                $blog['blogdescription']    = $blogdescription;
                $blog['percent']            = $percent;
                $blogs[]                    = $blog;
            }
		}
    }

    // sort blogs by percent
	if (count($blogs) > 1) {
    	$fn = function ($a, $b) {
        	if ($a["percent"] == $b["percent"]) {
            	return 0;
        	}
        	return ($a["percent"] > $b["percent"]) ? -1 : 1;
    		};
    		usort($blogs, $fn);
		}
	}

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
	$content .= '<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="" class="blogs_directory_search_table">';
	$content .= '<tr>';
	$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="10%"> </td>';
	$content .= '<th style="background-color:' . $settings['blogs_directory_background_color'] . '; border-bottom-style:solid; border-bottom-color:' . $settings['blogs_directory_border_color'] . '; border-bottom-width:1px; font-size:12px;" width="90%"><center><strong>' .  esc_html( $settings['blogs_directory_title_blogs_page'] ) . '</strong></center></td>';
	$content .= '</tr>';
	//=================================//
	$tic_toc = 'toc';
	//=================================//
	if ( !empty( $blogs ) ) {
		foreach ($blogs as $blog){
			$blog_url = set_url_scheme( 'http://' . $blog['domain'] . $blog['path'] );
			$avatar_html = blogs_directory_get_blog_avatar_html( $blog['blog_id'], 32, $blog['blogname'], $settings['blogs_directory_avatar_fallback_order'] );
			$site_reviews_html = ( ! empty( $settings['blogs_directory_show_site_reviews'] ) ) ? blogs_directory_get_site_reviews_html( $blog['blog_id'] ) : '';

			//=============================//
			if ($tic_toc == 'toc'){
				$tic_toc = 'tic';
			} else {
				$tic_toc = 'toc';
			}
			if ($tic_toc == 'tic'){
				$bg_color = $settings['blogs_directory_alternate_background_color'];
			} else {
				$bg_color = $settings['blogs_directory_background_color'];
			}
			//=============================//
			$content .= '<tr>';
			if ( '' !== $avatar_html ) {
				$content .= '<td style="background-color:' . $bg_color . '; padding-top:10px;" valign="top" width="10%"><center><a style="text-decoration:none;" href="' . esc_url( $blog_url ) . '">' . $avatar_html . '</a></center></td>';
			} else {
				$content .= '<td style="background-color:' . $bg_color . '; padding-top:10px;" valign="top" width="10%"></td>';
			}
			$content .= '<td style="background-color:' . $bg_color . ';" width="90%">';
			$content .= '<a style="text-decoration:none; font-size:1.5em; margin-left:20px;" href="' . esc_url( $blog_url ) . '">' . esc_html( $blog['blogname'] ) . '</a><br />';
			if ( '' !== $site_reviews_html ) {
				$content .= $site_reviews_html . '<br />';
			}
			$content .= '<span class="blogs_dir_search_blog_description" style="font-size: 12px; color: #9D88B0" >' . esc_html( $blog['blogdescription'] ) . '</span>';
			$content .= '</td>';
			$content .= '</tr>';
		}
	} else {
		$content .= '<tr>';
		$content .= '<td style="background-color:' . $settings['blogs_directory_background_color'] . '; padding-top:10px;" valign="top" width="10%"></td>';
		$content .= '<td style="background-color:' . $settings['blogs_directory_background_color'] . ';" width="90%">' . __('Keine Ergebnisse...','blogs-directory') . '</td>';
		$content .= '</tr>';
	}
	//=================================//
	$content .= '</table>';
	$content .= '</div>';
	if ( !empty( $blogs ) ) {
		$content .= $navigation_content;
	}

	return $content;
}
