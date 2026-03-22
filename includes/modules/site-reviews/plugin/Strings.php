<?php

/**
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews;

class Strings
{
	/**
	 * @param string|null $key
	 * @param string      $fallback
	 *
	 * @return array|string
	 */
	public function post_type_labels( $key = null, $fallback = ''  )
	{
		return $this->result( $key, $fallback, [
			'add_new_item'          => __( 'Add New Review', 'blogs-directory' ),
			'all_items'             => __( 'All Reviews', 'blogs-directory' ),
			'archives'              => __( 'Review Archives', 'blogs-directory' ),
			'edit_item'             => __( 'Edit Review', 'blogs-directory' ),
			'insert_into_item'      => __( 'Insert into review', 'blogs-directory' ),
			'new_item'              => __( 'New Review', 'blogs-directory' ),
			'not_found'             => __( 'No Reviews found', 'blogs-directory' ),
			'not_found_in_trash'    => __( 'No Reviews found in Trash', 'blogs-directory' ),
			'search_items'          => __( 'Search Reviews', 'blogs-directory' ),
			'uploaded_to_this_item' => __( 'Uploaded to this review', 'blogs-directory' ),
			'view_item'             => __( 'View Review', 'blogs-directory' ),
		]);
	}

	/**
	 * @param string|null $key
	 * @param string      $fallback
	 *
	 * @return array|string
	 */
	public function post_updated_messages( $key = null, $fallback = ''  )
	{
		return $this->result( $key, $fallback, [
			'approved'      => __( 'Review has been approved and published.', 'blogs-directory' ),
			'draft_updated' => __( 'Review draft updated.', 'blogs-directory' ),
			'preview'       => __( 'Preview review', 'blogs-directory' ),
			'published'     => __( 'Review approved and published.', 'blogs-directory' ),
			'restored'      => __( 'Review restored to revision from %s.', 'blogs-directory' ),
			'reverted'      => __( 'Review has been reverted to its original submission state.', 'blogs-directory' ),
			'saved'         => __( 'Review saved.', 'blogs-directory' ),
			'scheduled'     => __( 'Review scheduled for: %s.', 'blogs-directory' ),
			'submitted'     => __( 'Review submitted.', 'blogs-directory' ),
			'unapproved'    => __( 'Review has been unapproved and is now pending.', 'blogs-directory' ),
			'updated'       => __( 'Review updated.', 'blogs-directory' ),
			'view'          => __( 'View review', 'blogs-directory' ),
		]);
	}

	/**
	 * @param string|null $key
	 * @param string      $fallback
	 *
	 * @return array|string
	 *
	 * @since 2.0.0
	 */
	public function review_types( $key = null, $fallback = ''  )
	{
		return $this->result( $key, $fallback, apply_filters( 'site-reviews/addon/types', [
			'local' => __( 'Local', 'blogs-directory' ),
		]));
	}

	/**
	 * @param string|null $key
	 * @param string      $fallback
	 *
	 * @return array|string
	 */
	public function validation( $key = null, $fallback = '' )
	{
		return $this->result( $key, $fallback, [
			'accepted'        => _x( 'The :attribute must be accepted.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'between.numeric' => _x( 'The :attribute must be between :min and :max.', ':attribute, :min, and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'between.string'  => _x( 'The :attribute must be between :min and :max characters.', ':attribute, :min, and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'email'           => _x( 'The :attribute must be a valid email address.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'max.numeric'     => _x( 'The :attribute may not be greater than :max.', ':attribute and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'max.string'      => _x( 'The :attribute may not be greater than :max characters.', ':attribute and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'min.numeric'     => _x( 'The :attribute must be at least :min.', ':attribute and :min are placeholders and should not be translated.', 'blogs-directory' ),
			'min.string'      => _x( 'The :attribute must be at least :min characters.', ':attribute and :min are placeholders and should not be translated.', 'blogs-directory' ),
			'regex'           => _x( 'The :attribute format is invalid.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'required'        => _x( 'The :attribute field is required.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
		]);
	}

	/**
	 * @param string|null $key
	 * @param string      $fallback
	 *
	 * @return array|string
	 *
	 * @since 2.0.0
	 */
	protected function result( $key, $fallback, array $values )
	{
		if( is_string( $key )) {
			return isset( $values[ $key ] )
				? $values[ $key ]
				: $fallback;
		}

		return $values;
	}
}
