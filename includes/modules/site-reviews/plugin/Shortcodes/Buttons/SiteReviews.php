<?php

/**
 * Site Reviews shortcode button
 *
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews\Shortcodes\Buttons;

use GeminiLabs\SiteReviews\Shortcodes\Buttons\Generator;

class SiteReviews extends Generator
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
				'type'    => 'listbox',
				'name'    => 'display',
				'label'   => esc_html__( 'Display', 'blogs-directory' ),
				'options' => $types,
				'tooltip' => __( 'Which reviews would you like to display?', 'blogs-directory' ),
			];
		}

		if( !empty( $terms )) {
			$category = [
				'type'    => 'listbox',
				'name'    => 'category',
				'label'   => esc_html__( 'Category', 'blogs-directory' ),
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
				'type'     => 'textbox',
				'name'     => 'title',
				'label'    => esc_html__( 'Title', 'blogs-directory' ),
				'tooltip'  => __( 'Enter a custom shortcode heading.', 'blogs-directory' ),
			],[
				'type'      => 'textbox',
				'name'      => 'count',
				'maxLength' => 5,
				'size'      => 3,
				'text'      => '10',
				'label'     => esc_html__( 'Count', 'blogs-directory' ),
				'tooltip'   => __( 'How many reviews would you like to display (default: 10)?', 'blogs-directory' ),
			],[
				'type'    => 'listbox',
				'name'    => 'rating',
				'label'   => esc_html__( 'Rating', 'blogs-directory' ),
				'options' => [
					'5' => esc_html__( '5 stars', 'blogs-directory' ),
					'4' => esc_html__( '4 stars', 'blogs-directory' ),
					'3' => esc_html__( '3 stars', 'blogs-directory' ),
					'2' => esc_html__( '2 stars', 'blogs-directory' ),
					'1' => esc_html__( '1 star', 'blogs-directory' ),
				],
				'tooltip' => __( 'What is the minimum rating to display (default: 1 star)?', 'blogs-directory' ),
			],[
				'type'    => 'listbox',
				'name'    => 'pagination',
				'label'   => esc_html__( 'Pagination', 'blogs-directory' ),
				'options' => [
					'true'  => esc_html__( 'Enable', 'blogs-directory' ),
					'ajax' => esc_html__( 'Enable (using ajax)', 'blogs-directory' ),
					'false' => esc_html__( 'Disable', 'blogs-directory' ),
				],
				'tooltip' => __( 'When using pagination this shortcode can only be used once on a page. (default: disable)', 'blogs-directory' ),
			],
			( isset( $display ) ? $display : [] ),
			( isset( $category ) ? $category : [] ),
			[
				'type'      => 'textbox',
				'name'      => 'assigned_to',
				'label'     => esc_html__( 'Post ID', 'blogs-directory' ),
				'tooltip'   => __( 'Limit reviews to those assigned to this post ID (separate multiple IDs with a comma). You can also enter "post_id" to use the ID of the current page.', 'blogs-directory' ),
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
				'type'     => 'textbox',
				'name'     => 'class',
				'label'    => esc_html__( 'Classes', 'blogs-directory' ),
				'tooltip'  => __( 'Add custom CSS classes to the shortcode.', 'blogs-directory' ),
			],[
				'type'    => 'container',
				'label'   => esc_html__( 'Hide', 'blogs-directory' ),
				'layout'  => 'grid',
				'columns' => 2,
				'spacing' => 5,
				'items'   => [
					[
						'type' => 'checkbox',
						'name' => 'hide_author',
						'text' => esc_html__( 'Author', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review author?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_date',
						'text' => esc_html__( 'Date', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review date?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_excerpt',
						'text' => esc_html__( 'Excerpt', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review excerpt?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_rating',
						'text' => esc_html__( 'Rating', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review rating?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_response',
						'text' => esc_html__( 'Response', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review response?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_title',
						'text' => esc_html__( 'Title', 'blogs-directory' ),
						'tooltip' => __( 'Hide the review title?', 'blogs-directory' ),
					],
				],
			],[
				'type'   => 'textbox',
				'name'   => 'id',
				'hidden' => true,
			],
		];
	}
}
