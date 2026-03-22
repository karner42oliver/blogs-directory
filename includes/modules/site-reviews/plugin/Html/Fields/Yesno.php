<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Html\Fields;

use PsourceLabs\SiteReviews\Html\Fields\Radio;

class Yesno extends Radio
{
	/**
	 * @return string
	 */
	public function render( array $defaults = [] )
	{
		$this->args['options'] = [
			'no'  => __( 'Nein', 'blogs-directory' ),
			'yes' => __( 'Ja', 'blogs-directory' ),
		];
		return parent::render( wp_parse_args( $defaults, [
			'default' => 'no',
		]));
	}
}
