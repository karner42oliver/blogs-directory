<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Commands;

class RegisterTaxonomy
{
	public $args;

	public function __construct( $input )
	{
		$this->args = $input;
	}
}
