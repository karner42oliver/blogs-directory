<?php defined( 'WPINC' ) || die; ?>

<div class="glsr-help-grid">

<div class="glsr-card card">
	<h3>F.A.Q.</h3>
	<dl>
		<dt>Wie aendere ich Platzhalter- oder Labeltexte?</dt>
		<dd>Alle Textbausteine des Plugins kannst du auf der Seite <code><a href="<?= admin_url( 'edit.php?post_type=site-review&page=settings&tab=settings&section=strings' ); ?>">Einstellungen -> Uebersetzungen</a></code> anpassen.</dd>

		<dt>Wie deaktiviere ich Plugin-CSS und/oder JavaScript?</dt>
		<dd>Wenn du Stylesheet oder JavaScript des Plugins nicht laden willst, nutze die gezeigten <code><a href="<?= admin_url( 'edit.php?post_type=site-review&page=help&tab=documentation&section=hooks' ); ?>">WordPress-Filter und Aktionen</a></code> in der <code>functions.php</code> deines Themes.</dd>

		<dt>Meine Widgets sehen in der Sidebar komisch aus. Woran liegt das?</dt>
		<dd>Manche Themes haben sehr schmale Sidebars oder eigene CSS-Regeln, die mit Site Reviews kollidieren. Du kannst entweder das Plugin-CSS deaktivieren oder die verwendeten Selektoren in deinem Theme ueberschreiben. Reine CSS-Themen lassen sich nicht allgemein supporten, da es zu viele Theme-Varianten gibt.</dd>
	</dl>
</div>

<div class="glsr-card card">
	<h3>Basis-Fehlersuche</h3>
	<ol>
		<li>
			<p><strong>Nutze die aktuelle Version von Site Reviews.</strong></p>
			<p>Site Reviews wird regelmaessig mit Bugfixes, Sicherheitsupdates und Verbesserungen aktualisiert. In der neuesten Version sind viele bekannte Probleme bereits behoben.</p>
		</li>
		<li>
			<p><strong>Teste mit einem Standard-WordPress-Theme (z.B. Twenty Seventeen).</strong></p>
			<p>Wenn auf der Seite jQuery- oder JavaScript-Fehler auftreten, pruefe das Verhalten kurz mit einem Standard-Theme. Besonders bei Drittanbieter-Themes sind JS-Konflikte haeufig.</p>
		</li>
		<li>
			<p><strong>Deaktiviere testweise alle Plugins.</strong></p>
			<p>Wenn ein Theme-Test nichts bringt, deaktiviere alle Plugins und aktiviere sie nacheinander wieder, jeweils mit Reload dazwischen. Sobald der Fehler wieder auftritt, hast du den Konfliktkandidaten gefunden.</p>
		</li>
	</ol>
</div>

<div class="glsr-card card">
	<h3>Support kontaktieren</h3>
	<p>Wenn die Schritte oben nicht helfen, hast du sehr wahrscheinlich einen echten Bug erwischt.</p>
	<p>Schreib eine E-Mail an <a href="mailto:site-reviews@geminilabs.io?subject=Support%20request">site-reviews@geminilabs.io</a> und beschreibe das Problem moeglichst konkret, inklusive reproduzierbarer Schritte.</p>
	<p><span class="required">Wichtig:</span> Bitte haenge auch den Bericht aus dem Tab <a href="<?= admin_url( 'edit.php?post_type=site-review&page=help&tab=system' ); ?>">Systeminfos</a> an.</p>
</div>

</div>
