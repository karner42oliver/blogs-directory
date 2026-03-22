<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Handlers;

use PsourceLabs\SiteReviews\App;
use PsourceLabs\SiteReviews\Commands\EnqueueAssets as Command;

class EnqueueAssets
{
	/**
	 * @var array
	 */
	protected $dependencies;

	/**
	 * @return void
	 */
	public function handle( Command $command )
	{
		$this->dependencies = glsr_resolve( 'Html' )->getDependencies();
		$ajaxNonce = wp_create_nonce( glsr_app()->id.'-ajax-nonce' );
		$variables = [
			'action'  => glsr_app()->prefix . '_action',
			'ajaxurl' => add_query_arg( '_nonce', $ajaxNonce, admin_url( 'admin-ajax.php' )),
			'ajaxnonce' => $ajaxNonce,
			'ajaxpagination' => ['#wpadminbar','.site-navigation-fixed'],
		];
		if( is_admin() ) {
			$this->enqueueAdmin( $command );
			if( user_can_richedit() ) {
				add_filter( 'mce_external_plugins', [ $this, 'enqueueTinymcePlugins'], 15 );
				$variables = array_merge( $variables, [
					'shortcodes' => $this->localizeShortcodes(),
				]);
			}
		}
		else {
			$this->enqueuePublic( $command );
		}
		wp_localize_script( $command->handle, 'site_reviews', apply_filters( 'site-reviews/enqueue/localize', $variables ));
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueueAdmin( Command $command )
	{
		$screen = glsr_current_screen();
		if( !$this->isSiteReviewsAdminScreen( $screen ) )return;

		$sortableHandle = $command->handle . '-sortable';
		$dependencies = array_merge( $this->dependencies, ['jquery', $sortableHandle, 'underscore', 'wp-util'] );

		wp_enqueue_style(
			$command->handle,
			$command->url . 'css/site-reviews-admin.css',
			[],
			$command->version
		);

		wp_enqueue_style(
			$command->handle . '-modern',
			$command->url . 'css/site-reviews-admin-modern.css',
			[$command->handle],
			$command->version
		);

		wp_enqueue_script(
			$command->handle . '-modern',
			$command->url . 'js/site-reviews-admin-modern.js',
			[],
			$command->version,
			true
		);

		if( !$this->isSiteReviewsPostTypeScreen( $screen ) )return;

		// Avoid JS conflicts on Add New screens where media-grid may initialize before fields exist.
		if(
			$this->isSiteReviewsPostTypeScreen( $screen )
			&& (
				( $this->getScreenBase( $screen ) == 'post' && empty( $_GET['post'] ))
				|| $this->getScreenBase( $screen ) == 'post-new'
			)
		) {
			return;
		}

		wp_enqueue_script(
			$sortableHandle,
			$command->url . 'js/site-reviews-sortable.js',
			['jquery'],
			$command->version,
			true
		);

		wp_enqueue_script(
			$command->handle,
			$command->url . 'js/site-reviews-admin.js',
			$dependencies,
			$command->version,
			true
		);
	}

	/**
	 * Limit admin assets to Site Reviews related screens.
	 *
	 * @param WP_Screen|null $screen
	 *
	 * @return bool
	 */
	protected function isSiteReviewsAdminScreen( $screen )
	{
		if( is_network_admin() ) {
			return false;
		}

		if( $this->isSiteReviewsPostTypeScreen( $screen ) ) {
			return true;
		}

		$screenId = $this->getScreenId( $screen );
		if( strpos( $screenId, App::POST_TYPE . '_page_' ) === 0 ) {
			return true;
		}

		if( $this->isSiteReviewsPostTypeRequest() || $this->isSiteReviewsSubmenuRequest() ) {
			return true;
		}

		$base = $this->getScreenBase( $screen );

		return in_array( $base, ['dashboard', 'widgets'], true );
	}

	/**
	 * @param WP_Screen|null $screen
	 *
	 * @return bool
	 */
	protected function isSiteReviewsPostTypeScreen( $screen )
	{
		if( isset( $screen->post_type ) && $screen->post_type == App::POST_TYPE ) {
			return true;
		}

		return $this->isSiteReviewsPostTypeRequest();
	}

	/**
	 * @return bool
	 */
	protected function isSiteReviewsPostTypeRequest()
	{
		$postType = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

		return $postType === App::POST_TYPE;
	}

	/**
	 * @return bool
	 */
	protected function isSiteReviewsSubmenuRequest()
	{
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		return in_array( $page, ['settings', 'help'], true );
	}

	/**
	 * @param WP_Screen|null $screen
	 *
	 * @return string
	 */
	protected function getScreenId( $screen )
	{
		return isset( $screen->id ) ? (string) $screen->id : '';
	}

	/**
	 * @param WP_Screen|null $screen
	 *
	 * @return string
	 */
	protected function getScreenBase( $screen )
	{
		return isset( $screen->base ) ? (string) $screen->base : '';
	}

	/**
	 * Enqueue public assets
	 *
	 * @return void
	 */
	public function enqueuePublic( Command $command )
	{
		$currentTheme = sanitize_title( (string) wp_get_theme()->get( 'Name' ));

		$stylesheet = file_exists( $command->path . "css/{$currentTheme}.css" )
			? $command->url . "css/{$currentTheme}.css"
			: $command->url . 'css/site-reviews.css';

		if( apply_filters( 'site-reviews/assets/css', true )) {
			wp_enqueue_style(
				$command->handle,
				$stylesheet,
				[],
				$command->version
			);
		}
		if( glsr_get_option( 'reviews-form.recaptcha.integration' ) == 'custom' ) {
			$this->enqueueRecaptchaScript( $command );
		}
		if( apply_filters( 'site-reviews/assets/js', true )) {
			wp_enqueue_script(
				$command->handle,
				$command->url . 'js/site-reviews.js',
				['jquery'],
				$command->version,
				true
			);
		}
	}

	/**
	 * Enqueue custom integration reCAPTCHA script
	 *
	 * @return void
	 */
	public function enqueueRecaptchaScript( Command $command )
	{
		wp_enqueue_script( $command->handle . '/google-recaptcha', add_query_arg([
			'hl' => apply_filters( 'site-reviews/recaptcha/language', get_locale() ),
			'onload' => 'glsr_render_recaptcha',
			'render' => 'explicit',
		], 'https://www.google.com/recaptcha/api.js' ));

		$inlineScript = file_get_contents( sprintf( '%sjs/recaptcha.js', $command->path ));

		wp_add_inline_script( $command->handle . '/google-recaptcha', $inlineScript, 'before' );
	}

	/**
	 * Enqueue TinyMCE plugins
	 *
	 * @return array|null
	 */
	public function enqueueTinymcePlugins( array $plugins )
	{
		if( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ))return;

		$plugins['glsr_shortcode'] = glsr_app()->url . 'assets/js/mce-plugin.js';

		return $plugins;
	}

	/**
	 * @return array
	 */
	protected function localizeShortcodes()
	{
		$variables = [];

		foreach( glsr_app()->mceShortcodes as $tag => $args ) {
			if( !empty( $args['required'] )) {
				$variables[ $tag ] = $args['required'];
			}
		}

		return $variables;
	}
}
