<?php

/**
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Handlers;

use Exception;
use PsourceLabs\SiteReviews\Commands\RegisterShortcodes as Command;
use ReflectionException;

class RegisterShortcodes
{
	/**
	 * @return void
	 */
	public function handle( Command $command )
	{
		foreach( $command->shortcodes as $key ) {
			try {

				$shortcodeClass = glsr_resolve( 'Helper' )->buildClassName( $key, 'PsourceLabs\SiteReviews\Shortcodes' );

				add_shortcode( $key, [ glsr_resolve( $shortcodeClass ), 'printShortcode'] );
			}
			catch( Exception $e ) {
				glsr_resolve( 'Log\Logger' )->error( sprintf( 'Error registering shortcode. Message: %s "(%s:%s)"',
					$e->getMessage(),
					$e->getFile(),
					$e->getLine()
				));
			}
		}
	}
}
