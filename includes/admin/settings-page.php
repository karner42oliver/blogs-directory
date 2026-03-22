<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Network admin menu
function blogs_directory_admin_page() {
        add_submenu_page( 'settings.php',  __( 'Blogs-Verzeichnis', 'blogs-directory' ), __( 'Blogs-Verzeichnis', 'blogs-directory' ), 'manage_network_options', 'blog-directory-settings', 'blogs_directory_site_admin_options' );
        // $page = add_submenu_page( 'blog-directory', __( 'Settings', 'blogs-directory' ), __( 'Settings', 'blogs-directory' ), 'manage_network_options', 'blog-directory-settings', 'blogs_directory_site_admin_options' );
}

/**
 * Laedt das moderne Admin-Stylesheet nur auf der Netzwerk-Seite fuer das Blogs-Verzeichnis.
 */
function blogs_directory_settings_enqueue_assets( $hook_suffix ) {
    if ( ! is_network_admin() ) {
        return;
    }

    if ( 'settings_page_blog-directory-settings' !== $hook_suffix ) {
        return;
    }

    $style_rel_path = 'includes/admin/assets/blogs-directory-network-admin.css';
    $style_path = BLOGS_DIRECTORY_PLUGIN_DIR . $style_rel_path;
    $style_url = plugins_url( $style_rel_path, BLOGS_DIRECTORY_PLUGIN_FILE );
    $style_version = file_exists( $style_path ) ? (string) filemtime( $style_path ) : '1.0.0';

    wp_enqueue_style(
        'blogs-directory-network-admin',
        $style_url,
        array(),
        $style_version
    );

    $script_rel_path = 'includes/admin/assets/blogs-directory-network-admin.js';
    $script_path = BLOGS_DIRECTORY_PLUGIN_DIR . $script_rel_path;
    $script_url = plugins_url( $script_rel_path, BLOGS_DIRECTORY_PLUGIN_FILE );
    $script_version = file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0';

    wp_enqueue_script(
        'blogs-directory-network-admin',
        $script_url,
        array(),
        $script_version,
        true
    );
}

