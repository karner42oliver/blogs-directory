<?php defined( 'WPINC' ) || die; ?>

<form method="post" action="">

	<?php
		echo $html->p( sprintf( _x( 'Alle hier angezeigten Datums- und Zeitangaben verwenden die WordPress-%s.', 'configured timezone', 'blogs-directory' ),
			sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php' ), __( 'Zeitzone', 'blogs-directory' ))
		));
	?>

	<table class="wp-list-table widefat fixed striped glsr-status">

		<thead>
			<tr>
				<th class="site"><?= __( 'Seite', 'blogs-directory' ); ?></th>
				<th class="total-fetched column-primary"><?= __( 'Bewertungen', 'blogs-directory' ); ?></th>
				<th class="last-fetch"><?= __( 'Letzter Abruf', 'blogs-directory' ); ?></th>
				<th class="next-fetch"><?= __( 'Naechster geplanter Abruf', 'blogs-directory' ); ?></th>
			</tr>
		</thead>

		<tbody>

		<?php foreach( $tabs['settings']['sections'] as $key => $title ) : ?>

			<tr data-type="<?= $key; ?>">
				<td class="site">
					<a href="<?= admin_url( 'edit.php?post_type=site-review&page=' . glsr_app()->id . "&tab=settings&section={$key}" ); ?>"><?= $title; ?></a>
				</td>
				<td class="total-fetched column-primary">
					<a href="<?= admin_url( "edit.php?post_type=site-review&post_status=all&type={$key}" ); ?>"><?= $db->getReviewCount( 'type', $key ); ?></a>
					<button type="button" class="toggle-row"><span class="screen-reader-text"><?= __( 'Mehr Details anzeigen', 'blogs-directory' ); ?></span></button>
				</td>
				<td class="last-fetch" data-colname="<?= __( 'Letzter Abruf', 'blogs-directory' ); ?>">
					<?= $db->getOption( 'last_fetch.' . $key, __( 'Es wurde noch kein Abruf abgeschlossen', 'blogs-directory' )); ?>
				</td>
				<td class="next-fetch" data-colname="<?= __( 'Naechster geplanter Abruf', 'blogs-directory' ); ?>">
					<?= $db->getOption( 'next_fetch.' . $key, __( 'Aktuell ist nichts geplant', 'blogs-directory' )); ?>
				</td>
			</tr>

		<?php endforeach; ?>

		</tbody>

	</table>

	<br>

	<hr>

	<table class="form-table">
		<tbody>

		<?php

			echo $html->row()->select( 'type', [
				'label'      => __( 'Bewertungen abrufen von', 'blogs-directory' ),
				'options'    => $tabs['settings']['sections'],
				'attributes' => 'data-type',
				'prefix'     => glsr_app()->prefix,
			]);

			echo $html->row()->progress([
				'label'  => __( 'Abrufstatus', 'blogs-directory' ),
				'active' => __( 'Bewertungen werden abgerufen...', 'blogs-directory' ),
				'class'  => 'green',
			]);
		?>

		</tbody>
	</table>

	<?php wp_nonce_field( 'fetch-reviews' ); ?>

	<?php printf( '<input type="hidden" name="%s[action]" value="fetch-reviews">', glsr_app()->prefix ); ?>

	<?php submit_button( __( 'Bewertungen abrufen', 'blogs-directory' ), 'large primary', 'fetch-reviews' ); ?>

</form>
