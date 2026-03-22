<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Handlers;

use PsourceLabs\SiteReviews\Commands\TogglePinned as Command;
use PsourceLabs\SiteReviews\Notices;

class TogglePinned
{
	/**
	 * @var Notices
	 */
	protected $notices;

	public function __construct( Notices $notices )
	{
		$this->notices = $notices;
	}

	/**
	 * @return bool
	 */
	public function handle( Command $command )
	{
		if( !get_post( $command->id )) {
			return false;
		}

		if( is_null( $command->pinned )) {
			$meta = get_post_meta( $command->id, 'pinned', true );
			$command->pinned = !wp_validate_boolean( $meta );
		}
		else {
			$notice = $command->pinned
				? __( 'The review is pinned.', 'blogs-directory' )
				: __( 'The review is unpinned.', 'blogs-directory' );

			$this->notices->addSuccess( $notice );
		}

		update_post_meta( $command->id, 'pinned', $command->pinned );

		return $command->pinned;
	}
}
