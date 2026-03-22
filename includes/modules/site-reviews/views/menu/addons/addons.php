<?php defined( 'WPINC' ) || die; ?>

<p><?= __( 'Die folgenden Add-ons erweitern die Funktionen von Site Reviews.', 'blogs-directory' ); ?></p>

<div class="glsr-addons wp-clearfix">

<?php

	echo $html->renderPartial( 'addon', [
		'name'        => 'tripadvisor',
		'title'       => __( 'Bald verfuegbar in v3.0.0', 'blogs-directory' ),
		'description' => __( 'Synchronisiere Deine Tripadvisor-Bewertungen mit optionaler Mindestbewertung und zeige sie auf Deiner Seite an.', 'blogs-directory' ),
		'link'        => '',
	]);

	echo $html->renderPartial( 'addon', [
		'name'        => 'yelp',
		'title'       => __( 'Bald verfuegbar in v3.0.0', 'blogs-directory' ),
		'description' => __( 'Synchronisiere Deine Yelp-Bewertungen mit optionaler Mindestbewertung und zeige sie auf Deiner Seite an.', 'blogs-directory' ),
		'link'        => '',
	]);

?>

</div>
