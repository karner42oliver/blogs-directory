<?php

/**
 * Site Reviews Summary shortcode button
 *
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.3.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews\Shortcodes\Buttons;

use GeminiLabs\SiteReviews\Shortcodes\Buttons\Generator;

class SiteReviewsSummary extends Generator
{
	/**
	 * @return array
	 */
	public function fields()
	{
		$types = glsr_resolve( 'Database' )->getReviewTypes();
		$terms = glsr_resolve( 'Database' )->getTerms();

		if( count( $types ) > 1 ) {
			$display = [
				'type' => 'listbox',
				'name' => 'display',
				'label' => esc_html__( 'Display', 'blogs-directory' ),
				'options' => $types,
				'tooltip' => __( 'Which reviews would you like to display?', 'blogs-directory' ),
			];
		}
		if( !empty( $terms )) {
			$category = [
				'type' => 'listbox',
				'name' => 'category',
				'label' => esc_html__( 'Category', 'blogs-directory' ),
				'options' => $terms,
				'tooltip' => __( 'Limit reviews to this category.', 'blogs-directory' ),
			];
		}
		return [
			[
				'type' => 'container',
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'All settings are optional.', 'blogs-directory' )),
				'minWidth' => 320,
			],[
				'type' => 'textbox',
				'name' => 'title',
				'label' => esc_html__( 'Title', 'blogs-directory' ),
				'tooltip' => __( 'Enter a custom shortcode heading.', 'blogs-directory' ),
			],[
				'type' => 'textbox',
				'name' => 'labels',
				'label' => esc_html__( 'Labels', 'blogs-directory' ),
				'tooltip' => __( 'Enter custom labels for the 1-5 star rating levels (from high to low), and separate each with a comma. The defaults labels are: "Excellent, Very good, Average, Poor, Terrible".', 'blogs-directory' ),
			],[
				'type' => 'listbox',
				'name' => 'rating',
				'label' => esc_html__( 'Rating', 'blogs-directory' ),
				'options' => [
					'5' => esc_html( sprintf( _n( '%s star', '%s stars', 5, 'blogs-directory' ), 5 )),
					'4' => esc_html( sprintf( _n( '%s star', '%s stars', 4, 'blogs-directory' ), 4 )),
					'3' => esc_html( sprintf( _n( '%s star', '%s stars', 3, 'blogs-directory' ), 3 )),
					'2' => esc_html( sprintf( _n( '%s star', '%s stars', 2, 'blogs-directory' ), 2 )),
					'1' => esc_html( sprintf( _n( '%s star', '%s stars', 1, 'blogs-directory' ), 1 )),
				],
				'tooltip' => __( 'What is the minimum rating? (default: 1 star)', 'blogs-directory' ),
			],
			( isset( $display ) ? $display : [] ),
			( isset( $category ) ? $category : [] ),
			[
				'type' => 'textbox',
				'name' => 'assigned_to',
				'label' => esc_html__( 'Post ID', 'blogs-directory' ),
				'tooltip' => __( "Limit reviews to those assigned to this post ID (separate multiple ID's with a comma). You can also enter 'post_id' to use the ID of the current page.", 'blogs-directory' ),
			],[
				'type' => 'listbox',
				'name' => 'schema',
				'label' => esc_html__( 'Schema', 'blogs-directory' ),
				'options' => [
					'true' => esc_html__( 'Enable rich snippets', 'blogs-directory' ),
					'false' => esc_html__( 'Disable rich snippets', 'blogs-directory' ),
				],
				'tooltip' => __( 'Rich snippets are disabled by default.', 'blogs-directory' ),
			],[
				'type' => 'textbox',
				'name' => 'class',
				'label' => esc_html__( 'Classes', 'blogs-directory' ),
				'tooltip' => __( 'Add custom CSS classes to the shortcode.', 'blogs-directory' ),
			],[
				'type' => 'container',
				'label' => esc_html__( 'Hide', 'blogs-directory' ),
				'layout' => 'grid',
				'columns' => 2,
				'spacing' => 5,
				'items' => [
					[
						'type' => 'checkbox',
						'name' => 'hide_bars',
						'text' => esc_html__( 'Bars', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Hide the percentage bars?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_rating',
						'text' => esc_html__( 'Rating', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Hide the rating?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_stars',
						'text' => esc_html__( 'Stars', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Hide the stars?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_summary',
						'text' => esc_html__( 'Summary', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Hide the summary text?', 'blogs-directory' ),
					],
				],
			],
		];
	}
}
