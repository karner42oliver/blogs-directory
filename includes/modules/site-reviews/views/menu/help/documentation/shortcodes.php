<?php defined( 'WPINC' ) || die; ?>

<div class="glsr-help-grid">

<div class="glsr-card card">
	<h3>[site_reviews]</h3>
	<p>Dieser Shortcode zeigt deine zuletzt eingereichten Bewertungen.</p>

	<code>assigned_to="100,101"</code>
	<p>Mit "assigned_to" begrenzt du auf bestimmte Seiten-/Beitrags-IDs. Erlaubt sind eine oder mehrere IDs (Komma-getrennt) oder "post_id" fuer die aktuelle Seite.</p>

	<code>category="13,test"</code>
	<p>Mit "category" begrenzt du auf eine oder mehrere Kategorien (ID oder Slug).</p>

	<code>class="my-reviews full-width"</code>
	<p>Mit "class" fuegst du eigene CSS-Klassen hinzu.</p>

	<code>count=10</code>
	<p>Standardmaessig werden 10 Bewertungen angezeigt. Mit "count" aenderst du die Anzahl.</p>

	<code>hide=author,content,date,rating,response,title,url</code>
	<p>Standardmaessig werden alle Felder angezeigt. Mit "hide" blendest du einzelne Felder aus. Wenn alles ausgeblendet ist, wird nichts gerendert.</p>

	<code>offset=1</code>
	<p>Mit "offset" ueberspringst du Bewertungen. Beispiel: <em>[site_reviews count=5 offset=2]</em> zeigt 5 Bewertungen ab dem dritten Eintrag. Mit Pagination nicht empfohlen.</p>

	<code>pagination=true</code>
	<p>Mit "pagination" verteilst du Bewertungen auf mehrere Seiten. Werte: "true", "ajax" oder "false". "ajax" laedt Folgeseiten ohne kompletten Page-Reload. Pro Seite sollte dann nur ein <em>[site_reviews]</em> verwendet werden.</p>

	<code>rating=4</code>
	<p>Standardmaessig werden alle 1-5-Sterne-Bewertungen gezeigt. Mit "rating" setzt du die Mindestbewertung.</p>

	<code>schema=true</code>
	<p>Mit "schema" aktivierst du Rich Snippets (standardmaessig aus). Im Unterschied zu <em>[site_reviews_summary]</em> erzeugt dieser Shortcode sowohl Gesamtschema als auch Schema je Einzelbewertung.</p>
	<p><span class="required">Wichtig:</span> Nur einmal pro Seite nutzen, um doppelte Schemas zu vermeiden.</p>

	<code>id="type some random text here"</code>
	<p>Mit "id" gibst du dem Shortcode eine eigene ID, besonders nuetzlich bei AJAX-Pagination.</p>

	<code>title="Our Reviews"</code>
	<p>Standardmaessig gibt es keine Ueberschrift. Mit "title" setzt du eine eigene.</p>
</div>

<div class="glsr-card card">
	<h3>[site_reviews_summary]</h3>
	<p>Dieser Shortcode zeigt eine Zusammenfassung deiner Bewertungen.</p>

	<code>assigned_to="100,101"</code>
	<p>Mit "assigned_to" wird die Berechnungsbasis der Durchschnittsbewertung auf bestimmte Seiten-/Beitrags-IDs begrenzt.</p>
	<p><span class="required">Wichtig:</span> Wenn du parallel <em>[site_reviews]</em> nutzt, sollte der Wert bei beiden gleich sein.</p>

	<code>category="13,test"</code>
	<p>Mit "category" begrenzt du die Reviews fuer die Durchschnittsberechnung auf bestimmte Kategorien.</p>
	<p><span class="required">Wichtig:</span> Bei Kombination mit <em>[site_reviews]</em> den gleichen Wert verwenden.</p>

	<code>class="my-reviews-summary full-width"</code>
	<p>Mit "class" fuegst du eigene CSS-Klassen hinzu.</p>

	<code>count=20</code>
	<p>Standardmaessig wird der Durchschnitt ueber alle passenden Bewertungen berechnet. Mit "count" begrenzt du die Menge.</p>

	<code>hide=bars,rating,stars,summary</code>
	<p>Standardmaessig werden alle Felder gezeigt. Mit "hide" blendest du Felder aus.</p>

	<code>labels="5 star,4 star,3 star,2 star,1 star"</code>
	<p>Mit "labels" setzt du eigene Bezeichnungen fuer die Balkenstufen (hoch nach niedrig), kommagetrennt.</p>

	<code>rating=1</code>
	<p>Standardmaessig fliessen alle 1-5-Sterne-Bewertungen ein. Mit "rating" setzt du die Mindestbewertung.</p>
	<p><span class="required">Wichtig:</span> Bei Kombination mit <em>[site_reviews]</em> den gleichen Wert verwenden.</p>

	<code>schema=true</code>
	<p>Mit "schema" aktivierst du Rich Snippets (standardmaessig aus). Dieser Shortcode erzeugt nur das Gesamtschema; <em>[site_reviews]</em> erzeugt zusaetzlich Schema pro Einzelbewertung.</p>
	<p><span class="required">Wichtig:</span> Nur einmal pro Seite nutzen, um doppelte Schemas zu vermeiden.</p>

	<code>text="{rating} out of {max} stars"</code>
	<p>Mit "text" passt du den Zusammenfassungstext an. Verfuegbare Platzhalter: "{rating}", "{max}" und "%d". Besser ist meist eine eigene Uebersetzung in "Site Reviews -> Einstellungen -> Uebersetzungen".</p>
	<p>Standardtext: "{rating} von {max} Sternen (basierend auf %d Bewertungen)".</p>

	<code>title="Overall Rating"</code>
	<p>Standardmaessig gibt es keine Ueberschrift. Mit "title" setzt du eine eigene.</p>
</div>

<div class="glsr-card card">
	<h3>[site_reviews_form]</h3>
	<p>Dieser Shortcode zeigt das Formular zum Einreichen von Bewertungen.</p>

	<code>assign_to="101"</code>
	<p>Mit "assign_to" weist du eingereichte Bewertungen automatisch einer Seite oder einem Beitrag zu.</p>

	<code>category="13,test"</code>
	<p>Mit "category" weist du eingereichten Bewertungen automatisch eine oder mehrere Kategorien zu.</p>

	<code>class="my-reviews-form"</code>
	<p>Mit "class" fuegst du dem Formular eigene CSS-Klassen hinzu.</p>

	<code>description="Required fields are marked &lt;span&gt;*&lt;/span&gt;"</code>
	<p>Standardmaessig gibt es keine Beschreibung. Mit "description" setzt du eine eigene.</p>

	<code>hide=email,name,terms,title</code>
	<p>Mit "hide" blendest du bestimmte Formularfelder aus. Bewertungs- und Review-Feld koennen nicht ausgeblendet werden.</p>

	<code>id="type some random text here"</code>
	<p>Dieser Shortcode sollte pro Seite nur einmal verwendet werden. Wenn mehrere noetig sind, gib jedem mit "id" einen eindeutigen Wert.</p>

	<code>title="Submit a Review"</code>
	<p>Standardmaessig gibt es keine Ueberschrift. Mit "title" setzt du eine eigene.</p>
</div>

</div>
