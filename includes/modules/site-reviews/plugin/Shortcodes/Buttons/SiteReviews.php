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
				'label'   => esc_html__( 'Anzeigen', 'blogs-directory' ),
				'options' => $types,
				'tooltip' => __( 'Welche Bewertungen sollen angezeigt werden?', 'blogs-directory' ),
			];
		}

		if( !empty( $terms )) {
			$category = [
				'type'    => 'listbox',
				'name'    => 'category',
				'label'   => esc_html__( 'Kategorie', 'blogs-directory' ),
				'options' => $terms,
				'tooltip' => __( 'Bewertungen auf diese Kategorie begrenzen.', 'blogs-directory' ),
			];
		}

		return [
			[
				'type' => 'container',
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'Alle Einstellungen sind optional.', 'blogs-directory' )),
				'minWidth' => 320,
			],[
				'type'     => 'textbox',
				'name'     => 'title',
				'label'    => esc_html__( 'Titel', 'blogs-directory' ),
				'tooltip'  => __( 'Gib eine eigene Shortcode-Ueberschrift ein.', 'blogs-directory' ),
			],[
				'type'      => 'textbox',
				'name'      => 'count',
				'maxLength' => 5,
				'size'      => 3,
				'text'      => '10',
				'label'     => esc_html__( 'Anzahl', 'blogs-directory' ),
				'tooltip'   => __( 'Wie viele Bewertungen sollen angezeigt werden (Standard: 10)?', 'blogs-directory' ),
			],[
				'type'    => 'listbox',
				'name'    => 'rating',
				'label'   => esc_html__( 'Bewertung', 'blogs-directory' ),
				'options' => [
					'5' => esc_html__( '5 Sterne', 'blogs-directory' ),
					'4' => esc_html__( '4 Sterne', 'blogs-directory' ),
					'3' => esc_html__( '3 Sterne', 'blogs-directory' ),
					'2' => esc_html__( '2 Sterne', 'blogs-directory' ),
					'1' => esc_html__( '1 Stern', 'blogs-directory' ),
				],
				'tooltip' => __( 'Welche Mindestbewertung soll angezeigt werden (Standard: 1 Stern)?', 'blogs-directory' ),
			],[
				'type'    => 'listbox',
				'name'    => 'pagination',
				'label'   => esc_html__( 'Seitennavigation', 'blogs-directory' ),
				'options' => [
					'true'  => esc_html__( 'Aktivieren', 'blogs-directory' ),
					'ajax' => esc_html__( 'Aktivieren (mit AJAX)', 'blogs-directory' ),
					'false' => esc_html__( 'Deaktivieren', 'blogs-directory' ),
				],
				'tooltip' => __( 'Mit Seitennavigation kann dieser Shortcode pro Seite nur einmal verwendet werden. (Standard: deaktiviert)', 'blogs-directory' ),
			],
			( isset( $display ) ? $display : [] ),
			( isset( $category ) ? $category : [] ),
			[
				'type'      => 'textbox',
				'name'      => 'assigned_to',
				'label'     => esc_html__( 'Beitrags-ID', 'blogs-directory' ),
				'tooltip'   => __( 'Bewertungen auf diese Beitrags-ID begrenzen (mehrere IDs mit Komma trennen). Du kannst auch "post_id" fuer die aktuelle Seite verwenden.', 'blogs-directory' ),
			],[
				'type' => 'listbox',
				'name' => 'schema',
				'label' => esc_html__( 'Schema', 'blogs-directory' ),
				'options' => [
					'true' => esc_html__( 'Rich Snippets aktivieren', 'blogs-directory' ),
					'false' => esc_html__( 'Rich Snippets deaktivieren', 'blogs-directory' ),
				],
				'tooltip' => __( 'Rich Snippets sind standardmaessig deaktiviert.', 'blogs-directory' ),
			],[
				'type'     => 'textbox',
				'name'     => 'class',
				'label'    => esc_html__( 'Klassen', 'blogs-directory' ),
				'tooltip'  => __( 'Eigene CSS-Klassen zum Shortcode hinzufuegen.', 'blogs-directory' ),
			],[
				'type'    => 'container',
				'label'   => esc_html__( 'Ausblenden', 'blogs-directory' ),
				'layout'  => 'grid',
				'columns' => 2,
				'spacing' => 5,
				'items'   => [
					[
						'type' => 'checkbox',
						'name' => 'hide_author',
						'text' => esc_html__( 'Autor', 'blogs-directory' ),
						'tooltip' => __( 'Bewertungsautor ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_date',
						'text' => esc_html__( 'Datum', 'blogs-directory' ),
						'tooltip' => __( 'Bewertungsdatum ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_excerpt',
						'text' => esc_html__( 'Auszug', 'blogs-directory' ),
						'tooltip' => __( 'Bewertungsauszug ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_rating',
						'text' => esc_html__( 'Bewertung', 'blogs-directory' ),
						'tooltip' => __( 'Bewertung ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_response',
						'text' => esc_html__( 'Antwort', 'blogs-directory' ),
						'tooltip' => __( 'Antwort auf Bewertung ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_title',
						'text' => esc_html__( 'Titel', 'blogs-directory' ),
						'tooltip' => __( 'Bewertungstitel ausblenden?', 'blogs-directory' ),
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
