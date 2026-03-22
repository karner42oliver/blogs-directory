<?php

/**
 * Site Reviews shortcode
 *
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Shortcodes;

use PsourceLabs\SiteReviews\Shortcode;
use PsourceLabs\SiteReviews\Traits\SiteReviews as Common;

class SiteReviews extends Shortcode
{
	use Common;

	/**
	 * @return string
	 */
	public function printShortcode( $atts = [] )
	{
		$args = $this->normalize( $atts, [
			'count' => 10,
			'display' => 'all',
			'offset' => '',
			'pagination' => false,
			'schema' => false,
		]);
		if( $args['assigned_to'] == 'post_id' ) {
			$args['assigned_to'] = intval( get_the_ID() );
		}
		ob_start();
		echo '<div class="shortcode-site-reviews">';
		if( !empty( $args['title'] )) {
			printf( '<h3 class="glsr-shortcode-title">%s</h3>', $args['title'] );
		}
		$this->renderReviews( $args );
		echo '</div>';
		return ob_get_clean();
	}
}
