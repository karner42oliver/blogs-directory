<?php

/**
 * Site Reviews Summary shortcode button
 *
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.3.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Shortcodes\Buttons;

use PsourceLabs\SiteReviews\Shortcodes\Buttons\Generator;

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
				'label' => esc_html__( 'Anzeigen', 'blogs-directory' ),
				'options' => $types,
				'tooltip' => __( 'Welche Bewertungen sollen angezeigt werden?', 'blogs-directory' ),
			];
		}
		if( !empty( $terms )) {
			$category = [
				'type' => 'listbox',
				'name' => 'category',
				'label' => esc_html__( 'Kategorie', 'blogs-directory' ),
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
				'type' => 'textbox',
				'name' => 'title',
				'label' => esc_html__( 'Titel', 'blogs-directory' ),
				'tooltip' => __( 'Gib eine eigene Shortcode-Ueberschrift ein.', 'blogs-directory' ),
			],[
				'type' => 'textbox',
				'name' => 'labels',
				'label' => esc_html__( 'Bezeichnungen', 'blogs-directory' ),
				'tooltip' => __( 'Gib eigene Bezeichnungen fuer die 1-5-Sterne-Stufen ein (von hoch nach niedrig), getrennt mit Komma. Standard: "Ausgezeichnet, Sehr gut, Durchschnittlich, Schlecht, Furchtbar".', 'blogs-directory' ),
			],[
				'type' => 'listbox',
				'name' => 'rating',
				'label' => esc_html__( 'Bewertung', 'blogs-directory' ),
				'options' => [
					'5' => esc_html( sprintf( _n( '%s Stern', '%s Sterne', 5, 'blogs-directory' ), 5 )),
					'4' => esc_html( sprintf( _n( '%s Stern', '%s Sterne', 4, 'blogs-directory' ), 4 )),
					'3' => esc_html( sprintf( _n( '%s Stern', '%s Sterne', 3, 'blogs-directory' ), 3 )),
					'2' => esc_html( sprintf( _n( '%s Stern', '%s Sterne', 2, 'blogs-directory' ), 2 )),
					'1' => esc_html( sprintf( _n( '%s Stern', '%s Sterne', 1, 'blogs-directory' ), 1 )),
				],
				'tooltip' => __( 'Welche Mindestbewertung? (Standard: 1 Stern)', 'blogs-directory' ),
			],
			( isset( $display ) ? $display : [] ),
			( isset( $category ) ? $category : [] ),
			[
				'type' => 'textbox',
				'name' => 'assigned_to',
				'label' => esc_html__( 'Beitrags-ID', 'blogs-directory' ),
				'tooltip' => __( "Bewertungen auf diese Beitrags-ID begrenzen (mehrere IDs mit Komma trennen). Du kannst auch 'post_id' fuer die aktuelle Seite verwenden.", 'blogs-directory' ),
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
				'type' => 'textbox',
				'name' => 'class',
				'label' => esc_html__( 'Klassen', 'blogs-directory' ),
				'tooltip' => __( 'Eigene CSS-Klassen zum Shortcode hinzufuegen.', 'blogs-directory' ),
			],[
				'type' => 'container',
				'label' => esc_html__( 'Ausblenden', 'blogs-directory' ),
				'layout' => 'grid',
				'columns' => 2,
				'spacing' => 5,
				'items' => [
					[
						'type' => 'checkbox',
						'name' => 'hide_bars',
						'text' => esc_html__( 'Balken', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Prozentbalken ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_rating',
						'text' => esc_html__( 'Bewertung', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Bewertung ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_stars',
						'text' => esc_html__( 'Sterne', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Sterne ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_summary',
						'text' => esc_html__( 'Zusammenfassung', 'blogs-directory' ),
						'tooltip' => esc_attr__( 'Zusammenfassungstext ausblenden?', 'blogs-directory' ),
					],
				],
			],
		];
	}
}
