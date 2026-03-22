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
			'add_new_item'          => __( 'Neue Bewertung hinzufuegen', 'blogs-directory' ),
			'all_items'             => __( 'Alle Bewertungen', 'blogs-directory' ),
			'archives'              => __( 'Bewertungs-Archive', 'blogs-directory' ),
			'edit_item'             => __( 'Bewertung bearbeiten', 'blogs-directory' ),
			'insert_into_item'      => __( 'In Bewertung einfuegen', 'blogs-directory' ),
			'new_item'              => __( 'Neue Bewertung', 'blogs-directory' ),
			'not_found'             => __( 'Keine Bewertungen gefunden', 'blogs-directory' ),
			'not_found_in_trash'    => __( 'Keine Bewertungen im Papierkorb gefunden', 'blogs-directory' ),
			'search_items'          => __( 'Bewertungen durchsuchen', 'blogs-directory' ),
			'uploaded_to_this_item' => __( 'Zu dieser Bewertung hochgeladen', 'blogs-directory' ),
			'view_item'             => __( 'Bewertung anzeigen', 'blogs-directory' ),
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
			'approved'      => __( 'Bewertung wurde freigegeben und veroeffentlicht.', 'blogs-directory' ),
			'draft_updated' => __( 'Bewertungsentwurf aktualisiert.', 'blogs-directory' ),
			'preview'       => __( 'Vorschau der Bewertung', 'blogs-directory' ),
			'published'     => __( 'Bewertung freigegeben und veroeffentlicht.', 'blogs-directory' ),
			'restored'      => __( 'Bewertung auf Revision von %s zurueckgesetzt.', 'blogs-directory' ),
			'reverted'      => __( 'Bewertung wurde auf den urspruenglichen Einsendezustand zurueckgesetzt.', 'blogs-directory' ),
			'saved'         => __( 'Bewertung gespeichert.', 'blogs-directory' ),
			'scheduled'     => __( 'Bewertung geplant fuer: %s.', 'blogs-directory' ),
			'submitted'     => __( 'Bewertung eingereicht.', 'blogs-directory' ),
			'unapproved'    => __( 'Freigabe der Bewertung wurde aufgehoben und sie ist jetzt ausstehend.', 'blogs-directory' ),
			'updated'       => __( 'Bewertung aktualisiert.', 'blogs-directory' ),
			'view'          => __( 'Bewertung anzeigen', 'blogs-directory' ),
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
			'local' => __( 'Lokal', 'blogs-directory' ),
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
			'accepted'        => _x( ':attribute muss akzeptiert werden.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'between.numeric' => _x( ':attribute muss zwischen :min und :max liegen.', ':attribute, :min, and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'between.string'  => _x( ':attribute muss zwischen :min und :max Zeichen lang sein.', ':attribute, :min, and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'email'           => _x( ':attribute muss eine gueltige E-Mail-Adresse sein.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'max.numeric'     => _x( ':attribute darf nicht groesser als :max sein.', ':attribute and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'max.string'      => _x( ':attribute darf nicht laenger als :max Zeichen sein.', ':attribute and :max are placeholders and should not be translated.', 'blogs-directory' ),
			'min.numeric'     => _x( ':attribute muss mindestens :min sein.', ':attribute and :min are placeholders and should not be translated.', 'blogs-directory' ),
			'min.string'      => _x( ':attribute muss mindestens :min Zeichen lang sein.', ':attribute and :min are placeholders and should not be translated.', 'blogs-directory' ),
			'regex'           => _x( 'Das Format von :attribute ist ungueltig.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
			'required'        => _x( 'Das Feld :attribute ist erforderlich.', ':attribute is a placeholder and should not be translated.', 'blogs-directory' ),
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
