<?php defined( 'WPINC' ) || die; ?>

<div class="glsr-help-grid">

<p class="glsr-help-intro">Die folgenden Helper-Funktionen funktionieren nur, wenn das Plugin <?= glsr_app()->name; ?> aktiv ist. Wenn du sie im Theme nutzt, pruefe sie vorher mit <a href="https://php.net/manual/en/function.function-exists.php">function_exists</a> in deiner <code>functions.php</code>.</p>

<div class="glsr-card card">
	<h3>Globaler Helper fuer eine einzelne Bewertung</h3>
	<pre><code>glsr_get_review( $post_id );</code></pre>
	<p>Die Variable <code>$post_id</code> ist erforderlich und entspricht der ID der Bewertung.</p>
	<p><strong>Tipp:</strong></p>
	<p>Mit folgendem Code kannst du alle verfuegbaren Werte im Rueckgabeobjekt ansehen.</p>
	<pre><code>glsr_debug( glsr_get_review( $post_id ));</code></pre>
</div>

<div class="glsr-card card">
	<h3>Globaler Helper fuer ein Bewertungs-Array</h3>
	<pre><code>glsr_get_reviews( $args );</code></pre>
	<p>Die Variable <code>$args</code> ist optional, muss aber ein Array sein.</p>
	<p><strong>Standardverwendung:</strong></p>
	<pre><code>glsr_get_reviews([
	'assigned_to' => '',
	'category' => '',
	'count' => 10,
	'order' => 'DESC',
	'orderby' => 'date',
	'pagination' => false,
	'post__in' => [],
	'post__not_in' => [],
	'rating' => 1,
	'type' => '',
]);</code></pre>
	<p><strong>Beispiel:</strong></p>
	<pre><code>$reviews = glsr_get_reviews([
	"count"  => -1,
	"rating" => 1,
]);

foreach( $reviews as $review ) {
	glsr_debug( $review );
}</code></pre>
</div>

<div class="glsr-card card">
	<h3>Globaler Helper fuer eine Plugin-Option</h3>
	<pre><code>glsr_get_option( $option_path, $fallback );</code></pre>
	<p><code>$option_path</code> ist erforderlich und nutzt Punkt-Notation zum gewuenschten Wert.</p>
	<p><code>$fallback</code> ist der Rueckgabewert, wenn die Option nicht existiert oder leer ist. Standard ist ein leerer String.</p>
	<p><strong>Beispiel:</strong></p>
	<p><code>"general.require.login"</code> liefert den Wert der Einstellung "Login erforderlich" aus dem Settings-Array:</p>
	<pre><code>[
	"general" => [
		"require" => [
			"approval" => "yes",
			"login" => "no",
		],
	],
]</code></pre>
	<p><strong>Tipp:</strong></p>
	<p>Mit dem folgenden Aufruf kannst du das komplette Settings-Array sehen und den passenden Pfad leichter finden.</p>
	<pre><code>glsr_debug( glsr_get_options() );</code></pre>
</div>

<div class="glsr-card card">
	<h3>Globaler Helper fuer alle Plugin-Optionen</h3>
	<pre><code>glsr_get_options();</code></pre>
</div>

<div class="glsr-card card">
	<h3>Globaler Helper zum Debuggen von Variablen</h3>
	<pre><code>glsr_debug( $variable, ... );</code></pre>
	<p>Diese Funktion gibt eine oder mehrere Variablen (Strings, Arrays, Objekte usw.) lesbar aus. Mehrere Werte trennst du mit Komma.</p>
	<p><strong>Beispiel:</strong></p>
	<pre><code>glsr_debug( $var1, $var2, $var3 );</code></pre>
</div>

<div class="glsr-card card">
	<h3>Globaler Helper zum Loggen</h3>
	<pre><code>glsr_log( $message, $level );</code></pre>
	<p>Diese Funktion schreibt einen Eintrag ins Log, wenn Logging aktiviert ist.</p>
	<p><code>$level</code> ist optional, Standard ist "debug". Verfuegbare Stufen: "emergency", "alert", "critical", "error", "warning", "notice", "info", "debug".</p>
	<p><strong>Beispiel:</strong></p>
	<pre><code>glsr_log( $log_this_variable );</code></pre>
</div>

</div>
