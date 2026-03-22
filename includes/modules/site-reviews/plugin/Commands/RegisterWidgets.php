<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Commands;

class RegisterWidgets
{
	public $widgets;

	public function __construct( $input )
	{
		$this->widgets = (object) $input;
	}
}
