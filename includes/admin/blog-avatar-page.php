<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Laedt Media-Assets nur auf der Blog-Avatar-Seite.
 */
function blogs_directory_blog_avatar_enqueue_assets( $hook_suffix ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'blogs-directory-blog-avatar' !== $page ) {
		return;
	}

	wp_enqueue_media();
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
	if ( ! in_array( $action, array( 'save', 'reset' ), true ) ) {
		return;
	}
	$result = blogs_directory_process_blog_avatar_action( $action, $_POST, $_FILES );
	$dmsg = $result['success'] ? $result['message_key'] : $result['error_key'];

	wp_safe_redirect( add_query_arg( array( 'page' => 'blogs-directory-blog-avatar', 'updated' => 'true', 'dmsg' => $dmsg ), admin_url( 'options-general.php' ) ) );
	exit;
}

/**
 * Ajax-Endpunkt fuer Inline-Speichern und Reset.
 */
function blogs_directory_blog_avatar_ajax() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Keine Berechtigung.', 'blogs-directory' ) ), 403 );
	}

	$nonce = isset( $_POST['blogs_directory_avatar_ajax_nonce'] ) ? wp_unslash( $_POST['blogs_directory_avatar_ajax_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'blogs-directory-blog-avatar' ) ) {
		wp_send_json_error( array( 'message' => __( 'Sicherheitspruefung fehlgeschlagen.', 'blogs-directory' ) ), 400 );
	}

	$action = isset( $_POST['blogs_directory_blog_avatar_action'] ) ? sanitize_key( wp_unslash( $_POST['blogs_directory_blog_avatar_action'] ) ) : '';
	if ( ! in_array( $action, array( 'save', 'reset' ), true ) ) {
		wp_send_json_error( array( 'message' => __( 'Ungueltige Aktion.', 'blogs-directory' ) ), 400 );
	}

	$result = blogs_directory_process_blog_avatar_action( $action, $_POST, $_FILES );
	if ( ! $result['success'] ) {
		wp_send_json_error(
			array(
				'message' => blogs_directory_get_blog_avatar_message( $result['error_key'] ),
			),
			400
		);
	}

	$blog_id = get_current_blog_id();
	$fallback_order = get_site_option( 'blogs_directory_avatar_fallback_order', 'site_icon_logo' );
	$avatar_url = blogs_directory_get_custom_blog_avatar_url( $blog_id, 96 );
	if ( empty( $avatar_url ) ) {
		$avatar_url = blogs_directory_get_blog_branding_url( $blog_id, 96, $fallback_order );
	}

	wp_send_json_success(
		array(
			'message' => blogs_directory_get_blog_avatar_message( $result['message_key'] ),
			'avatarUrl' => $avatar_url,
		)
	);
}

/**
 * Fuehrt Save/Reset fuer Blog-Avatare aus und liefert strukturiertes Ergebnis.
 */
