<?php

namespace PsourceLabs\SiteReviews;

use PsourceLabs\SiteReviews\App;
use PsourceLabs\SiteReviews\Html;
use PsourceLabs\SiteReviews\Translator;
use ReflectionClass;
use ReflectionMethod;

class Settings
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Html
	 */
	protected $html;

	/**
	 * @var array
	 */
	protected $settings;

	public function __construct( App $app, Html $html )
	{
		$this->app      = $app;
		$this->html     = $html;
		$this->settings = [];
	}

	/**
	 * Add a setting default
	 *
	 * @param string $formId
	 *
	 * @return void
	 */
	public function addSetting( $formId, array $args )
	{
		$args = $this->normalizePaths( $formId, $args );

		if( isset( $args['name'] )) {
			$this->settings[ $args['name']] = $this->getDefault( $args );
		}

		$this->html->addfield( $formId, $args );
	}

	/**
	 * Get the default field value
	 *
	 * @return string
	 */
	public function getDefault( array $args )
	{
		isset( $args['default'] ) ?: $args['default'] = '';
		isset( $args['placeholder'] ) ?: $args['placeholder'] = '';

		if( $args['default'] === ':placeholder' ) {
			$args['default'] = $args['placeholder'];
		}

		if( strpos( $args['type'], 'yesno' ) !== false && empty( $args['default'] )) {
			$args['default'] = 'no';
		}

		return $args['default'];
	}

	/**
	 * Get the default settings
	 *
	 * @return array
	 */
	public function getSettings()
	{
		$this->register();

		return $this->settings;
	}

	/**
	 * @param string $path
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function normalizePath( $path, $prefix )
	{
		return substr( $path, 0, strlen( $prefix )) != $prefix
			? sprintf( '%s.%s', $prefix, $path )
			: $path;
	}

	/**
	 * @param string $formId
	 *
	 * @return array
	 */
	public function normalizePaths( $formId, array $args )
	{
		$prefix = strtolower( str_replace( '/', '.', $formId ));

		if( isset( $args['name'] ) && is_string( $args['name'] )) {
			$args['name'] = $this->normalizePath( $args['name'], $prefix );
		}

		if( isset( $args['depends'] ) && is_array( $args['depends'] )) {
			$depends = [];
			foreach( $args['depends'] as $path => $value ) {
				$depends[ $this->normalizePath( $path, $prefix ) ] = $value;
			}
			$args['depends'] = $depends;
		}

		return $args;
	}

	/**
	 * Register the settings for each form
	 *
	 * @return void
	 *
	 * @action admin_init
	 */
	public function register()
	{
		if( !empty( $this->settings ))return;

		$methods = (new ReflectionClass( __CLASS__ ))->getMethods( ReflectionMethod::IS_PROTECTED );

		foreach( $methods as $method ) {
			if( substr( $method->name, 0, 3 ) === 'set' ) {
				$this->{$method->name}();
			}
		}
	}

	/**
	 * @return void
	 */
	protected function setGeneral()
	{
		$formId = 'settings/general';

		$this->html->createForm( $formId, [
			'action' => admin_url( 'options.php' ),
			'nonce'  => $this->app->id . '-settings',
			'submit' => __( 'Einstellungen speichern', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'    => 'yesno_inline',
			'name'    => 'require.approval',
			'label'   => __( 'Freigabe erforderlich', 'blogs-directory' ),
			'default' => 'yes',
			'desc'    => __( 'Neue Bewertungen werden zuerst auf ausstehend gesetzt.', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'  => 'yesno_inline',
			'name'  => 'require.login',
			'label' => __( 'Login erforderlich', 'blogs-directory' ),
			'desc'  => __( 'Nur registrierte Benutzer duerfen Bewertungen absenden.', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'    => 'yesno_inline',
			'name'    => 'require.login_register',
			'label'   => __( 'Registrierungslink anzeigen', 'blogs-directory' ),
			'depends' => [
				'require.login' => 'yes',
			],
			'desc' => sprintf( __( 'Zeigt einen Link zur Registrierung neuer Benutzer. Dafuer muss die Option %s in den allgemeinen Einstellungen aktiviert sein.', 'blogs-directory' ),
				sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php' ), __( 'Jeder kann sich registrieren', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'    => 'radio',
			'name'    => 'notification',
			'label'   => __( 'Benachrichtigungen', 'blogs-directory' ),
			'default' => 'none',
			'options' => [
				'none'    => __( 'Keine Bewertungs-Benachrichtigungen senden', 'blogs-directory' ),
				'default' => __( 'An Administrator senden', 'blogs-directory' ) . sprintf( ' <code>%s</code>', (string) get_option( 'admin_email' )),
				'custom'  => __( 'An eine oder mehrere E-Mail-Adressen senden', 'blogs-directory' ),
				'webhook' => sprintf( __( 'An %s senden', 'blogs-directory' ), '<a href="https://slack.com/">Slack</a>' ),
			],
		]);

		$this->addSetting( $formId, [
			'type'    => 'text',
			'name'    => 'notification_email',
			'label'   => __( 'Benachrichtigungs-E-Mails senden an', 'blogs-directory' ),
			'depends' => [
				'notification' => 'custom',
			],
			'placeholder' => __( 'Mehrere E-Mail-Adressen mit Komma trennen', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'    => 'url',
			'name'    => 'webhook_url',
			'label'   => __( 'Webhook URL', 'blogs-directory' ),
			'depends' => [
				'notification' => 'webhook',
			],
			'desc' => sprintf( __( 'Um Benachrichtigungen an Slack zu senden, erstelle einen neuen %s und fuege danach die Webhook-URL oben ein.', 'blogs-directory' ),
				sprintf( '<a href="%s">%s</a>', esc_url( 'https://slack.com/apps/new/A0F7XDUAZ-incoming-webhooks' ), __( 'Incoming WebHook', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'    => 'code',
			'name'    => 'notification_message',
			'label'   => __( 'Benachrichtigungs-Template', 'blogs-directory' ),
			'rows'    => 10,
			'depends' => [
				'notification' => ['custom', 'default', 'webhook'],
			],
			'default' => $this->html->renderTemplate( 'email/templates/review-notification', [] ),
			'desc' => 'Um den Standardtext wiederherzustellen, speichere ein leeres Template.
				Wenn du Benachrichtigungen an Slack sendest, wird dieses Template nur als Rueckfall genutzt, falls <a href="https://api.slack.com/docs/attachments">Message Attachments</a> deaktiviert sind.<br>
				Verfuegbare Template-Tags:<br>
				<code>{review_rating}</code> - Die Bewertungszahl (1-5)<br>
				<code>{review_title}</code> - Der Bewertungstitel<br>
				<code>{review_content}</code> - Der Bewertungsinhalt<br>
				<code>{review_author}</code> - Der Autor der Bewertung<br>
				<code>{review_email}</code> - Die E-Mail des Bewertungsautors<br>
				<code>{review_ip}</code> - Die IP-Adresse des Bewertungsautors<br>
				<code>{review_link}</code> - Der Link zum Bearbeiten/Anzeigen der Bewertung',
		]);
	}

	/**
	 * @return void
	 */
	protected function setReviews()
	{
		$formId = 'settings/reviews';

		$this->html->createForm( $formId, [
			'action' => admin_url( 'options.php' ),
			'nonce'  => $this->app->id . '-settings',
			'submit' => __( 'Einstellungen speichern', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'date.format',
			'label' => __( 'Datumsformat', 'blogs-directory' ),
			'options' => [
				'default' => __( 'Standard-Datumsformat verwenden', 'blogs-directory' ),
				'relative' => __( 'Relatives Datumsformat verwenden', 'blogs-directory' ),
				'custom' => __( 'Eigenes Datumsformat verwenden', 'blogs-directory' ),
			],
			'desc'  => sprintf( __( 'Das Standard-Datumsformat entspricht dem Wert in deinen %s.', 'blogs-directory' ),
				sprintf( '<a href="%s">%s<a>', get_admin_url( null, 'options-general.php' ), __( 'WordPress-Einstellungen', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'    => 'text',
			'name'    => 'date.custom',
			'label'   => __( 'Eigenes Datumsformat', 'blogs-directory' ),
			'default' => get_option( 'date_format' ),
			'desc'    => sprintf( __( 'Trag ein eigenes Datumsformat ein (%s).', 'blogs-directory' ),
				sprintf( '<a href="https://codex.wordpress.org/Formatting_Date_and_Time">%s</a>', __( 'Dokumentation zu Datums- und Zeitformaten', 'blogs-directory' ))
			),
			'depends' => [
				'date.format' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'yesno_inline',
			'name'  => 'assigned_links.enabled',
			'label' => __( 'Zugewiesene Links aktivieren', 'blogs-directory' ),
			'desc'  => __( 'Zeigt einen Link zum zugewiesenen Beitrag einer Bewertung.', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'  => 'yesno_inline',
			'name'  => 'avatars.enabled',
			'label' => __( 'Avatare aktivieren', 'blogs-directory' ),
			'desc'  => sprintf( __( 'Zeigt Avatare der Bewerter. Diese werden aus der E-Mail-Adresse ueber %s erzeugt.', 'blogs-directory' ),
				sprintf( '<a href="https://gravatar.com">%s</a>', __( 'Gravatar', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'yesno_inline',
			'name'  => 'excerpt.enabled',
			'label' => __( 'Auszuege aktivieren', 'blogs-directory' ),
			'desc'  => __( 'Zeigt einen Auszug statt der vollstaendigen Bewertung.', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'    => 'number',
			'name'    => 'excerpt.length',
			'label'   => __( 'Laenge des Auszugs', 'blogs-directory' ),
			'default' => '55',
			'desc'    => __( 'Legt die Wortanzahl fuer den Auszug fest.', 'blogs-directory' ),
			'depends' => [
				'excerpt.enabled' => 'yes',
			],
		]);

		$this->html->addfield( $formId, [
			'type'  => 'heading',
			'value' => __( 'Rich Snippets (schema.org)', 'blogs-directory' ),
			'desc'  => __( 'Bewertungs-Snippets erscheinen in Google-Suchergebnissen und enthalten Sternebewertung sowie weitere Zusammenfassungsdaten.', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'schema.type.default',
			'label' => __( 'Standard-Schema-Typ', 'blogs-directory' ),
			'default' => 'LocalBusiness',
			'options' => [
				'LocalBusiness' => __( 'Lokales Unternehmen', 'blogs-directory' ),
				'Product' => __( 'Produkt', 'blogs-directory' ),
				'custom' => __( 'Benutzerdefiniert', 'blogs-directory' ),
			],
			'desc' => sprintf( __( 'Das ist der Standard-Schema-Typ fuer das bewertete Element. Du kannst ihn pro Beitrag/Seite ueberschreiben, indem du einen %s-Metawert mit %s setzt.', 'blogs-directory' ),
				'<code>schema_type</code>',
				sprintf( '<a href="https://codex.wordpress.org/Using_Custom_Fields#Usage">%s</a>', __( 'Benutzerdefinierte Felder', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'schema.type.custom',
			'label' => __( 'Benutzerdefinierter Schema-Typ', 'blogs-directory' ),
			'depends' => [
				'schema.type.default' => 'custom',
			],
			'desc' => sprintf(
				__( 'Google unterstuetzt Bewertungs-Sterne fuer folgende Schema-Inhaltstypen: lokale Unternehmen, Filme, Buecher, Musik und Produkte. %s', 'blogs-directory' ),
				sprintf( '<a href="https://schema.org/docs/schemas.html">%s</a>', __( 'Mehr Infos zu Schema-Typen findest du hier.', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'schema.name.default',
			'label' => __( 'Standardname', 'blogs-directory' ),
			'default' => 'post',
			'options' => [
				'post' => __( 'Titel der zugewiesenen oder aktuellen Seite verwenden', 'blogs-directory' ),
				'custom' => __( 'Eigenen Titel eingeben', 'blogs-directory' ),
			],
			'desc' => sprintf( __( 'Das ist der Standardname des bewerteten Elements. Du kannst ihn pro Beitrag/Seite ueberschreiben, indem du einen %s-Metawert mit %s setzt.', 'blogs-directory' ),
				'<code>schema_name</code>',
				sprintf( '<a href="https://codex.wordpress.org/Using_Custom_Fields#Usage">%s</a>', __( 'Benutzerdefinierte Felder', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'schema.name.custom',
			'label' => __( 'Benutzerdefinierter Name', 'blogs-directory' ),
			'depends' => [
				'schema.name.default' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'schema.description.default',
			'label' => __( 'Standardbeschreibung', 'blogs-directory' ),
			'default' => 'post',
			'options' => [
				'post' => __( 'Auszug der zugewiesenen oder aktuellen Seite verwenden', 'blogs-directory' ),
				'custom' => __( 'Eigene Beschreibung eingeben', 'blogs-directory' ),
			],
			'desc' => sprintf( __( 'Das ist die Standardbeschreibung fuer das bewertete Element. Du kannst sie pro Beitrag/Seite ueberschreiben, indem du einen %s-Metawert mit %s setzt.', 'blogs-directory' ),
				'<code>schema_description</code>',
				sprintf( '<a href="https://codex.wordpress.org/Using_Custom_Fields#Usage">%s</a>', __( 'Benutzerdefinierte Felder', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'schema.description.custom',
			'label' => __( 'Benutzerdefinierte Beschreibung', 'blogs-directory' ),
			'depends' => [
				'schema.description.default' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'schema.url.default',
			'label' => __( 'Standard-URL', 'blogs-directory' ),
			'default' => 'post',
			'options' => [
				'post' => __( 'URL der zugewiesenen oder aktuellen Seite verwenden', 'blogs-directory' ),
				'custom' => __( 'Eigene URL eingeben', 'blogs-directory' ),
			],
			'desc' => sprintf( __( 'Das ist die Standard-URL fuer das bewertete Element. Du kannst sie pro Beitrag/Seite ueberschreiben, indem du einen %s-Metawert mit %s setzt.', 'blogs-directory' ),
				'<code>schema_url</code>',
				sprintf( '<a href="https://codex.wordpress.org/Using_Custom_Fields#Usage">%s</a>', __( 'Benutzerdefinierte Felder', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'schema.url.custom',
			'label' => __( 'Benutzerdefinierte URL', 'blogs-directory' ),
			'depends' => [
				'schema.url.default' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'schema.image.default',
			'label' => __( 'Standardbild', 'blogs-directory' ),
			'default' => 'post',
			'options' => [
				'post' => __( 'Beitragsbild der zugewiesenen oder aktuellen Seite verwenden', 'blogs-directory' ),
				'custom' => __( 'Eigene Bild-URL eingeben', 'blogs-directory' ),
			],
			'desc' => sprintf( __( 'Das ist das Standardbild fuer das bewertete Element. Du kannst es pro Beitrag/Seite ueberschreiben, indem du einen %s-Metawert mit %s setzt.', 'blogs-directory' ),
				'<code>schema_image</code>',
				sprintf( '<a href="https://codex.wordpress.org/Using_Custom_Fields#Usage">%s</a>', __( 'Benutzerdefinierte Felder', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'schema.image.custom',
			'label' => __( 'Benutzerdefinierte Bild-URL', 'blogs-directory' ),
			'depends' => [
				'schema.image.default' => 'custom',
			],
		]);
	}

	/**
	 * @return void
	 */
	protected function setReviewsForm()
	{
		$formId = 'settings/reviews-form';

		$this->html->createForm( $formId, [
			'action' => admin_url( 'options.php' ),
			'nonce'  => $this->app->id . '-settings',
			'submit' => __( 'Einstellungen speichern', 'blogs-directory' ),
		]);

		$this->addSetting( $formId, [
			'type'    => 'checkbox',
			'name'    => 'required',
			'label'   => __( 'Pflichtfelder', 'blogs-directory' ),
			'default' => ['title','content','name','email'],
			'options' => [
				'title' => __( 'Titel', 'blogs-directory' ),
				'content' => __( 'Bewertung', 'blogs-directory' ),
				'name' => __( 'Name', 'blogs-directory' ),
				'email' => __( 'E-Mail', 'blogs-directory' ),
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'recaptcha.integration',
			'label' => __( 'Unsichtbares reCAPTCHA', 'blogs-directory' ),
			'options' => [
				'' => __( 'reCAPTCHA nicht verwenden', 'blogs-directory' ),
				'custom' => __( 'reCAPTCHA verwenden', 'blogs-directory' ),
				'invisible-recaptcha' => _x( 'Drittanbieter-Plugin verwenden: Invisible reCaptcha', 'plugin name', 'blogs-directory' ),
			],
			'desc'  => sprintf( __( 'Unsichtbares reCAPTCHA ist ein kostenloser Anti-Spam-Dienst von Google. Um ihn zu nutzen, musst du %s und ein API-Key-Paar fuer deine Seite erstellen. Wenn du bereits eines der hier gelisteten reCAPTCHA-Plugins nutzt, waehle es aus, sonst "reCAPTCHA verwenden".', 'blogs-directory' ),
				sprintf( '<a href="https://www.google.com/recaptcha/admin" target="_blank">%s</a>', __( 'registrieren', 'blogs-directory' ))
			),
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'recaptcha.key',
			'label' => __( 'Site-Schluessel', 'blogs-directory' ),
			'depends' => [
				'recaptcha.integration' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'text',
			'name'  => 'recaptcha.secret',
			'label' => __( 'Site-Secret', 'blogs-directory' ),
			'depends' => [
				'recaptcha.integration' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type'  => 'select',
			'name'  => 'recaptcha.position',
			'label' => __( 'Badge-Position', 'blogs-directory' ),
			'options' => [
				'bottomleft' => 'Unten links',
				'bottomright' => 'Unten rechts',
				'inline' => 'Inline',
			],
			'depends' => [
				'recaptcha.integration' => 'custom',
			],
		]);

		$this->addSetting( $formId, [
			'type' => 'textarea',
			'name' => 'blacklist.entries',
			'label' => __( 'Bewertungs-Blacklist', 'blogs-directory' ),
			'desc' => __( 'Wenn eine Bewertung eines dieser Woerter im Titel, Inhalt, Namen, in der E-Mail oder IP-Adresse enthaelt, wird sie abgelehnt. Ein Wort oder eine IP pro Zeile. Es wird auch innerhalb von Woertern gematcht, daher trifft "press" auch auf "WordPress" zu.', 'blogs-directory' ),
			'class' => 'large-text code',
			'rows' => 10,
		]);

		$this->addSetting( $formId, [
			'type' => 'select',
			'name' => 'blacklist.action',
			'label' => __( 'Blacklist-Aktion', 'blogs-directory' ),
			'options' => [
				'unapprove' => __( 'Freigabe erforderlich', 'blogs-directory' ),
				'reject' => __( 'Absendung ablehnen', 'blogs-directory' ),
			],
			'desc' => __( 'Waehle die Aktion, die ausgefuehrt werden soll, wenn eine Bewertung auf der Blacklist steht.', 'blogs-directory' ),
		]);
	}

	/**
	 * @return void
	 */
	protected function setStrings()
	{
		$formId = 'settings/strings';

		$this->html->createForm( $formId, [
			'action' => admin_url( 'options.php' ),
			'class'  => 'glsr-strings-form',
			'nonce'  => $this->app->id . '-settings',
			'submit' => __( 'Einstellungen speichern', 'blogs-directory' ),
		]);

		// This exists for when there are no custom translations
		$this->addSetting( $formId, [
			'type' => 'hidden',
			'name' => '',
		]);

		$this->html->addCustomField( $formId, function() {
			$translations = $this->app->make( 'Translator' )->renderAll();
			$class = empty( $translations )
				? 'glsr-hidden'
				: '';
			return $this->html->renderTemplate( 'strings/translations', [
				'class' => $class,
				'translations' => $translations,
			]);
		});
	}
}
