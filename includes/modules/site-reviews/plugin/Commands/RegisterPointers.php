<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Commands;

class RegisterPointers
{
	public $pointers;

	public function __construct( $input )
	{
		$this->pointers = $input;
	}
}
