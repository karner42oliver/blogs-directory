<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Html\Fields;

use PsourceLabs\SiteReviews\Html\Fields\Text;

class Url extends Text
{
	/**
	 * @return string
	 */
	public function render( array $defaults = [] )
	{
		return parent::render( wp_parse_args( $defaults, [
			'class' => 'regular-text code',
			'type'  => 'url',
		]));
	}
}
