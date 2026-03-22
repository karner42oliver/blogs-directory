<?php defined( 'WPINC' ) || die; ?>

<form method="post" action="" name="glsr-<?= $form_id; ?>" class="<?= $class; ?>">
<?php

	echo $html->renderField(['type' => 'honeypot']);

	echo $html->renderField([
		'type'       => 'select',
		'name'       => 'rating',
		'class'      => 'glsr-star-rating',
		'errors'     => $errors,
		'label'      => __( 'Deine Gesamtbewertung', 'blogs-directory' ),
		'prefix'     => false,
		'render'     => !in_array( 'rating', $exclude ),
		'suffix'     => $form_id,
		'value'      => $values['rating'],
		'options'    => [
			''  => __( 'Bewertung auswaehlen', 'blogs-directory' ),
			'5' => __( 'Ausgezeichnet', 'blogs-directory' ),
			'4' => __( 'Sehr gut', 'blogs-directory' ),
			'3' => __( 'Durchschnittlich', 'blogs-directory' ),
			'2' => __( 'Schwach', 'blogs-directory' ),
			'1' => __( 'Sehr schlecht', 'blogs-directory' ),
		],
	]);

	echo $html->renderField([
		'type'        => 'text',
		'name'        => 'title',
		'errors'      => $errors,
		'label'       => __( 'Titel Deiner Bewertung', 'blogs-directory' ),
		'placeholder' => __( 'Fasse Deine Bewertung kurz zusammen oder nenne ein interessantes Detail', 'blogs-directory' ),
		'prefix'      => false,
		'render'      => !in_array( 'title', $exclude ),
		'required'    => in_array( 'title', glsr_get_option( 'reviews-form.required', [] )),
		'suffix'      => $form_id,
		'value'       => $values['title'],
	]);

	echo $html->renderField([
		'type'        => 'textarea',
		'name'        => 'content',
		'errors'      => $errors,
		'label'       => __( 'Deine Bewertung', 'blogs-directory' ),
		'placeholder' => __( 'Erzaehl den Leuten von Deiner Erfahrung', 'blogs-directory' ),
		'prefix'      => false,
		'rows'        => 5,
		'render'      => !in_array( 'content', $exclude ),
		'required'    => in_array( 'content', glsr_get_option( 'reviews-form.required', [] )),
		'suffix'      => $form_id,
		'value'       => $values['content'],
	]);

	echo $html->renderField([
		'type'        => 'text',
		'name'        => 'name',
		'errors'      => $errors,
		'label'       => __( 'Dein Name', 'blogs-directory' ),
		'placeholder' => __( 'Sag uns Deinen Namen', 'blogs-directory' ),
		'prefix'      => false,
		'render'      => !in_array( 'name', $exclude ),
		'required'    => in_array( 'name', glsr_get_option( 'reviews-form.required', [] )),
		'suffix'      => $form_id,
		'value'       => $values['name'],
	]);

	echo $html->renderField([
		'type'        => 'email',
		'name'        => 'email',
		'errors'      => $errors,
		'label'       => __( 'Deine E-Mail', 'blogs-directory' ),
		'placeholder' => __( 'Sag uns Deine E-Mail', 'blogs-directory' ),
		'prefix'      => false,
		'render'      => !in_array( 'email', $exclude ),
		'required'    => in_array( 'email', glsr_get_option( 'reviews-form.required', [] )),
		'suffix'      => $form_id,
		'value'       => $values['email'],
	]);

	echo $html->renderField([
		'type'       => 'checkbox',
		'name'       => 'terms',
		'errors'     => $errors,
		'options'    => __( 'Diese Bewertung basiert auf meiner eigenen Erfahrung und gibt meine ehrliche Meinung wieder.', 'blogs-directory' ),
		'prefix'     => false,
		'render'     => !in_array( 'terms', $exclude ),
		'required'   => true,
		'suffix'     => $form_id,
		'value'      => $values['terms'],
	]);

	echo $html->renderField([
		'type'   => 'hidden',
		'name'   => 'action',
		'prefix' => false,
		'value'  => 'post-review',
	]);

	echo $html->renderField([
		'type'   => 'hidden',
		'name'   => 'form_id',
		'prefix' => false,
		'value'  => $form_id,
	]);

	echo $html->renderField([
		'type'   => 'hidden',
		'name'   => 'assign_to',
		'prefix' => false,
		'value'  => $assign_to,
	]);

	echo $html->renderField([
		'type'   => 'hidden',
		'name'   => 'category',
		'prefix' => false,
		'value'  => $category,
	]);

	echo $html->renderField([
		'type'   => 'hidden',
		'name'   => 'excluded',
		'prefix' => false,
		'value'  => esc_attr( json_encode( $exclude )),
	]);

	wp_nonce_field( 'post-review' );

	if( $message ) {
		printf( '<div class="glsr-form-messages%s">%s</div>', ( $errors ? ' gslr-has-errors' : '' ), wpautop( $message ));
	}

	echo $html->renderField([
		'type'   => 'submit',
		'prefix' => false,
		'value'  => __( 'Bewertung absenden', 'blogs-directory' ),
	]);

?>
</form>
