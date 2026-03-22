<?php defined( 'WPINC' ) || die; ?>

<div class="glsr-help-grid">

<p class="glsr-help-intro">Hooks (Filter und Aktionen) erlauben Anpassungen am Plugin, ohne Core-Dateien direkt zu aendern. Die folgenden Snippets kommen in die <code>functions.php</code> deines Themes.</p>

<div class="glsr-card card">
	<h3>Plugin-CSS deaktivieren</h3>
	<pre><code>add_filter( 'site-reviews/assets/css', '__return_false' );</code></pre>
	<p>Nutze diesen Hook, wenn das Plugin-Stylesheet nicht geladen werden soll.</p>
</div>

<div class="glsr-card card">
	<h3>Plugin-JavaScript deaktivieren</h3>
	<pre><code>add_filter( 'site-reviews/assets/js', '__return_false' );</code></pre>
	<p>Nutze diesen Hook, wenn das Plugin-JavaScript nicht geladen werden soll.</p>
</div>

<div class="glsr-card card">
	<h3>Aktion direkt nach dem Absenden einer Bewertung</h3>
	<pre><code>add_action( 'site-reviews/local/review/submitted', function( $message, $request ) {
	// do something here.
}, 10, 2 );</code></pre>
	<p>Mit diesem Hook kannst du direkt nach erfolgreicher Einsendung reagieren.</p>
	<p><code>$message</code> ist die Erfolgsmeldung fuer den Benutzer.</p>
	<p><code>$request</code> ist das PHP-Objekt der Einsendung, inkl. Referrer (<code>$request->referrer</code>) und AJAX-Status (<code>$request->ajaxRequest</code>).</p>
</div>

<div class="glsr-card card">
	<h3>Standard-<a href="https://developers.google.com/recaptcha/docs/language" target="_blank">reCAPTCHA-Sprache</a> aendern</h3>
	<pre><code>add_filter( 'site-reviews/recaptcha/language', function( $locale ) {
	// return a language code here (e.g. "en")
	return $locale;
});</code></pre>
	<p>Funktioniert nur bei der reCAPTCHA-Einstellung "Custom Integration".</p>
</div>

<div class="glsr-card card">
	<h3>JSON-LD-Schema-Typ-Eigenschaften anpassen</h3>
	<pre><code>$schemaType = 'LocalBusiness';
add_filter( "site-reviews/schema/{$schemaType}", function( array $schema, array $args ) {
	// do something here.
	return $schema;
}, 10, 2 );</code></pre>
	<p>Mit diesem Hook passt du die Eigenschaften des primaren Schema-Typs an (z.B. address, priceRange, telephone).</p>
	<p><code>$schema</code> enthaelt das bestehende Schema-Array, <code>$args</code> die Query-Argumente fuer die Bewertungsberechnung.</p>
	<p>Teste Anpassungen danach mit Googles <a href="https://search.google.com/structured-data/testing-tool">Structured Data Testing Tool</a>.</p>
</div>

<div class="glsr-card card">
	<h3>Finale JSON-LD-Schemas anpassen</h3>
	<pre><code>add_filter( 'site-reviews/schema/all', function( array $schemas ) {
	// do something here.
	return $schemas;
});</code></pre>
	<p>Mit diesem Hook kannst du alle generierten JSON-LD-Schemas direkt vor der Ausgabe anpassen.</p>
	<p><code>$schemas</code> ist ein Array mit allen Schemas auf der Seite. Idealerweise ist es nur eins, bei mehreren Schema-aktiven Shortcodes aber ggf. mehr.</p>
	<p>Teste Anpassungen danach mit Googles <a href="https://search.google.com/structured-data/testing-tool">Structured Data Testing Tool</a>.</p>
</div>

</div>