function blogs_directory_process_blog_avatar_action( $action, array $post_data, array $file_data ) {
	$blog_id = get_current_blog_id();
	$avatar_dir = blogs_directory_get_blog_avatar_dir_path( $blog_id );
	$sizes = array( 16, 32, 48, 96, 128, 192, 256 );

	if ( 'reset' === $action ) {
		blogs_directory_delete_blog_avatar_files( $avatar_dir, $blog_id, $sizes );

		return array(
			'success' => true,
			'message_key' => 'avatar-reset',
		);
	}

	$attachment_id = isset( $post_data['blogs_directory_avatar_attachment_id'] ) ? absint( wp_unslash( $post_data['blogs_directory_avatar_attachment_id'] ) ) : 0;
	$file_path = '';
	$mime_type = '';

	if ( $attachment_id > 0 ) {
		$mime_type = get_post_mime_type( $attachment_id );
		$file_path = get_attached_file( $attachment_id );
	} elseif ( ! empty( $file_data['avatar_file']['tmp_name'] ) && ! empty( $file_data['avatar_file']['name'] ) ) {
		$uploaded_file = $file_data['avatar_file'];
		$wp_filetype = wp_check_filetype_and_ext( $uploaded_file['tmp_name'], $uploaded_file['name'], false );
		$mime_type = isset( $wp_filetype['type'] ) ? $wp_filetype['type'] : '';
		$file_path = $uploaded_file['tmp_name'];
	}

	if ( empty( $file_path ) ) {
		return array( 'success' => false, 'error_key' => 'missing-image' );
	}

	if ( ! wp_match_mime_types( 'image', $mime_type ) || ! is_file( $file_path ) ) {
		return array( 'success' => false, 'error_key' => 'invalid-file' );
	}

	if ( ! wp_mkdir_p( $avatar_dir ) ) {
		return array( 'success' => false, 'error_key' => 'write-error' );
	}

	$image_size = @getimagesize( $file_path );
	if ( empty( $image_size[0] ) || empty( $image_size[1] ) ) {
		return array( 'success' => false, 'error_key' => 'image-error' );
	}

	$original_width = (int) $image_size[0];
	$original_height = (int) $image_size[1];
	$crop_max = max( 1, min( $original_width, $original_height ) );
	$crop_size = isset( $post_data['blogs_directory_avatar_crop_size'] ) ? absint( wp_unslash( $post_data['blogs_directory_avatar_crop_size'] ) ) : $crop_max;
	$crop_size = min( max( 1, $crop_size ), $crop_max );

	$max_x = max( 0, $original_width - $crop_size );
	$max_y = max( 0, $original_height - $crop_size );
	$crop_x = isset( $post_data['blogs_directory_avatar_crop_x'] ) ? absint( wp_unslash( $post_data['blogs_directory_avatar_crop_x'] ) ) : (int) floor( $max_x / 2 );
	$crop_y = isset( $post_data['blogs_directory_avatar_crop_y'] ) ? absint( wp_unslash( $post_data['blogs_directory_avatar_crop_y'] ) ) : (int) floor( $max_y / 2 );
	$crop_x = min( max( 0, $crop_x ), $max_x );
	$crop_y = min( max( 0, $crop_y ), $max_y );

	blogs_directory_delete_blog_avatar_files( $avatar_dir, $blog_id, $sizes );

	foreach ( $sizes as $size ) {
		$image_editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $image_editor ) ) {
			return array( 'success' => false, 'error_key' => 'image-error' );
		}

		$crop_result = $image_editor->crop( $crop_x, $crop_y, $crop_size, $crop_size, $size, $size, false );
		if ( is_wp_error( $crop_result ) ) {
			return array( 'success' => false, 'error_key' => 'image-error' );
		}

		$save_result = $image_editor->save( $avatar_dir . 'blog-' . $blog_id . '-' . $size . '.png', 'image/png' );
		if ( is_wp_error( $save_result ) ) {
			return array( 'success' => false, 'error_key' => 'image-error' );
		}
	}

	return array(
		'success' => true,
		'message_key' => 'avatar-saved',
	);
}

/**
 * Liefert lokalisierte Meldungen fuer Save/Reset.
 */
function blogs_directory_get_blog_avatar_message( $message_key ) {
	$messages = array(
		'avatar-saved' => __( 'Blog-Avatar gespeichert.', 'blogs-directory' ),
		'avatar-reset' => __( 'Blog-Avatar zurueckgesetzt.', 'blogs-directory' ),
		'missing-image' => __( 'Bitte zuerst ein Bild auswaehlen.', 'blogs-directory' ),
		'invalid-file' => __( 'Die hochgeladene Datei ist kein gueltiges Bild.', 'blogs-directory' ),
		'write-error'  => __( 'Avatar-Verzeichnis ist nicht beschreibbar.', 'blogs-directory' ),
		'image-error'  => __( 'Bild konnte nicht verarbeitet werden.', 'blogs-directory' ),
	);

	return isset( $messages[ $message_key ] ) ? $messages[ $message_key ] : __( 'Unbekannter Fehler.', 'blogs-directory' );
}

