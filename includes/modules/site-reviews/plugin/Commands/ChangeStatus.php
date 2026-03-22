<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Commands;

class ChangeStatus
{
	public $id;
	public $status;

	public function __construct( $input )
	{
		$this->id     = $input['post_id'];
		$this->status = $input['status'] == 'approve'
			? 'publish'
			: 'pending';
	}
}
