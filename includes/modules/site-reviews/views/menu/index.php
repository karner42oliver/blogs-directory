<?php defined( 'WPINC' ) || die; ?>

<div class="wrap glsr-modern-admin" id="glsr-admin-app">

	<h1 class="page-title"><?= esc_html( get_admin_page_title() ); ?></h1>

<?php

	printf( '<div id="glsr-notices">%s</div>', $notices->show( false ) );

	echo $html->renderPartial( 'tabs' , [
		'page' => $page,
		'tabs' => $tabs,
		'tab'  => $tabView,
	]);

	echo $html->renderPartial( 'subsubsub' , [
		'page'    => $page,
		'tabs'    => $tabs,
		'tab'     => $tabView,
		'section' => $tabViewSection,
	]);

	$file = $tabViewSection ? sprintf( '%s/%s', $tabView, $tabViewSection ) : $tabView;
	$file = trailingslashit( __DIR__ ) . sprintf( '%s/%s.php', $page, $file );

	$file = apply_filters( 'site-reviews/addon/views/file', $file, $view, $data );

	echo '<div id="glsr-tab-content" class="glsr-tab-content" aria-live="polite">';

	if( file_exists( $file ) ) {
		include $file;
	}
	else {
		$log->error( sprintf( 'File not found: %s', $file ) );
	}

	echo '</div>';

?>

</div>