/**
 * Loescht bestehende Avatar-Dateien in allen unterstuetzten Formaten.
 */
function blogs_directory_delete_blog_avatar_files( $avatar_dir, $blog_id, array $sizes ) {
	$extensions = array( 'png', 'jpg', 'jpeg', 'gif', 'webp' );

	foreach ( $sizes as $size ) {
		foreach ( $extensions as $extension ) {
			$file = $avatar_dir . 'blog-' . $blog_id . '-' . $size . '.' . $extension;
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}
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
		<div id="blogs-directory-avatar-notice-area">
		<?php
		if ( isset( $_GET['updated'] ) ) {
			$dmsg = isset( $_GET['dmsg'] ) ? sanitize_key( wp_unslash( $_GET['dmsg'] ) ) : '';
			if ( $dmsg ) {
				?><div class="updated notice"><p><?php echo esc_html( blogs_directory_get_blog_avatar_message( $dmsg ) ); ?></p></div><?php
			}
		}
		?>
		</div>

		<p><?php _e( 'Nur Blog-Avatar: User-Avatar-Funktionen sind hier bewusst nicht enthalten.', 'blogs-directory' ); ?></p>

		<div class="blogs-directory-avatar-editor" style="max-width: 880px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 24px;">
			<div style="display:grid; grid-template-columns:minmax(280px, 360px) minmax(260px, 1fr); gap:24px; align-items:start;">
				<div>
					<div id="blogs-directory-avatar-stage" style="position:relative; width:320px; max-width:100%; aspect-ratio:1 / 1; border:1px solid #dcdcde; border-radius:10px; overflow:hidden; background:#f6f7f7; display:flex; align-items:center; justify-content:center;">
						<img id="blogs-directory-avatar-source" src="<?php echo esc_url( ! empty( $custom_avatar_url ) ? $custom_avatar_url : $effective_avatar_url ); ?>" alt="" style="max-width:100%; max-height:100%; display:<?php echo ! empty( $effective_avatar_url ) ? 'block' : 'none'; ?>;" />
						<div id="blogs-directory-avatar-empty" style="padding:20px; text-align:center; color:#646970; <?php echo ! empty( $effective_avatar_url ) ? 'display:none;' : ''; ?>">
									<?php _e( 'Noch kein Bild ausgewählt.', 'blogs-directory' ); ?>
						</div>
					</div>
							<p class="description" style="margin-top:12px;"><?php _e( 'Wähle ein Bild aus der Mediathek oder lade eines hoch. Den quadratischen Zuschnitt kannst du direkt unten anpassen.', 'blogs-directory' ); ?></p>
				</div>

				<div>
					<div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
								<button type="button" id="blogs-directory-avatar-select" class="button button-primary"><?php _e( 'Bild wählen', 'blogs-directory' ); ?></button>
								<button type="button" id="blogs-directory-avatar-replace" class="button" style="display:none;"><?php _e( 'Bild ersetzen', 'blogs-directory' ); ?></button>
					</div>

					<div id="blogs-directory-avatar-controls" style="display:none;">
						<label for="blogs-directory-avatar-crop-size" style="display:block; font-weight:600; margin-bottom:6px;"><?php _e( 'Zoom / Ausschnittgröße', 'blogs-directory' ); ?></label>
						<input type="range" id="blogs-directory-avatar-crop-size" min="1" max="100" value="100" style="width:100%; margin-bottom:16px;" />

						<label for="blogs-directory-avatar-crop-x" style="display:block; font-weight:600; margin-bottom:6px;"><?php _e( 'Horizontal verschieben', 'blogs-directory' ); ?></label>
						<input type="range" id="blogs-directory-avatar-crop-x" min="0" max="0" value="0" style="width:100%; margin-bottom:16px;" />

						<label for="blogs-directory-avatar-crop-y" style="display:block; font-weight:600; margin-bottom:6px;"><?php _e( 'Vertikal verschieben', 'blogs-directory' ); ?></label>
						<input type="range" id="blogs-directory-avatar-crop-y" min="0" max="0" value="0" style="width:100%; margin-bottom:18px;" />

						<div>
							<p style="margin:0 0 8px; font-weight:600;"><?php _e( 'Live-Vorschau', 'blogs-directory' ); ?></p>
							<canvas id="blogs-directory-avatar-preview" width="96" height="96" style="width:96px; height:96px; border-radius:10px; border:1px solid #dcdcde; background:#f6f7f7;"></canvas>
						</div>
					</div>

					<div id="blogs-directory-avatar-current" style="margin-top:20px;">
						<p style="margin:0 0 8px; font-weight:600;"><?php _e( 'Aktuell verwendet', 'blogs-directory' ); ?></p>
						<?php if ( ! empty( $effective_avatar_url ) ) : ?>
							<img src="<?php echo esc_url( $effective_avatar_url ); ?>" alt="" class="avatar avatar-96 photo" height="96" width="96" />
						<?php else : ?>
							<span><?php _e( 'Kein Avatar/Logo gefunden.', 'blogs-directory' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<form id="blogs-directory-avatar-form" action="<?php echo esc_url( admin_url( 'options-general.php?page=blogs-directory-blog-avatar' ) ); ?>" method="post" enctype="multipart/form-data" style="margin-top:20px;">
			<?php wp_nonce_field( 'blogs-directory-blog-avatar' ); ?>
			<input type="hidden" name="action" value="blogs_directory_blog_avatar" />
			<input type="hidden" name="blogs_directory_avatar_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'blogs-directory-blog-avatar' ) ); ?>" />
			<input type="hidden" name="blogs_directory_avatar_attachment_id" id="blogs_directory_avatar_attachment_id" value="0" />
			<input type="hidden" name="blogs_directory_avatar_crop_x" id="blogs_directory_avatar_crop_x" value="0" />
			<input type="hidden" name="blogs_directory_avatar_crop_y" id="blogs_directory_avatar_crop_y" value="0" />
			<input type="hidden" name="blogs_directory_avatar_crop_size" id="blogs_directory_avatar_crop_size" value="0" />
			<input type="file" id="blogs-directory-avatar-file" name="avatar_file" accept="image/*" style="display:none;" />
			<p class="submit">
				<button type="submit" id="blogs-directory-avatar-save" name="blogs_directory_blog_avatar_action" value="save" class="button button-primary"><?php _e( 'Avatar speichern', 'blogs-directory' ); ?></button>
				<button type="submit" id="blogs-directory-avatar-reset" name="blogs_directory_blog_avatar_action" value="reset" class="button button-secondary"><?php _e( 'Avatar zuruecksetzen', 'blogs-directory' ); ?></button>
			</p>
		</form>

		<script>
		(function() {
			const ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			const ajaxNonce = <?php echo wp_json_encode( wp_create_nonce( 'blogs-directory-blog-avatar' ) ); ?>;
			const selectButton = document.getElementById('blogs-directory-avatar-select');
			const replaceButton = document.getElementById('blogs-directory-avatar-replace');
			const form = document.getElementById('blogs-directory-avatar-form');
			const fileInput = form ? form.querySelector('#blogs-directory-avatar-file') : null;
			const saveButton = document.getElementById('blogs-directory-avatar-save');
			const resetButton = document.getElementById('blogs-directory-avatar-reset');
			const noticeArea = document.getElementById('blogs-directory-avatar-notice-area');
			const currentWrap = document.getElementById('blogs-directory-avatar-current');
			const stageImage = document.getElementById('blogs-directory-avatar-source');
			const stageEmpty = document.getElementById('blogs-directory-avatar-empty');
			const controls = document.getElementById('blogs-directory-avatar-controls');
			const previewCanvas = document.getElementById('blogs-directory-avatar-preview');
			const previewContext = previewCanvas.getContext('2d');
			const cropSizeSlider = document.getElementById('blogs-directory-avatar-crop-size');
			const cropXSlider = document.getElementById('blogs-directory-avatar-crop-x');
			const cropYSlider = document.getElementById('blogs-directory-avatar-crop-y');
			const attachmentInput = document.getElementById('blogs_directory_avatar_attachment_id');
			const cropXInput = document.getElementById('blogs_directory_avatar_crop_x');
			const cropYInput = document.getElementById('blogs_directory_avatar_crop_y');
			const cropSizeInput = document.getElementById('blogs_directory_avatar_crop_size');

			if (!selectButton || !fileInput || !form || !saveButton || !resetButton || !noticeArea || !currentWrap) {
				return;
			}

			let frame = null;
			let image = null;
			let imageWidth = 0;
			let imageHeight = 0;
			let pendingAction = 'save';

			function renderNotice(message, isError) {
				noticeArea.innerHTML = '<div class="notice ' + (isError ? 'notice-error' : 'updated') + '"><p>' + message + '</p></div>';
			}

			function escapeHtml(value) {
				return String(value)
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;');
			}

			function updateCurrentAvatar(url) {
				if (url) {
					currentWrap.innerHTML = '<p style="margin:0 0 8px; font-weight:600;"><?php echo esc_js( __( 'Aktuell verwendet', 'blogs-directory' ) ); ?></p><img src="' + escapeHtml(url) + '?v=' + Date.now() + '" alt="" class="avatar avatar-96 photo" height="96" width="96" />';
				} else {
					currentWrap.innerHTML = '<p style="margin:0 0 8px; font-weight:600;"><?php echo esc_js( __( 'Aktuell verwendet', 'blogs-directory' ) ); ?></p><span><?php echo esc_js( __( 'Kein Avatar/Logo gefunden.', 'blogs-directory' ) ); ?></span>';
				}
			}

			function setBusy(isBusy) {
				saveButton.disabled = isBusy;
				resetButton.disabled = isBusy;
				selectButton.disabled = isBusy;
				replaceButton.disabled = isBusy;
			}

			function updateHiddenFields() {
				cropXInput.value = cropXSlider.value;
				cropYInput.value = cropYSlider.value;
				cropSizeInput.value = cropSizeSlider.dataset.actualSize || '0';
			}

			function drawPreview() {
				if (!image) {
					previewContext.clearRect(0, 0, 96, 96);
					return;
				}

				const cropSize = parseInt(cropSizeSlider.dataset.actualSize || '0', 10);
				const cropX = parseInt(cropXSlider.value || '0', 10);
				const cropY = parseInt(cropYSlider.value || '0', 10);

				previewContext.clearRect(0, 0, 96, 96);
				previewContext.drawImage(image, cropX, cropY, cropSize, cropSize, 0, 0, 96, 96);
				updateHiddenFields();
			}

			function syncSliderBounds() {
				const minDimension = Math.min(imageWidth, imageHeight);
				const percent = parseInt(cropSizeSlider.value || '100', 10);
				const cropSize = Math.max(1, Math.round(minDimension * (percent / 100)));
				const maxX = Math.max(0, imageWidth - cropSize);
				const maxY = Math.max(0, imageHeight - cropSize);

				cropSizeSlider.dataset.actualSize = String(cropSize);
				cropXSlider.max = String(maxX);
				cropYSlider.max = String(maxY);

				if (parseInt(cropXSlider.value || '0', 10) > maxX) {
					cropXSlider.value = String(maxX);
				}
				if (parseInt(cropYSlider.value || '0', 10) > maxY) {
					cropYSlider.value = String(maxY);
				}
			}

			function centerCrop() {
				const cropSize = parseInt(cropSizeSlider.dataset.actualSize || '0', 10);
				cropXSlider.value = String(Math.max(0, Math.round((imageWidth - cropSize) / 2)));
				cropYSlider.value = String(Math.max(0, Math.round((imageHeight - cropSize) / 2)));
			}

			function loadImage(url, attachmentId, width, height) {
				image = new Image();
				image.onload = function() {
					imageWidth = width || image.naturalWidth;
					imageHeight = height || image.naturalHeight;
					attachmentInput.value = String(attachmentId);
					stageImage.src = url;
					stageImage.style.display = 'block';
					stageEmpty.style.display = 'none';
					controls.style.display = 'block';
					replaceButton.style.display = 'inline-flex';
					cropSizeSlider.value = '100';
					syncSliderBounds();
					centerCrop();
					drawPreview();
				};
				image.src = url;
			}

			function loadLocalFile(file) {
				const reader = new FileReader();
				reader.onload = function(event) {
					loadImage(event.target.result, 0, 0, 0);
				};
				reader.readAsDataURL(file);
			}

			function openFrame() {
				if (typeof wp === 'undefined' || !wp.media) {
					fileInput.click();
					return;
				}

				if (!frame) {
					frame = wp.media({
						title: <?php echo wp_json_encode( __( 'Blog-Avatar wählen', 'blogs-directory' ) ); ?>,
						button: {
							text: <?php echo wp_json_encode( __( 'Dieses Bild verwenden', 'blogs-directory' ) ); ?>
						},
						multiple: false,
						library: {
							type: 'image'
						}
					});

					frame.on('select', function() {
						const attachment = frame.state().get('selection').first().toJSON();
						const full = attachment.sizes && attachment.sizes.full ? attachment.sizes.full : attachment;
						loadImage(full.url, attachment.id, full.width || attachment.width, full.height || attachment.height);
					});
				}

				frame.open();
			}

			selectButton.addEventListener('click', openFrame);
			replaceButton.addEventListener('click', openFrame);
			fileInput.addEventListener('change', function() {
				const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
				if (!file) {
					return;
				}

				attachmentInput.value = '0';
				loadLocalFile(file);
			});

			resetButton.addEventListener('click', function() {
				pendingAction = 'reset';
			});

			saveButton.addEventListener('click', function() {
				pendingAction = 'save';
			});

			form.addEventListener('submit', function(event) {
				event.preventDefault();
				const formData = new FormData(form);
				formData.set('blogs_directory_blog_avatar_action', pendingAction);
				formData.set('blogs_directory_avatar_ajax_nonce', ajaxNonce);

				setBusy(true);
				fetch(ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				})
					.then(function(response) {
						return response.json();
					})
					.then(function(result) {
						if (!result || !result.success) {
							const message = result && result.data && result.data.message ? result.data.message : <?php echo wp_json_encode( __( 'Speichern fehlgeschlagen.', 'blogs-directory' ) ); ?>;
							throw new Error(message);
						}

						renderNotice(result.data.message, false);
						updateCurrentAvatar(result.data.avatarUrl || '');

						if ('reset' === pendingAction) {
							attachmentInput.value = '0';
							fileInput.value = '';
							image = null;
							previewContext.clearRect(0, 0, 96, 96);
							controls.style.display = 'none';
							replaceButton.style.display = 'none';
						}
					})
					.catch(function(error) {
						renderNotice(error.message || <?php echo wp_json_encode( __( 'Speichern fehlgeschlagen.', 'blogs-directory' ) ); ?>, true);
					})
					.finally(function() {
						setBusy(false);
						pendingAction = 'save';
					});
			});

			[cropSizeSlider, cropXSlider, cropYSlider].forEach(function(control) {
				control.addEventListener('input', function() {
					if (!image) {
						return;
					}

					if (control === cropSizeSlider) {
						syncSliderBounds();
					}
					drawPreview();
				});
			});
		})();
		</script>
	</div>
	<?php
}
