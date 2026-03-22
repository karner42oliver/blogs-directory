<?php

/**
 * Site Reviews Form shortcode button
 *
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews\Shortcodes\Buttons;

use GeminiLabs\SiteReviews\Shortcodes\Buttons\Generator;

class SiteReviewsForm extends Generator
{
	/**
	 * @return array
	 */
	public function fields()
	{
		$terms = glsr_resolve( 'Database' )->getTerms();

		if( !empty( $terms )) {
			$category = [
				'type'    => 'listbox',
				'name'    => 'category',
				'label'   => esc_html__( 'Category', 'blogs-directory' ),
				'options' => $terms,
				'tooltip' => __( 'Automatically assign a category to reviews submitted with this shortcode.', 'blogs-directory' ),
			];
		}

		return [
			[
				'type' => 'container',
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'All settings are optional.', 'blogs-directory' )),
			],[
				'type'    => 'textbox',
				'name'    => 'title',
				'label'   => esc_html__( 'Title', 'blogs-directory' ),
				'tooltip' => __( 'Enter a custom shortcode heading.', 'blogs-directory' ),
			],[
				'type'    => 'textbox',
				'name'    => 'description',
				'label'   => esc_html__( 'Description', 'blogs-directory' ),
				'tooltip' => __( 'Enter a custom shortcode description.', 'blogs-directory' ),
				'minWidth' => 240,
				'minHeight' => 60,
				'multiline' => true,
			],
			( isset( $category ) ? $category : [] ),
			[
				'type'      => 'textbox',
				'name'      => 'assign_to',
				'label'     => esc_html__( 'Post ID', 'blogs-directory' ),
				'tooltip'   => __( 'Assign submitted reviews to a custom page/post ID. You can also enter "post_id" to assign reviews to the ID of the current page.', 'blogs-directory' ),
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
						'name' => 'hide_email',
						'text' => esc_html__( 'Email', 'blogs-directory' ),
						'tooltip' => __( 'Hide the email field?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_name',
						'text' => esc_html__( 'Name', 'blogs-directory' ),
						'tooltip' => __( 'Hide the name field?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_terms',
						'text' => esc_html__( 'Terms', 'blogs-directory' ),
						'tooltip' => __( 'Hide the terms field?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_title',
						'text' => esc_html__( 'Title', 'blogs-directory' ),
						'tooltip' => __( 'Hide the title field?', 'blogs-directory' ),
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
