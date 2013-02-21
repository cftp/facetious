<?php

/**
 * The main template function for outputting a Facetious search form.
 *
 * @param $args array Arguments for this form
 * @return string The markup for the form
 * @author John Blackbourn
 **/
function facetious( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'fields' => array(
			's'
		),
		'submit' => __( 'Go', 'facetious' ),
		'echo'   => true,
		'class'  => 'facetious_form',
		'id'     => 'facetious_form',
	) );

	$out = '';

	$out .= '<form action="' . home_url( '/' ) . '" class="' . esc_attr( $args['class'] ) . '"';

	if ( $args['id'] )
		$out .= ' id="' . esc_attr( $args['id'] ) . '"';

	$out .= '>';

	if ( isset( $args['post_type'] ) and( '-1' !== $args['post_type'] ) and ! isset( $args['post_type'][ 'pt' ] ) )
		$out .= sprintf( '<input type="hidden" name="post_type" value="%s" />', esc_attr( reset( $args['post_type'] ) ) );

	foreach ( $args['fields'] as $key => $val ) {

		if ( is_numeric( $key ) ) {
			$key = $val;
			$val = true;
		}

		if ( !is_array( $val ) ) {
			$val = array(
				'label' => $val
			);
		}

		# Switch depending on what we're asking for.
		# Currently everything that's not 's' or 'm' is treated as a taxonomy.
		switch ( $key ) {

			# Keyword search input:
			case 's':

				if ( empty( $val['label'] ) or ( true === $val['label'] ) )
					$val['label'] = __( 'Search by keyword', 'facetious' );
				if ( !isset( $val['class'] ) )
					$val['class'] = 'facetious_input facetious_input_search';
				if ( !isset( $val['id'] ) )
					$val['id'] = 'facetious_input_search';

				$out .= sprintf( '<p class="%s">', 'facetious_search' );
				$out .= sprintf( '<label for="%1$s">%2$s</label>',
					esc_attr( $val['id'] ),
					$val['label']
				);
				$out .= sprintf( '<input type="text" name="s" value="%1$s" class="%2$s" id="%3$s" />',
					esc_attr( get_search_query( false ) ),
					esc_attr( $val['class'] ),
					esc_attr( $val['id'] )
				);
				$out .= '</p>';

				break;

			# Post type dropdown:
			case 'pt':

				$post_types = array();
				if ( isset( $val[ 'values' ] ) ) {
					if ( ! is_array( $val[ 'values' ] ) )
						$val[ 'values' ] = (array) $val[ 'values' ];
					foreach ( $val[ 'values' ] as $pt ) {
						if ( is_object( $pto = get_post_type_object( $pt ) ) )
							$post_types[ $pt ] = $pto;
					}
				} else {
					$post_type_names = get_post_types( array( 'publicly_queryable' => true ) );
					foreach ( $post_type_names as $pt ) {
						if ( is_object( $pto = get_post_type_object( $pt ) ) )
							$post_types[ $pt ] = $pto;
					}
				}

				if ( empty( $post_types ) )
					continue;

				if ( empty( $val['label'] ) or ( true === $val['label'] ) )
					$val['label'] = __( 'Filter by type', 'facetious' );
				if ( !isset( $val['class'] ) )
					$val['class'] = 'facetious_filter facetious_filter_post_type';
				if ( !isset( $val['id'] ) )
					$val['id'] = 'facetious_filter_post_type';
				if ( !isset( $val['all'] ) )
					$val['all'] = __( 'All types', 'facetious' );

				$out .= sprintf( '<p class="%s">', 'facetious_post_type' );
				$out .= sprintf( '<label for="%1$s">%2$s</label>',
					esc_attr( $val['id'] ),
					$val['label']
				);
				$out .= sprintf( '<select name="post_type" class="%1$s" id="%2$s" />',
					esc_attr( $val['class'] ),
					esc_attr( $val['id'] )
				);
				$out .= sprintf( '<option value="">%s</option>',
					esc_html( $val['all'] )
				);
				foreach ( $post_types as $pt => $pto ) {
					$out .= sprintf( '<option value="%s"%s>%s</option>',
						esc_attr( $pt ),
						selected( $pt, get_query_var( 'post_type' ), false ),
						esc_html( $pto->labels->name )
					);
				}
				$out .= '</select>';
				$out .= '</p>';

				break;

			# Month dropdown:
			case 'm':

				$months = facetious_get_available_months();

				if ( empty( $months ) )
					continue;

				if ( empty( $val['label'] ) or ( true === $val['label'] ) )
					$val['label'] = __( 'Month', 'facetious' );
				if ( !isset( $val['class'] ) )
					$val['class'] = 'facetious_filter facetious_filter_month';
				if ( !isset( $val['id'] ) )
					$val['id'] = 'facetious_filter_month';
				if ( !isset( $val['all'] ) )
					$val['all'] = __( 'Any Month', 'facetious' );

				$out .= sprintf( '<p class="%s">', 'facetious_month' );
				$out .= sprintf( '<label for="%1$s">%2$s</label>',
					esc_attr( $val['id'] ),
					$val['label']
				);
				$out .= sprintf( '<select name="m" class="%1$s" id="%2$s" />',
					esc_attr( $val['class'] ),
					esc_attr( $val['id'] )
				);
				$out .= sprintf( '<option value="">%s</option>',
					esc_html( $val['all'] )
				);
				foreach ( $months as $month_key => $month_val ) {
					$out .= sprintf( '<option value="%s"%s>%s</option>',
						esc_attr( $month_key ),
						selected( $month_key, get_query_var( 'm' ), false ),
						esc_html( $month_val )
					);
				}
				$out .= '</select>';
				$out .= '</p>';

				break;

			# Taxonomy dropdown:
			default:

				$tax = get_taxonomy( $key );

				if ( empty( $tax ) )
					continue;

				if ( !isset( $val['options'] ) ) {

					$terms = get_terms( $key, array(
						'hide_empty' => false
					) );

					if ( empty( $terms ) )
						continue;

					foreach ( $terms as $term )
						$val['options'][$term->slug] = $term->name;

				}

				if ( empty( $val['label'] ) or ( true === $val['label'] ) )
					$val['label'] = $tax->labels->singular_name;
				if ( !isset( $val['class'] ) )
					$val['class'] = sprintf( 'facetious_filter facetious_filter_%s', $tax->name );
				if ( !isset( $val['id'] ) )
					$val['id'] = sprintf( 'facetious_filter_%s', $tax->name );
				if ( !isset( $val['all'] ) )
					$val['all'] = $tax->labels->all_items;

				$out .= sprintf( '<p class="facetious_%s">', $tax->name );
				$out .= sprintf( '<label for="%1$s">%2$s</label>',
					esc_attr( $val['id'] ),
					$val['label']
				);

				$out .= sprintf( '<select name="%1$s" class="%2$s" id="%3$s" />',
					$tax->query_var,
					esc_attr( $val['class'] ),
					esc_attr( $val['id'] )
				);
				$out .= sprintf( '<option value="">%s</option>',
					esc_html( $val['all'] )
				);
				foreach ( $val['options'] as $value => $label ) {
					$out .= sprintf( '<option value="%s"%s>%s</option>',
						esc_attr( $value ),
						selected( $value, get_query_var( $tax->query_var ), false ),
						esc_html( $label )
					);
				}
				$out .= '</select>';
				$out .= '</p>';

				break;

		}

	}

	$out .= '<p class="facetious_submit">';
	$out .= sprintf( '<input type="submit" value="%s" class="facetious_submit_button" />', esc_attr( $args['submit'] ) );
	$out .= '</p>';

	$out .= '</form>';

	if ( $args['echo'] )
		echo $out;

	return $out;

}

/**
 * Helper function for retrieving a list of populated months for a given post type
 *
 * @param $post_type string An optional post type to restrict the query to
 * @return array The populated months
 * @author John Blackbourn
 **/
function facetious_get_available_months( $post_type = null ) {

	global $wpdb, $wp_locale;

	if ( $post_type )
		$where = $wpdb->prepare( 'WHERE post_type = %s', $post_type );
	else
		$where = '';

	$months = $wpdb->get_results( "
		SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
		FROM {$wpdb->posts}
		{$where}
		ORDER BY post_date DESC
	" );

	$month_count = count( $months );

	if ( !$month_count )
		return array();
	if ( ( 1 == $month_count ) and ( 0 == $months[0]->month ) )
		return array();

	$available_months = array();

	foreach ( $months as $arc_row ) {

		if ( 0 == $arc_row->year )
			continue;

		$month = zeroise( $arc_row->month, 2 );

		# @TODO _x() this:
		$available_months[$arc_row->year . $month] = sprintf( __( '%1$s %2$d', 'facetious' ),
			$wp_locale->get_month( $month ),
			$arc_row->year
		);

	}

	return $available_months;

}
