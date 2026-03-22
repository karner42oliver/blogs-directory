<?php

/**
 * Site Reviews Form shortcode button
 *
 * @package   PsourceLabs\SiteReviews
 * @copyright Copyright (c) 2017, Paul Ryley
 * @license   GPLv3
 * @since     2.0.0
 * -------------------------------------------------------------------------------------------------
 */

namespace PsourceLabs\SiteReviews\Shortcodes\Buttons;

use PsourceLabs\SiteReviews\Shortcodes\Buttons\Generator;

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
				'label'   => esc_html__( 'Kategorie', 'blogs-directory' ),
				'options' => $terms,
				'tooltip' => __( 'Eingereichten Bewertungen automatisch eine Kategorie zuweisen.', 'blogs-directory' ),
			];
		}

		return [
			[
				'type' => 'container',
				'html' => sprintf( '<p class="strong">%s</p>', esc_html__( 'Alle Einstellungen sind optional.', 'blogs-directory' )),
			],[
				'type'    => 'textbox',
				'name'    => 'title',
				'label'   => esc_html__( 'Titel', 'blogs-directory' ),
				'tooltip' => __( 'Gib eine eigene Shortcode-Ueberschrift ein.', 'blogs-directory' ),
			],[
				'type'    => 'textbox',
				'name'    => 'description',
				'label'   => esc_html__( 'Beschreibung', 'blogs-directory' ),
				'tooltip' => __( 'Gib eine eigene Shortcode-Beschreibung ein.', 'blogs-directory' ),
				'minWidth' => 240,
				'minHeight' => 60,
				'multiline' => true,
			],
			( isset( $category ) ? $category : [] ),
			[
				'type'      => 'textbox',
				'name'      => 'assign_to',
				'label'     => esc_html__( 'Beitrags-ID', 'blogs-directory' ),
				'tooltip'   => __( 'Eingereichte Bewertungen einer benutzerdefinierten Seiten-/Beitrags-ID zuweisen. Du kannst auch "post_id" fuer die aktuelle Seite verwenden.', 'blogs-directory' ),
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
						'name' => 'hide_email',
						'text' => esc_html__( 'E-Mail', 'blogs-directory' ),
						'tooltip' => __( 'E-Mail-Feld ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_name',
						'text' => esc_html__( 'Name', 'blogs-directory' ),
						'tooltip' => __( 'Namensfeld ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_terms',
						'text' => esc_html__( 'AGB', 'blogs-directory' ),
						'tooltip' => __( 'AGB-Feld ausblenden?', 'blogs-directory' ),
					],[
						'type' => 'checkbox',
						'name' => 'hide_title',
						'text' => esc_html__( 'Titel', 'blogs-directory' ),
						'tooltip' => __( 'Titelfeld ausblenden?', 'blogs-directory' ),
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
