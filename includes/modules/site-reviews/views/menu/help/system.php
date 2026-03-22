<?php defined( 'WPINC' ) || die; ?>

<br>

<form method="post"><?php
	wp_nonce_field( 'download-system-info' );
	printf( '<textarea rows="13" name="%s[system-info]" class="glsr-log" onclick="this.select()" readonly>%s</textarea>', glsr_app()->prefix, $system_info->getAll() );
	printf( '<input type="hidden" name="%s[action]" value="download-system-info">', glsr_app()->prefix );
	submit_button( __( 'Systeminfos herunterladen', 'blogs-directory' ), 'secondary', '', false ); ?>
</form>

<br>

<h3><?= __( 'Protokollierung', 'blogs-directory' ); ?></h3>
<p><?= __( 'Du kannst die Protokollierung zum Debuggen aktivieren. Logdateien koennen schnell wachsen, deshalb solltest Du das nicht dauerhaft aktiviert lassen.', 'blogs-directory' ); ?></p>

<?php $logging_enabled = $db->getOption( 'logging', 0 ); ?>

<?php if( $logging_enabled == 1 ) : ?>

<textarea rows="13" id="log-file" class="glsr-log" onclick="this.select()" readonly><?= $log; ?></textarea>

<form method="post" class="float-left"><?php
	wp_nonce_field( 'clear-log' );
	printf( '<input type="hidden" name="%s[action]" value="clear-log">', glsr_app()->prefix );
	submit_button( __( 'Log leeren', 'blogs-directory' ), 'secondary', 'clear-log', false ); ?>
</form>

<form method="post"><?php
	wp_nonce_field( 'download-log' );
	printf( '<input type="hidden" name="%s[action]" value="download-log">', glsr_app()->prefix );
	submit_button( __( 'Log herunterladen', 'blogs-directory' ), 'secondary', '', false ); ?>
</form>

<?php endif; ?>

<form method="post" action="<?= admin_url('options.php'); ?>"><?php

	settings_fields( glsr_app()->id . '-logging' );
	do_settings_sections( glsr_app()->id . '-logging' );

	if( $logging_enabled == 1 ) {
		printf( '<input type="hidden" name="%s[logging]" value="0">', glsr_resolve( 'Database' )->getOptionName() );
		submit_button( __( 'Protokollierung deaktivieren', 'blogs-directory' ));
	}
	else {
		printf( '<input type="hidden" name="%s[logging]" value="1">', glsr_resolve( 'Database' )->getOptionName() );
		submit_button( __( 'Protokollierung aktivieren', 'blogs-directory' ) );
	}?>
</form>
