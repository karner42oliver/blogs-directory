<?php defined( 'WPINC' ) || die; ?>

<div class="glsr-search-box" id="glsr-search-posts">
	<span class="glsr-spinner"><span class="spinner"></span></span>
	<input type="hidden" id="assigned_to" name="assigned_to" value="<?= $id; ?>">
	<input type="search" class="glsr-search-input" autocomplete="off" placeholder="<?= __( 'Tippe zum Suchen...', 'blogs-directory' ); ?>">
	<span class="glsr-search-results"></span>
	<p><?= __( 'Suche hier nach einer Seite oder einem Beitrag, den Du dieser Bewertung zuordnen willst. Du kannst nach Titel oder ID suchen.', 'blogs-directory' ); ?></p>
	<span class="description"><?= $template; ?></span>
</div>

<script type="text/html" id="tmpl-glsr-assigned-post">
<?php include glsr_app()->path . 'views/edit/assigned-post.php'; ?>
</script>
