<?php

/**
 * Site Reviews widget
 *
 * @package   GeminiLabs\SiteReviews
 * @copyright Copyright (c) 2016, Paul Ryley
 * @license   GPLv3
 * @since     1.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace GeminiLabs\SiteReviews\Widgets;

use GeminiLabs\SiteReviews\Traits\SiteReviews as Common;
use GeminiLabs\SiteReviews\Widget;

class SiteReviews extends Widget
{
	use Common;

	/**
	 * Display the widget form
	 *
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance )
	{
		$args = $this->normalize( $instance );
		$types = glsr_resolve( 'Database' )->getReviewTypes();
		$terms = glsr_resolve( 'Database' )->getTerms();

		$this->create_field([
			'type'  => 'text',
			'name'  => 'title',
			'label' => __( 'Titel', 'blogs-directory' ),
			'value' => $args['title'],
		]);

		$this->create_field([
			'type'    => 'number',
			'name'    => 'count',
			'label'   => __( 'Wie viele Bewertungen sollen angezeigt werden?', 'blogs-directory' ),
			'value'   => $args['count'],
			'default' => 5,
			'max'     => 100,
		]);

		$this->create_field([
			'type'  => 'select',
			'name'  => 'rating',
			'label' => __( 'Welche Mindestbewertung soll angezeigt werden?', 'blogs-directory' ),
			'value' => $args['rating'],
			'options' => [
				'5' => sprintf( _n( '%s Stern', '%s Sterne', 5, 'blogs-directory' ), 5 ),
				'4' => sprintf( _n( '%s Stern', '%s Sterne', 4, 'blogs-directory' ), 4 ),
				'3' => sprintf( _n( '%s Stern', '%s Sterne', 3, 'blogs-directory' ), 3 ),
				'2' => sprintf( _n( '%s Stern', '%s Sterne', 2, 'blogs-directory' ), 2 ),
				'1' => sprintf( _n( '%s Stern', '%s Sterne', 1, 'blogs-directory' ), 1 ),
			],
		]);

		if( count( $types ) > 1 ) {
			$this->create_field([
				'type'  => 'select',
				'name'  => 'display',
				'label' => __( 'Welche Bewertungen sollen angezeigt werden?', 'blogs-directory' ),
				'class' => 'widefat',
				'value' => $args['display'],
				'options' => ['' => __( 'Alle Bewertungen', 'blogs-directory' ) ] + $types,
			]);
		}

		if( !empty( $terms )) {
			$this->create_field([
				'type'  => 'select',
				'name'  => 'category',
				'label' => __( 'Bewertungen auf diese Kategorie begrenzen', 'blogs-directory' ),
				'class' => 'widefat',
				'value' => $args['category'],
				'options' => ['' => __( 'Alle Kategorien', 'blogs-directory' ) ] + glsr_resolve( 'Database' )->getTerms(),
			]);
		}

		$this->create_field([
			'type'    => 'text',
			'name'    => 'assigned_to',
			'label'   => __( 'Bewertungen auf diese Seiten-/Beitrags-ID begrenzen', 'blogs-directory' ),
			'value'   => $args['assigned_to'],
			'default' => '',
			'placeholder' => __( 'Mehrere IDs mit Komma trennen', 'blogs-directory' ),
			'description' => sprintf( __( 'Du kannst auch %s eingeben, um auf die aktuelle Seite zu begrenzen.', 'blogs-directory' ), '<code>post_id</code>' ),
		]);

		$this->create_field([
			'type'  => 'text',
			'name'  => 'class',
			'label' => __( 'Eigene CSS-Klassen hier eingeben', 'blogs-directory' ),
			'value' => $args['class'],
		]);

		$this->create_field([
			'type'  => 'checkbox',
			'name'  => 'hide',
			'value' => $args['hide'],
			'options' => [
				'author' => __( 'Bewertungsautor ausblenden?', 'blogs-directory' ),
				'date' => __( 'Bewertungsdatum ausblenden?', 'blogs-directory' ),
				'excerpt' => __( 'Bewertungsauszug ausblenden?', 'blogs-directory' ),
				'rating' => __( 'Bewertung (Sterne) ausblenden?', 'blogs-directory' ),
				'response' => __( 'Antwort auf Bewertung ausblenden?', 'blogs-directory' ),
				'title' => __( 'Bewertungstitel ausblenden?', 'blogs-directory' ),
			],
		]);
	}

	/**
	 * Update the widget form
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance )
	{
		if( $new_instance['count'] < 0 ) {
			$new_instance['count'] = 0;
		}
		if( $new_instance['count'] > 100 ) {
			$new_instance['count'] = 100;
		}
		if( !is_numeric( $new_instance['count'] )) {
			$new_instance['count'] = 5;
		}
		return parent::update( $new_instance, $old_instance );
	}

	/**
	 * Display the widget Html
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance )
	{
		$instance = $this->normalize( $instance );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		if( $instance['assigned_to'] == 'post_id' ) {
			$instance['assigned_to'] = intval( get_the_ID() );
		}

		echo $args['before_widget'];
		if( !empty( $title )) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$this->renderReviews( $instance );
		echo $args['after_widget'];
	}
}
