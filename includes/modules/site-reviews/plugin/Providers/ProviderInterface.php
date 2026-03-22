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

interface ProviderInterface
{
	/**
	 * @return void
	 */
	public function register( App $app );
}
