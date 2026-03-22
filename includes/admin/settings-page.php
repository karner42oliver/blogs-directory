<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Network admin menu
function blogs_directory_admin_page() {
        add_submenu_page( 'settings.php',  __( 'Blogs-Verzeichnis', 'blogs-directory' ), __( 'Blogs-Verzeichnis', 'blogs-directory' ), 'manage_network_options', 'blog-directory-settings', 'blogs_directory_site_admin_options' );
        // $page = add_submenu_page( 'blog-directory', __( 'Settings', 'blogs-directory' ), __( 'Settings', 'blogs-directory' ), 'manage_network_options', 'blog-directory-settings', 'blogs_directory_site_admin_options' );
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
	?>

    <div class="wrap">
    <?php
    //Display status message
    if ( isset( $_GET['updated'] ) ) {
		switch ($_GET['dmsg']) {
				case 'settings-saved':
						$msg = __( 'Einstellungen gespeichert.', 'blogs-directory' );
						break;
				default:
						$msg = sprintf(__( 'Fehler: %s', 'blogs-directory' ), base64_encode($_GET['dmsg']));;
		}
        ?><div id="message" class="updated fade"><p><?php echo $msg; ?></p></div><?php
    }
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?php _e('Blogs-Verzeichnis Einstellungen','blogs-directory') ?></h2>
        <?php
        $blogs_directory_base = defined('BLOGS_DIRECTORY_SLUG') ? BLOGS_DIRECTORY_SLUG : 'blogs';
        $directory_url = home_url( '/' . $blogs_directory_base . '/' );
        ?>
        <a href="<?php echo esc_url( $directory_url ); ?>" class="button" target="_blank" style="text-decoration: none;">
            <?php _e('Verzeichnis ansehen', 'blogs-directory') ?> ↗
        </a>
    </div>
    <form method="post" name="" >
		<?php wp_nonce_field('save-site-directory', '_wp_nonce', $_SERVER['PHP_SELF']); ?>
		<table class="form-table">
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
                    <th width="33%" scope="row"><?php _e('Titel der Verzeichnis Seite','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_title_blogs_page" type="text" id="blogs_directory_title_blogs_page" value="<?php echo ( isset( $blogs_directory_title_blogs_page ) && '' != $blogs_directory_title_blogs_page ) ? $blogs_directory_title_blogs_page : 'Netzwerkseiten'; ?>" size="20" />
                        <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: "Netzwerkseiten"</span>
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
                    <th width="33%" scope="row"><?php _e('Bewertungen anzeigen','blogs-directory') ?></th>
                    <td>
                        <input name="blogs_directory_show_site_reviews" id="blogs_directory_show_site_reviews" type="checkbox" value="1" <?php checked( (int) $blogs_directory_show_site_reviews, 1 ); ?> />
                        <label for="blogs_directory_show_site_reviews"><?php _e('Zeige Site-Reviews Bewertung in der Verzeichnisliste an','blogs-directory') ?></label><br />
                        <span class="description"><?php _e('Zeigt Durchschnitt und Anzahl veroeffentlichter Bewertungen pro Site, falls vorhanden.','blogs-directory'); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Hintergrundfarbe','blogs-directory') ?></th>
                    <td>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input name="blogs_directory_background_color" type="color" id="blogs_directory_background_color" value="<?php echo esc_attr( $blogs_directory_background_color ); ?>" />
                        <div id="preview_background_color" style="width: 50px; height: 40px; border: 1px solid #ccc; border-radius: 4px; background-color: <?php echo esc_attr( $blogs_directory_background_color ); ?>"></div>
                        <span id="text_background_color" style="font-family: monospace; font-size: 12px;"><?php echo esc_html( $blogs_directory_background_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #F2F2EA</span></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Alternative Hintergrundfarbe','blogs-directory') ?></th>
                    <td>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input name="blogs_directory_alternate_background_color" type="color" id="blogs_directory_alternate_background_color" value="<?php echo esc_attr( $blogs_directory_alternate_background_color ); ?>" />
                        <div id="preview_alternate_background_color" style="width: 50px; height: 40px; border: 1px solid #ccc; border-radius: 4px; background-color: <?php echo esc_attr( $blogs_directory_alternate_background_color ); ?>"></div>
                        <span id="text_alternate_background_color" style="font-family: monospace; font-size: 12px;"><?php echo esc_html( $blogs_directory_alternate_background_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #FFFFFF</span></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><?php _e('Rahmenfarbe','blogs-directory') ?></th>
                    <td>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input name="blogs_directory_border_color" type="color" id="blogs_directory_border_color" value="<?php echo esc_attr( $blogs_directory_border_color ); ?>" />
                        <div id="preview_border_color" style="width: 50px; height: 40px; border: 3px solid <?php echo esc_attr( $blogs_directory_border_color ); ?>; border-radius: 4px; background-color: #fff;"></div>
                        <span id="text_border_color" style="font-family: monospace; font-size: 12px;"><?php echo esc_html( $blogs_directory_border_color ); ?></span>
                    </div>
                    <br /><span class="description"><?php _e('Standard','blogs-directory') ?>: #CFD0CB</span></td>
                </tr>
		    </table>
            <p class="submit">
                <input type="submit" class="button-primary" name="save_settings" value="<?php _e('Änderungen speichern','blogs-directory') ?>" />
            </p>
        </form>
    </div>

	<script>
	(function() {
		const colorInputs = [
			{ input: 'blogs_directory_background_color', preview: 'preview_background_color', text: 'text_background_color' },
			{ input: 'blogs_directory_alternate_background_color', preview: 'preview_alternate_background_color', text: 'text_alternate_background_color' },
			{ input: 'blogs_directory_border_color', preview: 'preview_border_color', text: 'text_border_color' }
		];

		colorInputs.forEach(function(item) {
			const input = document.getElementById(item.input);
			const preview = document.getElementById(item.preview);
			const text = document.getElementById(item.text);

			if (!input || !preview || !text) return;

			input.addEventListener('input', function() {
				const color = this.value;
				text.textContent = color;

				if (item.input === 'blogs_directory_border_color') {
					preview.style.borderColor = color;
				} else {
					preview.style.backgroundColor = color;
				}
			});
		});
	})();
	</script>

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
	update_site_option( 'blogs_directory_show_description', $show_description );
    update_site_option( 'blogs_directory_show_site_reviews', $show_site_reviews );
    update_site_option( 'blogs_directory_avatar_fallback_order', $avatar_fallback_order );
	update_site_option( 'blogs_directory_title_blogs_page', $blogs_directory_title_blogs_page );

	global $wpdb, $blogs_directory_base;
	$page_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_name = '" . $blogs_directory_base . "' AND post_type = 'page'" );

	if ( 1 == $page_count ) {
		$wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->posts . " SET post_title = %s WHERE post_name = %s AND post_type = 'page'", $blogs_directory_title_blogs_page, $blogs_directory_base ) );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'blog-directory-settings', 'updated' => 'true', 'dmsg' => 'settings-saved' ), network_admin_url( 'admin.php' ) ) );
	exit;
}