function blogs_directory_site_admin_options() {
	$blogs_directory_sort_by                    = get_site_option('blogs_directory_sort_by', 'alphabetically');
	$blogs_directory_per_page                   = get_site_option('blogs_directory_per_page', '10');
	$blogs_directory_background_color           = get_site_option('blogs_directory_background_color', '#F2F2EA');
	$blogs_directory_alternate_background_color = get_site_option('blogs_directory_alternate_background_color', '#FFFFFF');
    $blogs_directory_border_color               = get_site_option('blogs_directory_border_color', '#CFD0CB');
    $blogs_directory_hide_blogs                 = get_site_option('blogs_directory_hide_blogs');
    $blogs_directory_title_blogs_page           = get_site_option('blogs_directory_title_blogs_page');
	$blogs_directory_show_description           = get_site_option('blogs_directory_show_description');
    $blogs_directory_avatar_fallback_order      = get_site_option('blogs_directory_avatar_fallback_order', 'site_icon_logo');
    $blogs_directory_show_site_reviews          = get_site_option('blogs_directory_show_site_reviews', 0);
    $blogs_directory_show_recent_posts          = (int) get_site_option( 'blogs_directory_show_recent_posts', 0 );
    $blogs_directory_recent_posts_number        = (int) get_site_option( 'blogs_directory_recent_posts_number', 3 );
    $blogs_directory_recent_posts_title_chars   = (int) get_site_option( 'blogs_directory_recent_posts_title_chars', 80 );
    $blogs_directory_recent_posts_content_chars = (int) get_site_option( 'blogs_directory_recent_posts_content_chars', 0 );
    $blogs_directory_recent_posts_show_avatars  = (int) get_site_option( 'blogs_directory_recent_posts_show_avatars', 0 );
    $blogs_directory_recent_posts_avatar_size   = (int) get_site_option( 'blogs_directory_recent_posts_avatar_size', 24 );
    $blogs_directory_recent_posts_post_type     = get_site_option( 'blogs_directory_recent_posts_post_type', 'post' );
    $recent_posts_detail_class                  = $blogs_directory_show_recent_posts ? '' : ' bd-hidden-row';
    $blogs_directory_include_main_site          = (int) get_site_option( 'blogs_directory_include_main_site', 1 );
	$blogs_directory_site_reviews_mode          = blogs_directory_get_site_reviews_network_mode();
    $allowed_tabs                                = array( 'general', 'frontend', 'reviews' );
    $tab_raw                                     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
    $active_tab                                  = in_array( $tab_raw, $allowed_tabs, true ) ? $tab_raw : 'general';
	?>

    <div class="wrap bd-modern-admin">
    <?php
    //Display status message
    if ( isset( $_GET['updated'] ) ) {
        $dmsg = isset( $_GET['dmsg'] ) ? sanitize_key( wp_unslash( $_GET['dmsg'] ) ) : '';
        switch ( $dmsg ) {
				case 'settings-saved':
						$msg = __( 'Einstellungen gespeichert.', 'blogs-directory' );
						break;
				default:
                        $msg = __( 'Fehler beim Speichern der Einstellungen.', 'blogs-directory' );
		}
        ?><div id="message" class="notice notice-success is-dismissible"><p><?php echo esc_html( $msg ); ?></p></div><?php
    }
    ?>
    <div class="bd-admin-header">
        <h1 class="bd-page-title"><?php _e('Blogs-Verzeichnis Einstellungen','blogs-directory') ?></h1>
        <?php
        $blogs_directory_base = defined('BLOGS_DIRECTORY_SLUG') ? BLOGS_DIRECTORY_SLUG : 'blogs';
        $directory_url = home_url( '/' . $blogs_directory_base . '/' );
        ?>
        <a href="<?php echo esc_url( $directory_url ); ?>" class="button bd-directory-link" target="_blank" rel="noopener noreferrer">
            <?php _e('Verzeichnis ansehen', 'blogs-directory') ?> ->
        </a>
    </div>

    <nav class="nav-tab-wrapper bd-tab-nav" aria-label="<?php esc_attr_e( 'Einstellungsbereiche', 'blogs-directory' ); ?>">
        <?php
        $tab_urls = array(
            'general'  => add_query_arg( array( 'page' => 'blog-directory-settings', 'tab' => 'general' ), network_admin_url( 'settings.php' ) ),
            'frontend' => add_query_arg( array( 'page' => 'blog-directory-settings', 'tab' => 'frontend' ), network_admin_url( 'settings.php' ) ),
            'reviews'  => add_query_arg( array( 'page' => 'blog-directory-settings', 'tab' => 'reviews' ), network_admin_url( 'settings.php' ) ),
        );
        ?>
        <a class="nav-tab <?php echo ( 'general' === $active_tab ) ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $tab_urls['general'] ); ?>"><?php _e( 'Allgemein', 'blogs-directory' ); ?></a>
        <a class="nav-tab <?php echo ( 'frontend' === $active_tab ) ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $tab_urls['frontend'] ); ?>"><?php _e( 'Frontend', 'blogs-directory' ); ?></a>
        <a class="nav-tab <?php echo ( 'reviews' === $active_tab ) ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $tab_urls['reviews'] ); ?>"><?php _e( 'Bewertungen', 'blogs-directory' ); ?></a>
    </nav>

    <div class="bd-settings-panel" id="bd-settings-panel">
        <form method="post" class="bd-settings-form" >
		<?php wp_nonce_field( 'save-site-directory', '_wp_nonce' ); ?>
		<input type="hidden" name="blogs_directory_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

		<?php if ( 'general' === $active_tab ) : ?>
		<p class="bd-tab-intro"><?php _e( 'Globale Einstellungen fuer Verhalten und Basisdarstellung des Verzeichnisses.', 'blogs-directory' ); ?></p>
		<table class="form-table bd-form-table">
            <tr valign="top">
            <th width="33%" scope="row"><?php _e('Sortiert nach','blogs-directory') ?></th>
            <td>
            <select name="blogs_directory_sort_by" id="blogs_directory_sort_by">
            	<option value="alphabetically" <?php if ( $blogs_directory_sort_by == 'alphabetically' ) { echo 'selected="selected"'; } ?> ><?php _e('Seitenname (A-Z)','blogs-directory'); ?></option>
            	<option value="latest" <?php if ( $blogs_directory_sort_by == 'latest' ) { echo 'selected="selected"'; } ?> ><?php _e('Neueste','blogs-directory'); ?></option>
            	<option value="last_updated" <?php if ( $blogs_directory_sort_by == 'last_updated' ) { echo 'selected="selected"'; } ?> ><?php _e('Zuletzt aktualisiert','blogs-directory'); ?></option>
            </select>
            <br /></td>
            </tr>
            <tr valign="top">
            <th width="33%" scope="row"><?php _e('Auflistung pro Seite','blogs-directory') ?></th>
            <td>
            <select name="blogs_directory_per_page" id="blogs_directory_per_page">
                <option value="5" <?php if ( $blogs_directory_per_page == '5' ) { echo 'selected="selected"'; } ?> ><?php echo '5'; ?></option>
                <option value="10" <?php if ( $blogs_directory_per_page == '10' ) { echo 'selected="selected"'; } ?> ><?php echo '10'; ?></option>
                <option value="15" <?php if ( $blogs_directory_per_page == '15' ) { echo 'selected="selected"'; } ?> ><?php echo '15'; ?></option>
                <option value="20" <?php if ( $blogs_directory_per_page == '20' ) { echo 'selected="selected"'; } ?> ><?php echo '20'; ?></option>
                <option value="25" <?php if ( $blogs_directory_per_page == '25' ) { echo 'selected="selected"'; } ?> ><?php echo '25'; ?></option>
                <option value="30" <?php if ( $blogs_directory_per_page == '30' ) { echo 'selected="selected"'; } ?> ><?php echo '30'; ?></option>
                <option value="35" <?php if ( $blogs_directory_per_page == '35' ) { echo 'selected="selected"'; } ?> ><?php echo '35'; ?></option>
                <option value="40" <?php if ( $blogs_directory_per_page == '40' ) { echo 'selected="selected"'; } ?> ><?php echo '40'; ?></option>
                <option value="45" <?php if ( $blogs_directory_per_page == '45' ) { echo 'selected="selected"'; } ?> ><?php echo '45'; ?></option>
                <option value="50" <?php if ( $blogs_directory_per_page == '50' ) { echo 'selected="selected"'; } ?> ><?php echo '50'; ?></option>
            </select>
            <br /></td>
            </tr>
            <tr valign="top">
            <th width="33%" scope="row"><?php _e('Webseiten ausblenden','blogs-directory') ?></th>
            <td>
            <input name="blogs_directory_hide_blogs[pro_site]" id="blogs_directory_hide_blogs[pro_site]" type="checkbox" value="1" <?php echo ( isset( $blogs_directory_hide_blogs['pro_site'] ) && '1' == $blogs_directory_hide_blogs['pro_site'] ) ? 'checked' : '' ; ?>  />
            <label for="blogs_directory_hide_blogs[pro_site]"><?php _e('Bloghosting Plugin','blogs-directory') ?></label><br />
            <span class="description"><?php _e('(Unbezahlte Blogs ausblenden.)','blogs-directory') ?></span><br />
			<input name="blogs_directory_hide_blogs[private]" id="blogs_directory_hide_blogs[private]" type="checkbox" value="1" <?php echo ( isset( $blogs_directory_hide_blogs['private'] ) && '1' == $blogs_directory_hide_blogs['private'] ) ? 'checked' : '' ; ?>  />
            <label for="blogs_directory_hide_blogs[private]"><?php _e('Privat','blogs-directory') ?></label><br />
            <span class="description"><?php _e('(Blende Blogs aus, die Suchmaschinen blockieren.)','blogs-directory') ?></span><br />
            </td>
            </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Hauptseite anzeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_include_main_site" id="blogs_directory_include_main_site" type="checkbox" value="1" <?php checked( $blogs_directory_include_main_site, 1 ); ?> />
                        <label for="blogs_directory_include_main_site"><?php _e('Zeige die Netzwerk-Hauptseite im Verzeichnis an','blogs-directory') ?></label><br />
                    </td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Titel der Verzeichnis Seite','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_title_blogs_page" type="text" id="blogs_directory_title_blogs_page" value="<?php echo esc_attr( ( isset( $blogs_directory_title_blogs_page ) && '' != $blogs_directory_title_blogs_page ) ? $blogs_directory_title_blogs_page : 'Netzwerkseiten' ); ?>" size="20" />
                        <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: "Netzwerkseiten"</span>
                    </td>
                </tr>
            </table>
		<?php elseif ( 'frontend' === $active_tab ) : ?>
		<p class="bd-tab-intro"><?php _e( 'Optische und inhaltliche Darstellung des Verzeichnisses im Frontend.', 'blogs-directory' ); ?></p>
		<table class="form-table bd-form-table">
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Avatar-Fallback Reihenfolge','blogs-directory') ?></th>
                    <td>
                        <select name="blogs_directory_avatar_fallback_order" id="blogs_directory_avatar_fallback_order">
                            <option value="site_icon_logo" <?php selected( $blogs_directory_avatar_fallback_order, 'site_icon_logo' ); ?>><?php _e('Site-Icon zuerst, dann Logo','blogs-directory'); ?></option>
                            <option value="logo_site_icon" <?php selected( $blogs_directory_avatar_fallback_order, 'logo_site_icon' ); ?>><?php _e('Logo zuerst, dann Site-Icon','blogs-directory'); ?></option>
                        </select>
                        <br /><span class="description"><?php _e('Wenn kein Blog-Avatar gesetzt ist, wird diese Reihenfolge verwendet.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Beschreibung anzeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_show_description" id="blogs_directory_show_description" type="checkbox" value="1" <?php echo ( isset( $blogs_directory_show_description ) && '1' == $blogs_directory_show_description ) ? 'checked' : '' ; ?>  />
                        <label for="blogs_directory_show_description"><?php _e('Zeige die Beschreibung für jede Seite auf der Verzeichnis-Seite an','blogs-directory') ?></label><br />
                    </td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Hintergrundfarbe','blogs-directory') ?></th>
                    <td>
                    <div class="bd-color-control">
                        <input name="blogs_directory_background_color" type="color" id="blogs_directory_background_color" value="<?php echo esc_attr( $blogs_directory_background_color ); ?>" />
                        <div id="preview_background_color" class="bd-color-preview" data-preview-type="background" style="background-color: <?php echo esc_attr( $blogs_directory_background_color ); ?>"></div>
                        <span id="text_background_color" class="bd-color-code"><?php echo esc_html( $blogs_directory_background_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #F2F2EA</span></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Alternative Hintergrundfarbe','blogs-directory') ?></th>
                    <td>
                    <div class="bd-color-control">
                        <input name="blogs_directory_alternate_background_color" type="color" id="blogs_directory_alternate_background_color" value="<?php echo esc_attr( $blogs_directory_alternate_background_color ); ?>" />
                        <div id="preview_alternate_background_color" class="bd-color-preview" data-preview-type="background" style="background-color: <?php echo esc_attr( $blogs_directory_alternate_background_color ); ?>"></div>
                        <span id="text_alternate_background_color" class="bd-color-code"><?php echo esc_html( $blogs_directory_alternate_background_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #FFFFFF</span></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Rahmenfarbe','blogs-directory') ?></th>
                    <td>
                    <div class="bd-color-control">
                        <input name="blogs_directory_border_color" type="color" id="blogs_directory_border_color" value="<?php echo esc_attr( $blogs_directory_border_color ); ?>" />
                        <div id="preview_border_color" class="bd-color-preview is-border" data-preview-type="border" style="border-color: <?php echo esc_attr( $blogs_directory_border_color ); ?>"></div>
                        <span id="text_border_color" class="bd-color-code"><?php echo esc_html( $blogs_directory_border_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #CFD0CB</span></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Netzwerk-Beiträge anzeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_show_recent_posts" id="blogs_directory_show_recent_posts" type="checkbox" value="1" <?php checked( $blogs_directory_show_recent_posts, 1 ); ?> />
                        <label for="blogs_directory_show_recent_posts"><?php _e('Zeige aktuelle Beiträge je Blog im Verzeichnis an','blogs-directory') ?></label><br />
                        <span class="description"><?php _e('Erweitert jeden Verzeichnis-Eintrag um eine Liste aktueller Beiträge aus dem jeweiligen Blog.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Anzahl Beiträge pro Blog','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_number" type="number" min="1" max="10" id="blogs_directory_recent_posts_number" value="<?php echo esc_attr( $blogs_directory_recent_posts_number ); ?>" />
                        <br /><span class="description"><?php _e('Empfohlen: 3 bis 5.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Titel-Länge (Zeichen)','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_title_chars" type="number" min="0" max="200" id="blogs_directory_recent_posts_title_chars" value="<?php echo esc_attr( $blogs_directory_recent_posts_title_chars ); ?>" />
                        <br /><span class="description"><?php _e('0 = voller Titel.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Inhalts-Länge (Zeichen)','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_content_chars" type="number" min="0" max="500" id="blogs_directory_recent_posts_content_chars" value="<?php echo esc_attr( $blogs_directory_recent_posts_content_chars ); ?>" />
                        <br /><span class="description"><?php _e('0 = kein Auszug.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Autor-Avatar zeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_show_avatars" id="blogs_directory_recent_posts_show_avatars" type="checkbox" value="1" <?php checked( $blogs_directory_recent_posts_show_avatars, 1 ); ?> />
                        <label for="blogs_directory_recent_posts_show_avatars"><?php _e('Zeige Avatar vor dem Beitragstitel','blogs-directory') ?></label>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Avatar-Größe','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_avatar_size" type="number" min="16" max="96" id="blogs_directory_recent_posts_avatar_size" value="<?php echo esc_attr( $blogs_directory_recent_posts_avatar_size ); ?>" />
                        <br /><span class="description"><?php _e('Pixel, z.B. 24 oder 32.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top" class="bd-recent-posts-detail-row<?php echo esc_attr( $recent_posts_detail_class ); ?>">
                    <th width="33%" scope="row"><?php _e('Post-Type','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_recent_posts_post_type" type="text" id="blogs_directory_recent_posts_post_type" value="<?php echo esc_attr( $blogs_directory_recent_posts_post_type ); ?>" />
                        <br /><span class="description"><?php _e('Standard: post (z.B. auch news, article usw.).','blogs-directory'); ?></span>
                    </td>
                </tr>
            </table>
        <?php else : ?>
        <p class="bd-tab-intro"><?php _e( 'Steuerung der Site-Reviews Integration und Anzeige von Bewertungsdaten.', 'blogs-directory' ); ?></p>
        <table class="form-table bd-form-table">
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Bewertungen anzeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_show_site_reviews" id="blogs_directory_show_site_reviews" type="checkbox" value="1" <?php checked( (int) $blogs_directory_show_site_reviews, 1 ); ?> />
                        <label for="blogs_directory_show_site_reviews"><?php _e('Zeige Site-Reviews Bewertung in der Verzeichnisliste an','blogs-directory') ?></label><br />
                        <span class="description"><?php _e('Zeigt Durchschnitt und Anzahl veroeffentlichter Bewertungen pro Site, falls vorhanden.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Site Reviews Modulsteuerung','blogs-directory') ?></th>
                    <td>
                        <select name="blogs_directory_site_reviews_mode" id="blogs_directory_site_reviews_mode">
                            <option value="off" <?php selected( $blogs_directory_site_reviews_mode, 'off' ); ?>><?php _e('Aus','blogs-directory'); ?></option>
                            <option value="allow" <?php selected( $blogs_directory_site_reviews_mode, 'allow' ); ?>><?php _e('Netzwerkweit erlauben','blogs-directory'); ?></option>
                            <option value="force" <?php selected( $blogs_directory_site_reviews_mode, 'force' ); ?>><?php _e('Netzwerkweit erzwingen','blogs-directory'); ?></option>
                        </select>
                        <br /><span class="description"><?php _e('Nur bei "Netzwerkweit erlauben" erscheint auf jeder Subsite unter Einstellungen -> Diskussion ein eigener Aktivieren-Schalter.','blogs-directory'); ?></span>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
            <p class="submit bd-submit-row">
                <input type="submit" class="button-primary" name="save_settings" value="<?php _e('Änderungen speichern','blogs-directory') ?>" />
            </p>
        </form>
    </div>
    </div>

	<?php
}

function blogs_directory_save_options() {
	$page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
	if ( 'blog-directory-settings' !== $page || ! isset( $_POST['save_settings'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_network_options' ) ) {
		return;
	}

	$nonce = isset( $_POST['_wp_nonce'] ) ? wp_unslash( $_POST['_wp_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'save-site-directory' ) ) {
		return;
	}

    $allowed_tabs = array( 'general', 'frontend', 'reviews' );
    $active_tab_raw = isset( $_POST['blogs_directory_active_tab'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_active_tab'] ) ) : 'general';
    $active_tab = in_array( $active_tab_raw, $allowed_tabs, true ) ? $active_tab_raw : 'general';

	$allowed_sort_by = array( 'alphabetically', 'latest', 'last_updated' );
	$sort_by_raw = isset( $_POST['blogs_directory_sort_by'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_sort_by'] ) ) : 'alphabetically';
	$sort_by = in_array( $sort_by_raw, $allowed_sort_by, true ) ? $sort_by_raw : 'alphabetically';

	$allowed_per_page = array( '5', '10', '15', '20', '25', '30', '35', '40', '45', '50' );
	$per_page_raw = isset( $_POST['blogs_directory_per_page'] ) ? (string) absint( wp_unslash( $_POST['blogs_directory_per_page'] ) ) : '10';
	$per_page = in_array( $per_page_raw, $allowed_per_page, true ) ? $per_page_raw : '10';

	$background_color = isset( $_POST['blogs_directory_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['blogs_directory_background_color'] ) ) : '';
	$background_color = $background_color ? $background_color : '#F2F2EA';

	$alternate_background_color = isset( $_POST['blogs_directory_alternate_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['blogs_directory_alternate_background_color'] ) ) : '';
	$alternate_background_color = $alternate_background_color ? $alternate_background_color : '#FFFFFF';

	$border_color = isset( $_POST['blogs_directory_border_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['blogs_directory_border_color'] ) ) : '';
	$border_color = $border_color ? $border_color : '#CFD0CB';

	$hide_blogs = array();
	if ( isset( $_POST['blogs_directory_hide_blogs'] ) && is_array( $_POST['blogs_directory_hide_blogs'] ) ) {
		$hide_blogs_raw = wp_unslash( $_POST['blogs_directory_hide_blogs'] );
		$hide_blogs['pro_site'] = ( isset( $hide_blogs_raw['pro_site'] ) && '1' === (string) $hide_blogs_raw['pro_site'] ) ? 1 : 0;
		$hide_blogs['private'] = ( isset( $hide_blogs_raw['private'] ) && '1' === (string) $hide_blogs_raw['private'] ) ? 1 : 0;
	}

	$show_description = isset( $_POST['blogs_directory_show_description'] ) ? 1 : 0;
    $show_site_reviews = isset( $_POST['blogs_directory_show_site_reviews'] ) ? 1 : 0;
    $show_recent_posts = isset( $_POST['blogs_directory_show_recent_posts'] ) ? 1 : 0;
    $recent_posts_number = isset( $_POST['blogs_directory_recent_posts_number'] ) ? absint( wp_unslash( $_POST['blogs_directory_recent_posts_number'] ) ) : 3;
    $recent_posts_number = max( 1, min( 10, $recent_posts_number ) );
    $recent_posts_title_chars = isset( $_POST['blogs_directory_recent_posts_title_chars'] ) ? absint( wp_unslash( $_POST['blogs_directory_recent_posts_title_chars'] ) ) : 80;
    $recent_posts_title_chars = min( 200, $recent_posts_title_chars );
    $recent_posts_content_chars = isset( $_POST['blogs_directory_recent_posts_content_chars'] ) ? absint( wp_unslash( $_POST['blogs_directory_recent_posts_content_chars'] ) ) : 0;
    $recent_posts_content_chars = min( 500, $recent_posts_content_chars );
    $recent_posts_show_avatars = isset( $_POST['blogs_directory_recent_posts_show_avatars'] ) ? 1 : 0;
    $recent_posts_avatar_size = isset( $_POST['blogs_directory_recent_posts_avatar_size'] ) ? absint( wp_unslash( $_POST['blogs_directory_recent_posts_avatar_size'] ) ) : 24;
    $recent_posts_avatar_size = max( 16, min( 96, $recent_posts_avatar_size ) );
    $recent_posts_post_type_raw = isset( $_POST['blogs_directory_recent_posts_post_type'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_recent_posts_post_type'] ) ) : 'post';
    $recent_posts_post_type = '' !== $recent_posts_post_type_raw ? $recent_posts_post_type_raw : 'post';
    $include_main_site = isset( $_POST['blogs_directory_include_main_site'] ) ? 1 : 0;

    $allowed_site_reviews_modes = array( 'off', 'allow', 'force' );
    $site_reviews_mode_raw = isset( $_POST['blogs_directory_site_reviews_mode'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_site_reviews_mode'] ) ) : 'force';
    $site_reviews_mode = in_array( $site_reviews_mode_raw, $allowed_site_reviews_modes, true ) ? $site_reviews_mode_raw : 'force';

    $allowed_fallback_orders = array( 'site_icon_logo', 'logo_site_icon' );
    $fallback_order_raw = isset( $_POST['blogs_directory_avatar_fallback_order'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_avatar_fallback_order'] ) ) : 'site_icon_logo';
    $avatar_fallback_order = in_array( $fallback_order_raw, $allowed_fallback_orders, true ) ? $fallback_order_raw : 'site_icon_logo';

	$title_raw = isset( $_POST['blogs_directory_title_blogs_page'] ) ? sanitize_text_field( wp_unslash( $_POST['blogs_directory_title_blogs_page'] ) ) : '';
	$blogs_directory_title_blogs_page = '' !== $title_raw ? $title_raw : 'Sites';

	update_site_option( 'blogs_directory_sort_by', $sort_by );
	update_site_option( 'blogs_directory_per_page', $per_page );
	update_site_option( 'blogs_directory_background_color', $background_color );
	update_site_option( 'blogs_directory_alternate_background_color', $alternate_background_color );
	update_site_option( 'blogs_directory_border_color', $border_color );
	update_site_option( 'blogs_directory_hide_blogs', $hide_blogs );
    update_site_option( 'blogs_directory_include_main_site', $include_main_site );
	update_site_option( 'blogs_directory_show_description', $show_description );
    update_site_option( 'blogs_directory_show_site_reviews', $show_site_reviews );
    update_site_option( 'blogs_directory_show_recent_posts', $show_recent_posts );
    update_site_option( 'blogs_directory_recent_posts_number', $recent_posts_number );
    update_site_option( 'blogs_directory_recent_posts_title_chars', $recent_posts_title_chars );
    update_site_option( 'blogs_directory_recent_posts_content_chars', $recent_posts_content_chars );
    update_site_option( 'blogs_directory_recent_posts_show_avatars', $recent_posts_show_avatars );
    update_site_option( 'blogs_directory_recent_posts_avatar_size', $recent_posts_avatar_size );
    update_site_option( 'blogs_directory_recent_posts_post_type', $recent_posts_post_type );
    update_site_option( 'blogs_directory_site_reviews_mode', $site_reviews_mode );
    update_site_option( 'blogs_directory_avatar_fallback_order', $avatar_fallback_order );
	update_site_option( 'blogs_directory_title_blogs_page', $blogs_directory_title_blogs_page );

	global $wpdb, $blogs_directory_base;
    $page_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s",
            $blogs_directory_base,
            'page'
        )
    );

	if ( 1 == $page_count ) {
		$wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->posts . " SET post_title = %s WHERE post_name = %s AND post_type = 'page'", $blogs_directory_title_blogs_page, $blogs_directory_base ) );
	}

    wp_safe_redirect( add_query_arg( array( 'page' => 'blog-directory-settings', 'tab' => $active_tab, 'updated' => 'true', 'dmsg' => 'settings-saved' ), network_admin_url( 'settings.php' ) ) );
	exit;
}

/**
 * Sanitize-Callback fuer den Subsite-Schalter auf der Diskussionsseite.
 */
function blogs_directory_sanitize_site_reviews_enabled( $value ) {
    return 1 === (int) $value ? 1 : 0;
}

/**
 * Rendert den Site-Reviews Schalter auf Einstellungen -> Diskussion.
 */
function blogs_directory_render_site_reviews_discussion_field() {
    $enabled = (int) get_option( 'blogs_directory_site_reviews_enabled', 0 );
    ?>
    <label for="blogs_directory_site_reviews_enabled">
        <input type="checkbox" id="blogs_directory_site_reviews_enabled" name="blogs_directory_site_reviews_enabled" value="1" <?php checked( $enabled, 1 ); ?> />
        <?php _e( 'Site Reviews auf dieser Subsite aktivieren', 'blogs-directory' ); ?>
    </label>
    <p class="description"><?php _e( 'Gibt Deinen Besuchern und Usern die Möglichkeit, Bewertungen für diese Webseite abzugeben.', 'blogs-directory' ); ?></p>
    <?php
}

/**
 * Registriert den Subsite-Schalter auf Optionen -> Diskussion.
 */
function blogs_directory_register_site_reviews_discussion_setting() {
    if ( is_network_admin() ) {
        return;
    }

    if ( 'allow' !== blogs_directory_get_site_reviews_network_mode() ) {
        return;
    }

    register_setting(
        'discussion',
        'blogs_directory_site_reviews_enabled',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'blogs_directory_sanitize_site_reviews_enabled',
            'default'           => 0,
        )
    );

    add_settings_field(
        'blogs_directory_site_reviews_enabled',
        __( 'Site Reviews Modul', 'blogs-directory' ),
        'blogs_directory_render_site_reviews_discussion_field',
        'discussion',
        'default'
    );
}
