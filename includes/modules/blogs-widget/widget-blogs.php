<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BlogsWidget' ) ) {
	class BlogsWidget extends WP_Widget {
		public function __construct() {
			parent::__construct(
				'blogs_directory_widget',
				__( 'Blogs-Verzeichnis Widget', 'blogs-directory' ),
				array(
					'description' => __( 'Zeigt eine Liste von Netzwerk-Seiten an.', 'blogs-directory' ),
				)
			);
		}

		public function widget( $args, $instance ) {
			$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Aktuelle Seiten', 'blogs-directory' );
			$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			$number = $number > 0 ? $number : 5;

			echo $args['before_widget'];

			if ( ! empty( $title ) ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			$sites = get_sites(
				array(
					'number'   => $number,
					'public'   => 1,
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
					'orderby'  => 'last_updated',
					'order'    => 'DESC',
				)
			);

			if ( ! empty( $sites ) ) {
				echo '<ul class="blogs-directory-widget-list">';
				foreach ( $sites as $site ) {
					switch_to_blog( (int) $site->blog_id );
					$blog_name = get_option( 'blogname', '' );
					$blog_url = home_url( '/' );
					restore_current_blog();

					if ( '' === $blog_name ) {
						$blog_name = $site->domain . $site->path;
					}

					echo '<li><a href="' . esc_url( $blog_url ) . '">' . esc_html( $blog_name ) . '</a></li>';
				}
				echo '</ul>';
			} else {
				echo '<p>' . esc_html__( 'Keine Seiten gefunden.', 'blogs-directory' ) . '</p>';
			}

			echo $args['after_widget'];
		}

		public function form( $instance ) {
			$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Aktuelle Seiten', 'blogs-directory' );
			$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Titel:', 'blogs-directory' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Anzahl Seiten:', 'blogs-directory' ); ?></label>
				<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr( $number ); ?>" />
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
			$instance['number'] = isset( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
			$instance['number'] = $instance['number'] > 0 ? $instance['number'] : 5;

			return $instance;
		}
	}
}

if ( ! function_exists( 'blogs_directory_register_blogs_widget' ) ) {
	function blogs_directory_register_blogs_widget() {
		register_widget( 'BlogsWidget' );
	}

	add_action( 'widgets_init', 'blogs_directory_register_blogs_widget' );
}
