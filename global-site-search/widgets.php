<?php

class Global_Site_Search_Widget extends WP_Widget {

	public function __construct() {
		$widget_options = array(
			'classname'   => 'global-site-search',
			'description' => __( 'Netzwerksuche Widget', 'postindexer' ),
		);

		$control_options = array(
			'id_base' => 'global-site-search-widget',
		);

		parent::__construct( 'global-site-search-widget', __( 'Netzwerksuche Widget', 'postindexer' ), $widget_options, $control_options );
	}

	function widget( $args, $instance ) {
		global $global_site_search, $wp_query, $wpdb;

		extract( $args );

		echo $before_widget;

		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		// Suchformular
		$phrase = isset($_GET['gss_widget_phrase']) ? sanitize_text_field(wp_unslash($_GET['gss_widget_phrase'])) : '';
		// Widget-Suchformular per AJAX, damit kein Redirect erfolgt
		echo '<form id="gss-widget-form-' . esc_attr($this->id) . '" action="#" method="get">';
		echo '<input type="hidden" name="gss_widget_nonce" value="' . esc_attr( global_site_search_get_ajax_nonce() ) . '">';
		echo '<input type="text" name="gss_widget_phrase" value="' . esc_attr($phrase) . '" placeholder="' . esc_attr__('Suchbegriff...', 'postindexer') . '" style="width:70%;margin-right:0.5em;">';
		echo '<input type="submit" value="' . esc_attr__('Suchen', 'postindexer') . '">';
		echo '</form>';
		echo '<div id="gss-widget-results-' . esc_attr($this->id) . '">';
		if ($phrase !== '') {
			echo global_site_search_render_live_results( $phrase, 5, true );
		}
		echo '</div>';
		// AJAX-Handler für das Widget
		echo '<script>document.addEventListener("DOMContentLoaded",function(){
            var form=document.getElementById("gss-widget-form-' . esc_attr($this->id) . '");
            var results=document.getElementById("gss-widget-results-' . esc_attr($this->id) . '");
            var ajaxUrl=' . wp_json_encode( global_site_search_get_ajax_url() ) . ';
            if(form){
                form.addEventListener("submit",function(e){
                    e.preventDefault();
                    var phrase=form.querySelector("input[name=gss_widget_phrase]").value;
                    var nonce=form.querySelector("input[name=gss_widget_nonce]").value;
                    if(!phrase) return;
                    results.innerHTML=\'<div style="color:#888;">Suche läuft...</div>\';
                    var body=new URLSearchParams();
                    body.append("action","global_site_search_widget_live");
                    body.append("phrase",phrase);
                    body.append("nonce",nonce);
                    fetch(ajaxUrl,{
                        method:"POST",
                        headers:{
                            "Content-Type":"application/x-www-form-urlencoded; charset=UTF-8",
                            "X-Requested-With":"XMLHttpRequest"
                        },
                        body:body.toString()
                    })
                        .then(r=>r.text())
                        .then(html=>{results.innerHTML=html;});
                });
            }
        });</script>';

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array)$instance, array(
			'title' => __( 'Netzwerksuche', 'postindexer' ),
		) );
		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Titel', 'postindexer' ) ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>" class="widefat">
		</p><?php
	}

}

// Integration als Erweiterung für den Beitragsindexer
add_action('plugins_loaded', function() {
	if ( !class_exists('Postindexer_Extensions_Admin') ) return;
	global $postindexer_extensions_admin;
	if ( !isset($postindexer_extensions_admin) ) {
		if ( isset($GLOBALS['postindexeradmin']) && isset($GLOBALS['postindexeradmin']->extensions_admin) ) {
			$postindexer_extensions_admin = $GLOBALS['postindexeradmin']->extensions_admin;
		}
	}
	if ( isset($postindexer_extensions_admin) && $postindexer_extensions_admin->is_extension_active_for_site('global_site_search') ) {
		add_action( 'widgets_init', 'global_site_search_load_widgets' );
	}
});

// Entfernt: add_action( 'widgets_init', 'global_site_search_load_widgets' );
function global_site_search_load_widgets() {
	if ( in_array( get_current_blog_id(), global_site_search_get_allowed_blogs() ) ) {
		register_widget( 'Global_Site_Search_Widget' );
	}
}