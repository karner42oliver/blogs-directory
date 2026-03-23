<?php global $current_blog ?>
<form id="gss-ajax-search-form" action="#" method="get">
  <input type="hidden" name="gss_ajax_nonce" value="<?php echo esc_attr( global_site_search_get_ajax_nonce() ); ?>">
	<table border="0" cellpadding="2" cellspacing="2" style="width:100%">
		<tr>
			<td style="width:80%">
        <input type="text" name="phrase" id="gss-ajax-phrase" style="width:100%" value="<?php echo esc_attr( global_site_search_get_phrase() ) ?>">
			</td>
			<td style="text-align:right;width:20%">
				<input type="submit" value="<?php _e( 'Suchen', 'globalsitesearch' ) ?>">
			</td>
		</tr>
	</table>
</form>
<div id="gss-ajax-results"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('gss-ajax-search-form');
  var results = document.getElementById('gss-ajax-results');
  var ajaxUrl = <?php echo wp_json_encode( global_site_search_get_ajax_url() ); ?>;
  var nonceField = form ? form.querySelector('input[name="gss_ajax_nonce"]') : null;
  if (!form || !results || !nonceField) {
    return;
  }
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var phrase = document.getElementById('gss-ajax-phrase').value;
    if (!phrase) return;
    results.innerHTML = '<div style="color:#888;">Suche läuft...</div>';
    var body = new URLSearchParams();
    body.append('action', 'global_site_search_live');
    body.append('phrase', phrase);
    body.append('nonce', nonceField.value);
    fetch(ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: body.toString()
    })
      .then(r => r.text())
      .then(html => { results.innerHTML = html; });
  });
});
</script>