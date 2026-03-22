<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registriert eine Blog-Avatar-Seite im jeweiligen Site-Admin.
 */
function blogs_directory_blog_avatar_admin_page() {
	if ( is_network_admin() ) {
		return;
	}

	add_options_page(
		__( 'Blog-Avatar', 'blogs-directory' ),
		__( 'Blog-Avatar', 'blogs-directory' ),
		'manage_options',
		'blogs-directory-blog-avatar',
		'blogs_directory_blog_avatar_page'
	);
}

/**
 * Verarbeitet Upload und Reset fuer den Blog-Avatar.
 */
function blogs_directory_blog_avatar_handle_actions() {
	$page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
	if ( 'blogs-directory-blog-avatar' !== $page || ! isset( $_POST['blogs_directory_blog_avatar_action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nonce = isset( $_POST['_wp_nonce'] ) ? wp_unslash( $_POST['_wp_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'blogs-directory-blog-avatar' ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_POST['blogs_directory_blog_avatar_action'] ) );
	$blog_id = get_current_blog_id();
	$avatar_dir = blogs_directory_get_blog_avatar_dir_path( $blog_id );
	$sizes = array( 16, 32, 48, 96, 128, 192, 256 );

	if ( 'reset' === $action ) {
		foreach ( $sizes as $size ) {
			$file = $avatar_dir . 'blog-' . $blog_id . '-' . $size . '.png';
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'avatar-reset' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	if ( 'upload' !== $action ) {
		return;
	}

	if ( empty( $_FILES['avatar_file']['tmp_name'] ) || empty( $_FILES['avatar_file']['name'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'missing-file' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	$uploaded_file = $_FILES['avatar_file'];
	$wp_filetype = wp_check_filetype_and_ext( $uploaded_file['tmp_name'], $uploaded_file['name'], false );
	if ( ! wp_match_mime_types( 'image', $wp_filetype['type'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'invalid-file' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	if ( ! wp_mkdir_p( $avatar_dir ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'write-error' ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	foreach ( $sizes as $size ) {
		$image_editor = wp_get_image_editor( $uploaded_file['tmp_name'] );
		if ( is_wp_error( $image_editor ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'image-error' ), admin_url( 'options-general.php' ) ) );
			exit;
		}

		$resize_result = $image_editor->resize( $size, $size, true );
		if ( is_wp_error( $resize_result ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'image-error' ), admin_url( 'options-general.php' ) ) );
			exit;
		}

		$save_result = $image_editor->save( $avatar_dir . 'blog-' . $blog_id . '-' . $size . '.png', 'image/png' );
		if ( is_wp_error( $save_result ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'image-error' ), admin_url( 'options-general.php' ) ) );
			exit;
		}
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => 'avatar-saved' ), admin_url( 'options-general.php' ) ) );
	exit;
}

/**
 * Rendert die Blog-Avatar-Seite fuer die aktuelle Site.
 */
function blogs_directory_blog_avatar_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$blog_id = get_current_blog_id();
	$fallback_order = get_site_option( 'blogs_directory_avatar_fallback_order', 'site_icon_logo' );
	$custom_avatar_url = blogs_directory_get_custom_blog_avatar_url( $blog_id, 96 );
	$fallback_branding_url = blogs_directory_get_blog_branding_url( $blog_id, 96, $fallback_order );
	$effective_avatar_url = ! empty( $custom_avatar_url ) ? $custom_avatar_url : $fallback_branding_url;
	?>
	<div class="wrap">
		<h2><?php _e( 'Blog-Avatar', 'blogs-directory' ); ?></h2>
		<?php
		if ( isset( $_GET['updated'] ) ) {
			$dmsg = isset( $_GET['dmsg'] ) ? sanitize_key( wp_unslash( $_GET['dmsg'] ) ) : '';
			$messages = array(
				'avatar-saved' => __( 'Blog-Avatar gespeichert.', 'blogs-directory' ),
				'avatar-reset' => __( 'Blog-Avatar zurueckgesetzt.', 'blogs-directory' ),
				'missing-file' => __( 'Bitte eine Bilddatei auswaehlen.', 'blogs-directory' ),
				'invalid-file' => __( 'Die hochgeladene Datei ist kein gueltiges Bild.', 'blogs-directory' ),
				'write-error'  => __( 'Avatar-Verzeichnis ist nicht beschreibbar.', 'blogs-directory' ),
				'image-error'  => __( 'Bild konnte nicht verarbeitet werden.', 'blogs-directory' ),
			);
			if ( isset( $messages[ $dmsg ] ) ) {
				?><div class="updated notice"><p><?php echo esc_html( $messages[ $dmsg ] ); ?></p></div><?php
			}
		}
		?>

		<p><?php _e( 'Nur Blog-Avatar: User-Avatar-Funktionen sind hier bewusst nicht enthalten.', 'blogs-directory' ); ?></p>

		<p>
			<?php if ( ! empty( $effective_avatar_url ) ) : ?>
				<img src="<?php echo esc_url( $effective_avatar_url ); ?>" alt="" class="avatar avatar-96 photo" height="96" width="96" />
			<?php else : ?>
				<span><?php _e( 'Kein Avatar/Logo gefunden.', 'blogs-directory' ); ?></span>
			<?php endif; ?>
		</p>

		<form action="<?php echo esc_url( admin_url( 'options-general.php?page=blogs-directory-blog-avatar' ) ); ?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'blogs-directory-blog-avatar' ); ?>
			<p>
				<input name="avatar_file" id="avatar_file" type="file" accept="image/*" />
			</p>
			<p class="description"><?php _e( 'Erlaubte Formate: jpeg, png, gif. Das Bild wird automatisch auf Avatar-Groessen zugeschnitten.', 'blogs-directory' ); ?></p>
			<p class="submit">
				<button type="submit" name="blogs_directory_blog_avatar_action" value="upload" class="button button-primary"><?php _e( 'Avatar hochladen', 'blogs-directory' ); ?></button>
				<button type="submit" name="blogs_directory_blog_avatar_action" value="reset" class="button button-secondary"><?php _e( 'Avatar zuruecksetzen', 'blogs-directory' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}
