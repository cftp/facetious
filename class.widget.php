<?php

/**
 * The Facetious sidebar widget class
 **/

class Facetious_Widget extends WP_Widget {

	/**
	 * Class constructor
	 *
	 * @author John Blackbourn
	 **/
	function __construct() {
		parent::__construct( false, __( 'Facetious Search', 'facetious' ), array(
			'description' => __( 'A faceted search form for your site', 'facetious' )
		) );	
	}

	/**
	 * The widget output
	 *
	 * @param $args array The arguments for the current sidebar and current widget
	 * @param $instance array Specific arguments (eg. user-defined values) for the current widget instance
	 * @return null
	 * @author John Blackbourn
	 **/
    function widget( $args, $instance ) {

		echo $args['before_widget'];

		if ( !empty( $instance['title'] ) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];

		$facetious_args = array(
			'fields' => $instance['fields'],
			'id'     => false
		);

		if ( isset( $instance['post_type'] ) and ( '-1' !== $instance['post_type'] ) )
			$facetious_args['post_type'] = $instance['post_type'];

		facetious( $facetious_args );

		echo $args['after_widget'];

	}

	/**
	 * Return a list of the available search fields for a widget
	 *
	 * @return array List of fields keyed to their name
	 * @author John Blackbourn
	 **/
	function get_search_fields() {

		$fields = array(
			's' => __( 'Keyword Search', 'facetious' )
		);

		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $tax )
			$fields[$tax->name] = $tax->labels->singular_name;

		$fields['m'] = __( 'Month', 'facetious' );
		$fields['pt'] = __( 'Post type', 'facetious' );

		return $fields;

	}

	/**
	 * Return a list of the available post type restrictions for our widget
	 *
	 * @return array List of post types keyed to their name
	 * @author John Blackbourn
	 **/
	function get_search_post_types() {

		$types = array(
			'-1' => __( 'No Restriction', 'facetious' )
		);

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type )
			$types[$type->name] = $type->labels->singular_name;

		return $types;

	}

	/**
	 * The widget's options form
	 *
	 * @param $instance array Specific arguments (eg. user-defined values) for the current widget instance
	 * @author John Blackbourn
	 **/
	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(
			'title'     => __( 'Search', 'facetious' ),
			'submit'    => __( 'Go', 'facetious' ),
			'fields'    => array( 's' ),
			'post_type' => array( '-1' ),
		) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:', 'facetious' ); ?></label>
			<input class="widefat" type="text"
				id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>"
				value="<?php echo esc_attr( $instance['title'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'submit' ); ?>"><?php _e( 'Submit Button:', 'facetious' ); ?></label>
			<input class="widefat" type="text"
				id="<?php echo $this->get_field_id( 'submit' ); ?>"
				name="<?php echo $this->get_field_name( 'submit' ); ?>"
				value="<?php echo esc_attr( $instance['submit'] ); ?>"
			/>
		</p>
		<p>
			<?php _e( 'Fields:', 'facetious' ); ?>
		</p>

		<p>
		<?php

		foreach ( $this->get_search_fields() as $key => $val ) {
			?>
			<label>
				<input type="checkbox" class="checkbox" value="<?php echo esc_attr( $key ); ?>"
					name="<?php echo $this->get_facetious_field_name( 'fields' ); ?>"
					<?php checked( in_array( $key, $instance['fields'] ) ); ?>
				/>
				<?php echo esc_html( $val ); ?>
			</label><br />
			<?php
		}

		?>
		</p>

		<p>
			<?php _e( 'Post Type:', 'facetious' ); ?>
		</p>

		<p>
		<?php

		foreach ( $this->get_search_post_types() as $key => $val ) {
			?>
			<label>
				<input type="radio" class="radio" value="<?php echo esc_attr( $key ); ?>"
					name="<?php echo $this->get_facetious_field_name( 'post_type' ); ?>"
					<?php checked( in_array( $key, $instance['post_type'] ) ); ?>
				/>
				<?php echo esc_html( $val ); ?>
			</label><br />
			<?php
		}

		?>
		</p>
		<?php

	}

	/**
	 * A helper function for correctly outputting an input's name when it needs to be an array format
	 *
	 * See: http://core.trac.wordpress.org/ticket/12133
	 *
	 * @param $field_name string The name of the input field
	 * @return The input field name in array format
	 * @author John Blackbourn
	 **/
	function get_facetious_field_name( $field_name ) {

		return sprintf( 'widget-%s[%s][%s][]',
			$this->id_base,
			$this->number,
			$field_name
		);

	}

	/**
	 * Register our widget
	 *
	 * @author John Blackbourn
	 **/
	public static function register() {
		register_widget( get_class() );
	}

}

add_action( 'widgets_init', array( 'Facetious_Widget', 'register' ) );

?>