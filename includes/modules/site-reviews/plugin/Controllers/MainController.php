<?php

/**
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews\Controllers;

use GeminiLabs\SiteReviews\App;
use GeminiLabs\SiteReviews\Commands\EnqueueAssets;
use GeminiLabs\SiteReviews\Commands\RegisterPointers;
use GeminiLabs\SiteReviews\Commands\RegisterPostType;
use GeminiLabs\SiteReviews\Commands\RegisterShortcodeButtons;
use GeminiLabs\SiteReviews\Commands\RegisterShortcodes;
use GeminiLabs\SiteReviews\Commands\RegisterTaxonomy;
use GeminiLabs\SiteReviews\Commands\RegisterWidgets;
use GeminiLabs\SiteReviews\Controllers\BaseController;
use GeminiLabs\SiteReviews\Strings;
use WP_Admin_Bar;
use WP_Post;

class MainController extends BaseController
{
	/**
	 * @return void
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueueAssets()
	{
		$command = new EnqueueAssets([
			'handle'  => $this->app->id,
			'path'    => $this->app->path . 'assets/',
			'url'     => $this->app->url . 'assets/',
			'version' => $this->app->version,
		]);

		$this->execute( $command );
	}

	/**
	 * @return string
	 *
	 * @filter script_loader_tag
	 */
	public function filterEnqueuedScripts( $tag, $handle )
	{
		return $handle == $this->app->id . '/google-recaptcha'
			&& glsr_get_option( 'reviews-form.recaptcha.integration' ) == 'custom'
			? str_replace( ' src=', ' async defer src=', $tag )
			: $tag;
	}

	/**
	 * Clears the log
	 *
	 * @return void
	 */
	public function postClearLog()
	{
		$this->log->clear();
		$this->notices->addSuccess( __( 'Log was cleared.', 'blogs-directory' ));
	}

	/**
	 * Downloads the log
	 *
	 * @return void
	 */
	public function postDownloadLog()
	{
		$this->log->download();
	}

	/**
	 * Downloads the system info
	 *
	 * @param string $system_info
	 *
	 * @return void
	 */
	public function postDownloadSystemInfo( $system_info )
	{
		$this->app->make( 'SystemInfo' )->download( $system_info );
	}

	/**
	 * Registers the plugin action links on the plugins page
	 *
	 * @return array
	 *
	 * @filter plugin_action_links_reviews/reviews.php
	 */
	public function registerActionLinks( array $links )
	{
		$settings_url = admin_url( 'edit.php?post_type=site-review&page=settings' );

		$links[] = sprintf( '<a href="%s">%s</a>', $settings_url, __( 'Settings', 'blogs-directory' ));

		return $links;
	}

	/**
	 * Adds the reviews count to the "At a Glance" Dashboard widget
	 *
	 * @return array
	 *
	 * @filter dashboard_glance_items
	 */
	public function registerDashboardGlanceItems( array $items )
	{
		$post_type = App::POST_TYPE;
		$num_posts = wp_count_posts( $post_type );

		if( !isset( $num_posts->publish ) || !$num_posts->publish ) {
			return $items;
		}

		$text = _n( '%s Review', '%s Reviews', $num_posts->publish, 'blogs-directory' );
		$text = sprintf( $text, number_format_i18n( $num_posts->publish ));

		$post_type_object = get_post_type_object( $post_type );

		$items[] = $post_type_object && current_user_can( $post_type_object->cap->edit_posts )
			? sprintf( '<a class="glsr-review-count" href="edit.php?post_type=%s">%s</a>', $post_type, $text )
			: sprintf( '<span class="glsr-review-count">%s</span>', $text );

		return $items;
	}

	/**
	 * @return void
	 *
	 * @action admin_menu
	 */
	public function registerMenuCount()
	{
		global $menu, $typenow;

		$post_type = App::POST_TYPE;

		foreach( $menu as $key => $value ) {
			if( !isset( $value[2] ) || $value[2] !== "edit.php?post_type={$post_type}" )continue;

			$postCount = wp_count_posts( $post_type );

			$menu[ $key ][0] .= sprintf( ' <span class="awaiting-mod count-%d"><span class="pending-count">%s</span></span>',
				absint( $postCount->pending ),
				number_format_i18n( $postCount->pending )
			);

			if( $typenow === $post_type ) {
				$menu[ $key ][4] .= ' current';
			}

			break;
		}
	}

	/**
	 * @param string $post_type
	 *
	 * @return void
	 *
	 * @action add_meta_boxes_review
	 */
	public function registerMetaBox( $post_type )
	{
		if( $post_type != App::POST_TYPE )return;

		add_meta_box( "{$this->app->id}_assigned_to", __( 'Assigned To', 'blogs-directory' ), [ $this, 'renderAssignedToMetabox'], null, 'side' );
		add_meta_box( "{$this->app->id}_review", __( 'Details', 'blogs-directory' ), [ $this, 'renderMetaBox'], null, 'side' );
		add_meta_box( "{$this->app->id}_response", __( 'Respond Publicly', 'blogs-directory' ), [ $this, 'renderResponseMetaBox'], null, 'normal' );
	}

	/**
	 * @return void
	 *
	 * @action admin_enqueue_scripts
	 */
	public function registerPointers()
	{
		$command = new RegisterPointers([[
			'id'       => 'glsr-pointer-pinned',
			'screen'   => App::POST_TYPE,
			'target'   => '#misc-pub-pinned',
			'title'    => __( 'Pin Your Reviews', 'blogs-directory' ),
			'content'  => __( 'You can pin exceptional reviews so that they are always shown first in your widgets and shortcodes.', 'blogs-directory' ),
			'position' => [
				'edge'  => 'right',  // top, bottom, left, right
				'align' => 'middle', // top, bottom, left, right, middle
			],
		]]);

		$this->execute( $command );
	}

	/**
	 * @return void
	 *
	 * @action init
	 */
	public function registerPostType()
	{
		if( !$this->app->hasPermission() )return;

		$command = new RegisterPostType([
			'single'      => __( 'Review', 'blogs-directory' ),
			'plural'      => __( 'Reviews', 'blogs-directory' ),
			'menu_name'   => __( 'Site Reviews', 'blogs-directory' ),
			'menu_icon'   => 'dashicons-star-half',
			'public'      => false,
			'has_archive' => false,
			'show_ui'     => true,
			'labels'      => glsr_resolve( 'Strings' )->post_type_labels(),
			'columns'     => [
				'title'       => '', // empty values use the default label
				'category'    => '',
				'assigned_to' => __( 'Assigned To', 'blogs-directory' ),
				'reviewer'    => __( 'Author', 'blogs-directory' ),
				'type'        => __( 'Type', 'blogs-directory' ),
				'stars'       => __( 'Rating', 'blogs-directory' ),
				'sticky'      => __( 'Pinned', 'blogs-directory' ),
				'date'        => '',
			],
		]);

		$this->execute( $command );
	}

	/**
	 * Add Approve/Unapprove links and remove Quick-edit
	 *
	 * @return array
	 *
	 * @filter post_row_actions
	 */
	public function registerRowActions( array $actions, WP_Post $post )
	{
		if( $post->post_type !== App::POST_TYPE || $post->post_status === 'trash' ) {
			return $actions;
		}

		$atts = [
			'approve' => [
				'aria-label' => esc_attr__( 'Approve this review', 'blogs-directory' ),
				'href'       => wp_nonce_url( admin_url( sprintf( 'post.php?post=%s&action=approve', $post->ID )), 'approve-review_' . $post->ID ),
				'text'       => __( 'Approve', 'blogs-directory' ),
			],
			'unapprove' => [
				'aria-label' => esc_attr__( 'Unapprove this review', 'blogs-directory' ),
				'href'       => wp_nonce_url( admin_url( sprintf( 'post.php?post=%s&action=unapprove', $post->ID )), 'unapprove-review_' . $post->ID ),
				'text'       => __( 'Unapprove', 'blogs-directory' ),
			],
		];

		$newActions = [];

		foreach( $atts as $key => $values ) {
			$newActions[ $key ] = sprintf( '<a href="%s" class="change-%s-status" aria-label="%s">%s</a>',
				$values['href'],
				App::POST_TYPE,
				$values['aria-label'],
				$values['text']
			);
		}

		// Remove Quick-edit
		unset( $actions['inline hide-if-no-js'] );

		return $newActions + $actions;
	}

	/**
	 * @return void
	 *
	 * @action admin_init
	 */
	public function registerSettings()
	{
		$optionName = $this->db->getOptionName();

		$settings = apply_filters( 'site-reviews/settings', ['logging', 'settings'] );

		foreach( $settings as $setting ) {
			register_setting( sprintf( '%s-%s', $this->app->id, $setting ), $optionName, [ $this, 'sanitizeSettings' ] );
		}

		$this->app->make( 'Settings' )->register();
	}

	/**
	 * @return void
	 *
	 * @action admin_init
	 */
	public function registerShortcodeButtons()
	{
		$site_reviews = esc_html__( 'Recent Site Reviews', 'blogs-directory' );
		$site_reviews_summary = esc_html__( 'Site Reviews Summary', 'blogs-directory' );
		$site_reviews_form = esc_html__( 'Submit a Site Review', 'blogs-directory' );

		$command = new registerShortcodeButtons([
			'site_reviews' => [
				'title' => $site_reviews,
				'label' => $site_reviews,
			],
			'site_reviews_summary' => [
				'title' => $site_reviews_summary,
				'label' => $site_reviews_summary,
			],
			'site_reviews_form' => [
				'title' => $site_reviews_form,
				'label' => $site_reviews_form,
			],
		]);

		$this->execute( $command );
	}

	/**
	 * @return void
	 *
	 * @action init
	 */
	public function registerShortcodes()
	{
		$command = new RegisterShortcodes([
			'site_reviews',
			'site_reviews_form',
			'site_reviews_summary',
		]);

		$this->execute( $command );
	}

	/**
	 * @return void
	 *
	 * @action admin_menu
	 */
	public function registerSubMenus()
	{
		$pages = [
			'settings' => __( 'Settings', 'blogs-directory' ),
			'help'     => __( 'Get Help', 'blogs-directory' ),
			'addons'   => __( 'Add-Ons', 'blogs-directory' ),
		];

		$pages = apply_filters( 'site-reviews/addon/submenu/pages', $pages );

		foreach( $pages as $slug => $title ) {

			$method = sprintf( 'render%sMenu', ucfirst( $slug ));

			$callback = apply_filters( 'site-reviews/addon/submenu/callback', [ $this, $method ], $slug );

			if( !is_callable( $callback ))continue;

			add_submenu_page( sprintf( 'edit.php?post_type=%s', App::POST_TYPE ), $title, $title, App::CAPABILITY, $slug, $callback );
		}
	}

	/**
	 * @return void
	 *
	 * @action init
	 */
	public function registerTaxonomy()
	{
		$command = new RegisterTaxonomy([
			'hierarchical'      => true,
			'meta_box_cb'       => 'glsr_categories_meta_box',
			'public'            => false,
			'show_admin_column' => true,
			'show_ui'           => true,
		]);

		$this->execute( $command );
	}

	/**
	 * @return void
	 *
	 * @action widgets_init
	 */
	public function registerWidgets()
	{
		$command = new RegisterWidgets([
			'site-reviews' => [
				'title'       => __( 'Recent Site Reviews', 'blogs-directory' ),
				'description' => __( 'Your site’s most recent Local Reviews.', 'blogs-directory' ),
				'class'       => 'glsr-widget glsr-widget-recent-reviews',
			],
			'site-reviews-form' => [
				'title'       => __( 'Submit a Site Review', 'blogs-directory' ),
				'description' => __( 'A "submit a review" form for your site.', 'blogs-directory' ),
				'class'       => 'glsr-widget glsr-widget-reviews-form',
			],
		]);

		$this->execute( $command );
	}

	/**
	 * add_submenu_page() callback
	 *
	 * @return void
	 */
	public function renderAddonsMenu()
	{
		$this->renderMenu( 'addons', [
			'addons' => __( 'Add-Ons', 'blogs-directory' ),
		]);
	}

	/**
	 * register_taxonomy() 'meta_box_cb' callback
	 *
	 * @return void
	 */
	public function renderAssignedToMetabox( $post )
	{
		if( $post->post_type != App::POST_TYPE )return;
		$assignedTo = (int) get_post_meta( $post->ID, 'assigned_to', true );
		$template = '';
		if( $assignedTo && $assignedPost = get_post( $assignedTo )) {
			ob_start();
			$this->renderTemplate( 'edit/assigned-post', [
				'url' => get_permalink( $assignedPost ),
				'title' => get_the_title( $assignedPost ),
			]);
			$template = ob_get_clean();
		}
		wp_nonce_field( 'assigned_to', '_nonce-assigned-to', false );
		$this->render( 'edit/metabox-assigned-to', [
			'id' => $assignedTo,
			'template' => $template,
		]);
	}

	/**
	 * add_submenu_page() callback
	 *
	 * @return void
	 */
	public function renderHelpMenu()
	{
		// allow addons to add their own help sections
		$sections = apply_filters( 'site-reviews/addon/documentation/sections', [
			'support'    => __( 'Support', 'blogs-directory' ),
			'shortcodes' => __( 'Shortcodes', 'blogs-directory' ),
			'hooks'      => __( 'Hooks', 'blogs-directory' ),
			'helpers'    => __( 'Helper Functions', 'blogs-directory' ),
		]);

		$this->renderMenu( 'help', [
			'documentation' => [
				'title'    => __( 'Documentation', 'blogs-directory' ),
				'sections' => $sections,
			],
			'system' => __( 'System Info', 'blogs-directory' ),
		],[
			'system_info' => $this->app->make( 'SystemInfo' ),
		]);
	}

	/**
	 * @return void
	 */
	public function renderMenu( $page, $tabs, $data = [] )
	{
		$tabs    = $this->normalizeTabs( $tabs );
		$tab     = $this->filterTab( $tabs );
		$section = $this->filterSection( $tabs, $tab );

		$defaults = [
			'page'           => $page,
			'tabs'           => $tabs,
			'tabView'        => $tab,
			'tabViewSection' => $section,
		];

		$data = apply_filters( 'site-reviews/addon/menu/data', $data, $defaults );

		$this->render( 'menu/index', wp_parse_args( $data, $defaults ));
	}

	/**
	 * add_meta_box() callback
	 *
	 * @return void
	 */
	public function renderMetaBox( WP_Post $post )
	{
		if( $post->post_type != App::POST_TYPE )return;

		$review = glsr_resolve( 'Helper' )->get( 'review', $post->ID );

		$this->render( 'edit/metabox-details', [
			'button'  => $this->getMetaboxButton( $post, $review ),
			'metabox' => $this->getMetaboxDetails( $review ),
		]);
	}

	/**
	 * @return void
	 * @action post_submitbox_misc_actions
	 */
	public function renderMetaBoxPinned()
	{
		$post = get_post();
		if( !( $post instanceof WP_Post ) || $post->post_type != App::POST_TYPE )return;
		$pinned = get_post_meta( $post->ID, 'pinned', true );
		$this->render( 'edit/pinned', ['pinned' => $pinned ] );
	}

	/**
	 * add_meta_box() callback
	 *
	 * @return void
	 */
	public function renderResponseMetaBox( WP_Post $post )
	{
		if( $post->post_type != App::POST_TYPE )return;

		$review = glsr_resolve( 'Helper' )->get( 'review', $post->ID );

		wp_nonce_field( 'response', '_nonce-response', false );

		$this->render( 'edit/metabox-response', [
			'response' => $review->response,
		]);
	}

	/**
	 * @return void
	 *
	 * @action edit_form_after_title
	 */
	public function renderReview( WP_Post $post )
	{
		if( $post->post_type != App::POST_TYPE )return;
		if( post_type_supports( App::POST_TYPE, 'title' ))return;
		if( get_post_meta( $post->ID, 'review_type', true ) == 'local' )return;

		$this->render( 'edit/review', ['post' => $post ] );
	}

	/**
	 * @return void
	 *
	 * @action edit_form_top
	 */
	public function renderReviewNotice( WP_Post $post )
	{
		if( $post->post_type != App::POST_TYPE )return;
		if( post_type_supports( App::POST_TYPE, 'title' ))return;

		$reviewType = get_post_meta( $post->ID, 'review_type', true );
		if( $reviewType == 'local' )return;
		$this->notices->addWarning( __( 'This review is read-only.', 'blogs-directory' ));
		$this->render( 'edit/notice' );
	}

	/**
	 * @return void
	 *
	 * @action wp_footer
	 */
	public function renderSchema()
	{
		echo $this->app->make( 'Schema' )->render();
	}

	/**
	 * add_submenu_page() callback
	 *
	 * @return void
	 */
	public function renderSettingsMenu()
	{
		// allow addons to add their own setting sections
		$sections = apply_filters( 'site-reviews/addon/settings/sections', [
			'general' => __( 'General', 'blogs-directory' ),
			'reviews' => __( 'Reviews', 'blogs-directory' ),
			'reviews-form' => __( 'Submission Form', 'blogs-directory' ),
			'strings' => __( 'Translations', 'blogs-directory' ),
		]);

		$this->renderMenu( 'settings', [
			'settings' => [
				'title' => __( 'Settings', 'blogs-directory' ),
				'sections' => $sections,
			],
			'licenses' => __( 'Licenses', 'blogs-directory' ),
		],[
			'settings' => $this->app->getDefaultSettings(),
		]);
	}

	/**
	 * register_taxonomy() 'meta_box_cb' callback
	 *
	 * @return void
	 */
	public function renderTaxonomyMetabox( $post, $box )
	{
		if( $post->post_type != App::POST_TYPE )return;

		$taxonomy = isset( $box['args']['taxonomy'] )
			? $box['args']['taxonomy']
			: App::TAXONOMY;

		$this->render( 'edit/metabox-categories', [
			'post'     => $post,
			'tax_name' => esc_attr( $taxonomy ),
			'taxonomy' => get_taxonomy( $taxonomy ),
		]);
	}

	/**
	 * Adds the shortcode button above the TinyMCE Editor on add/edit screens
	 *
	 * @return null|void
	 *
	 * @action media_buttons
	 */
	public function renderTinymceButton()
	{
		if( !glsr_current_screen() || glsr_current_screen()->base != 'post' )return;

		$shortcodes = [];

		foreach( $this->app->mceShortcodes as $shortcode => $values ) {
			if( !apply_filters( sanitize_title( $shortcode ) . '_condition', true ))continue;
			$shortcodes[ $shortcode ] = $values;
		}

		if( empty( $shortcodes ))return;

		$this->render( 'edit/tinymce', ['shortcodes' => $shortcodes ] );
	}

	/**
	 * register_setting() callback
	 * @param array $input
	 * @return array
	 */
	public function sanitizeSettings( $input )
	{
		if( !is_array( $input )) {
			$input = ['settings' => []];
		}

		$key = key( $input );
		$message = '';

		if( $key == 'logging' ) {
			$message = _n( 'Logging disabled.', 'Logging enabled.', (int) empty( $input[$key] ), 'blogs-directory' );
		}
		else if( $key == 'settings' ) {
			$message = __( 'Settings updated.', 'blogs-directory' );
		}

		$message = apply_filters( 'site-reviews/settings/notice', $message, $key );

		if( $message ) {
			$this->notices->addSuccess( $message );
		}

		$options = array_replace_recursive( $this->db->getOptions(), $input );

		if( isset( $input['settings']['reviews-form'] )) {
			$reviewsForm = &$options['settings']['reviews-form'];
			$reviewsForm['required'] = isset( $input['settings']['reviews-form']['required'] )
				? $input['settings']['reviews-form']['required']
				: [];
		}

		if( isset( $input['settings']['strings'] )) {
			$options['settings']['strings'] = array_values( array_filter( $input['settings']['strings'] ));
			array_walk( $options['settings']['strings'], function( &$string ) {
				if( isset( $string['s2'] )) {
					$string['s2'] = wp_strip_all_tags( $string['s2'] );
				}
				if( isset( $string['p2'] )) {
					$string['p2'] = wp_strip_all_tags( $string['p2'] );
				}
			});
		}
		return $options;
	}

	/**
	 * Gets the current menu page tab section
	 *
	 * @param string $tab
	 *
	 * @return string
	 */
	protected function filterSection( array $tabs, $tab )
	{
		$section = filter_input( INPUT_GET, 'section' );

		if( !$section || !isset( $tabs[ $tab ]['sections'][ $section ] )) {
			$section = isset( $tabs[ $tab ]['sections'] )
				? key( $tabs[ $tab ]['sections'] )
				: '';
		}

		return $section;
	}

	/**
	 * Gets the current menu page tab
	 *
	 * @return string
	 */
	protected function filterTab( array $tabs )
	{
		$tab = filter_input( INPUT_GET, 'tab' );

		if( !$tab || !array_key_exists( $tab, $tabs )) {
			$tab = key( $tabs );
		}

		return $tab;
	}

	/**
	 * Gets the metabox revert button
	 *
	 * @param object $review
	 *
	 * @return string
	 */
	protected function getMetaboxButton( WP_Post $post, $review )
	{
		$modified = false;

		if( $post->post_title !== $review->title
			|| $post->post_content !== $review->content
			|| $post->post_date !== $review->date ) {
			$modified = true;
		}

		$revertUrl = wp_nonce_url(
			admin_url( sprintf( 'post.php?post=%s&action=revert', $post->ID )),
			'revert-review_' . $post->ID
		);

		return !$modified
			? sprintf( '<button id="revert" class="button button-large" disabled>%s</button>', __( 'Nothing to Revert', 'blogs-directory' ))
			: sprintf( '<a href="%s" id="revert" class="button button-large">%s</a>', $revertUrl, __( 'Revert Changes', 'blogs-directory' ));
	}

	/**
	 * Gets the metabox details
	 *
	 * @param object $review
	 * @return array
	 */
	protected function getMetaboxDetails( $review )
	{
		$reviewTypeFallback = empty( $review->review_type )
			? __( 'Unknown', 'blogs-directory' )
			: ucfirst( $review->review_type );
		$reviewType = sprintf( __( '%s review', 'blogs-directory' ),
			glsr_resolve( 'Strings' )->review_types( $review->review_type, $reviewTypeFallback )
		);
		if( $review->url ) {
			$reviewType = sprintf( '<a href="%s" target="_blank">%s</a>', $review->url, $reviewType );
		}
		$reviewer = $review->user_id
			? sprintf( '<a href="%s">%s</a>', get_author_posts_url( $review->user_id ), get_the_author_meta( 'display_name', $review->user_id ))
			: __( 'Unregistered user', 'blogs-directory' );
		$email = $review->email
			? sprintf( '<a href="mailto:%1$s?subject=%3$s %2$s">%1$s</a>', $review->email, esc_attr( $review->title ), __( 'RE:', 'blogs-directory' ))
			: '&mdash;';
		$metabox = [
			__( 'Rating', 'blogs-directory' ) => $this->html->renderPartial( 'star-rating', ['rating' => $review->rating] ),
			__( 'Type', 'blogs-directory' ) => $reviewType,
			__( 'Date', 'blogs-directory' ) => get_date_from_gmt( $review->date, 'F j, Y' ),
			__( 'Reviewer', 'blogs-directory' ) => $reviewer,
			__( 'Name', 'blogs-directory' ) => $review->author,
			__( 'Email', 'blogs-directory' ) => $email,
			__( 'IP Address', 'blogs-directory' ) => $review->ip_address,
			__( 'Avatar', 'blogs-directory' ) => sprintf( '<img src="%s" width="96">', $review->avatar ),
		];
		return apply_filters( 'site-reviews/metabox/details', $metabox, $review );
	}

	/**
	 * Normalize the tabs array
	 *
	 * @return array
	 */
	protected function normalizeTabs( array $tabs )
	{
		foreach( $tabs as $key => $value ) {
			if( !is_array( $value )) {
				$tabs[ $key ] = ['title' => $value ];
			}
			if( $key == 'licenses' && !apply_filters( 'site-reviews/addon/licenses', false )) {
				unset( $tabs[ $key ] );
			}
		}

		return $tabs;
	}
}
