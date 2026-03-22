<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Providers;

use PsourceLabs\SiteReviews\App;
use PsourceLabs\SiteReviews\Log\Logger;
use PsourceLabs\SiteReviews\Providers\ProviderInterface;

/**
 * Note: We're using the full "namespace\classname" because "::class" isn't supported in PHP 5.4
 */
class MainProvider implements ProviderInterface
{
	public function register( App $app )
	{
		$app->bind( 'PsourceLabs\SiteReviews\App', $app );

		$app->bind( 'PsourceLabs\SiteReviews\Log\Logger', function( $app ) {
			return Logger::file( trailingslashit( $app->path ) . 'debug.log', $app->prefix );
		});

		$app->singleton(
			'PsourceLabs\SiteReviews\Html',
			'PsourceLabs\SiteReviews\Html'
		);

		$app->singleton(
			'PsourceLabs\SiteReviews\Session',
			'PsourceLabs\SiteReviews\Session'
		);

		$app->singleton(
			'PsourceLabs\SiteReviews\Settings',
			'PsourceLabs\SiteReviews\Settings'
		);

		$app->singleton(
			'PsourceLabs\SiteReviews\Translator',
			'PsourceLabs\SiteReviews\Translator'
		);

		// controllers should go last
		$app->singleton(
			'PsourceLabs\SiteReviews\Controllers\AjaxController',
			'PsourceLabs\SiteReviews\Controllers\AjaxController'
		);

		$app->singleton(
			'PsourceLabs\SiteReviews\Controllers\MainController',
			'PsourceLabs\SiteReviews\Controllers\MainController'
		);

		$app->singleton(
			'PsourceLabs\SiteReviews\Controllers\ReviewController',
			'PsourceLabs\SiteReviews\Controllers\ReviewController'
		);
	}
}
