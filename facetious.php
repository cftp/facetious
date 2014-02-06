<?php
/*
Plugin Name:  Facetious Search
Plugin URI:   https://github.com/cftp/facetious
Description:  A faceted search interface for WordPress
Version:      1.2
Author:       Code for the People
Author URI:   http://codeforthepeople.com/
Text Domain:  facetious
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2013 Code for the People ltd

                _____________
               /      ____   \
         _____/       \   \   \
        /\    \        \___\   \
       /  \    \                \
      /   /    /          _______\
     /   /    /          \       /
    /   /    /            \     /
    \   \    \ _____    ___\   /
     \   \    /\    \  /       \
      \   \  /  \____\/    _____\
       \   \/        /    /    / \
        \           /____/    /___\
         \                        /
          \______________________/


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

require_once dirname( __FILE__ ) . '/class.plugin.php';
require_once dirname( __FILE__ ) . '/class.widget.php';
require_once dirname( __FILE__ ) . '/template.php';

class Facetious extends Facetious_Plugin {

	/**
	 * Singleton stuff.
	 * 
	 * @access @static
	 * 
	 * @return Facetious
	 */
	static public function init() {
		static $instance = false;

		if ( ! $instance )
			$instance = new Facetious;

		return $instance;

	}

	/**
	 * Class constructor. Set up some filters and actions.
	 *
	 * @return null
	 * @author John Blackbourn
	 */
	function __construct() {

		# Actions:
		add_action( 'init',                 array( $this, 'action_init' ) );
		add_action( 'admin_init',           array( $this, 'action_admin_init' ) );
		add_action( 'parse_request',        array( $this, 'action_parse_request' ) );
		add_action( 'template_redirect',    array( $this, 'action_template_redirect' ) );
		add_action( 'facetious',            'facetious' );
		add_action( 'parse_query',          array( $this, 'action_parse_query' ) );

		# Filters:
		add_filter( 'query_vars',           array( $this, 'filter_query_vars' ) );
		add_filter( 'search_rewrite_rules', array( $this, 'filter_search_rewrite_rules' ) );
		add_filter( 'request',              array( $this, 'filter_request' ) );

		# Set up the plugin from the parent class:
		parent::__construct( __FILE__ );

	}

	/**
	 * Redirects a client to a pretty URL after performing a Facetious search
	 *
	 * @param WP $wp A WP class object (passed by ref)
	 * @return null
	 * @author Simon Wheatley
	 **/
	function action_parse_request( WP $wp ) {
		if ( isset( $wp->query_vars[ 'facetious_post_type' ] ) ) {
			$wp->query_vars[ 'post_type' ] = $wp->query_vars[ 'facetious_post_type' ];
		}
	}

	/**
	 * Explicitly set that this is_search, as without a 's'
	 * query var it may be interpreted as one type of archive
	 * or another.
	 *
	 * @param WP_Query $wp_query A WP_Query object (passed by ref)
	 * @return null
	 * @author Simon Wheatley
	 **/
	function action_parse_query( WP_Query $wp_query ) {
		if ( ! $wp_query->is_main_query() )
			return;
		if ( isset( $wp_query->query[ 'facetious' ] ) && ! empty( $wp_query->query[ 'facetious' ] ) )
			$wp_query->is_search = true;
	}

	/**
	 * Redirects a client to a pretty URL after performing a Facetious search
	 *
	 * @return null
	 * @author John Blackbourn
	 **/
	function action_template_redirect() {
		global $wp_rewrite, $wp_query;

		if ( !$wp_rewrite->using_permalinks() )
			return;

		# Note the explicit use of isset() on the 's' query var instead of using get_query_var('s'). The
		# condition we're looking for is the presence of the 's' query string parameter even when it's empty.
		if ( !isset( $wp_query->query['s'] ) )
			return;

		# Bail if we're already viewing a pretty URL
		$base  = $this->get_search_base();
		if ( false !== strpos( $_SERVER['REQUEST_URI'], "/{$base}/" ) )
			return;

		wp_redirect( $this->construct_query_url( $wp_query->query ) , 301 );
		exit;
	}

	/**
	 * Constructs a Facetious URL from a WP_Query::query_vars like array
	 * of parameters.
	 *
	 * @param array $query A WP_Query::query like array of parameters
	 * @return string A Facetious format URL
	 * @author Simon Wheatley
	 **/
	function construct_query_url( $query ) {
		$parts = array();
		$base  = $this->get_search_base();

		# Build the array containing alternating keys and values
		foreach ( $query as $key => $val ) {
			if ( 'post_type' == $key )
				continue;
			if ( '' !== $val ) {
				$parts[] = $this->get_search_part( $key );
				$parts[] = $this->encode( stripslashes( $val ) );
			}
		}

		# Special case: If we've only got a keyword search parameter then we can strip the
		# leading 'keyword/' part for brevity
		if ( 2 == count( $parts ) and in_array( $this->get_search_part( 's' ), $parts ) )
			array_shift( $parts );

		$parts = implode( '/', $parts );

		# We use untrailingslashit() here to avoid getting double-slashed URLs when using WPML
		$url = untrailingslashit( home_url() ) . "/{$base}/{$parts}";

		# Add a trailing slash if our permastruct has one
		if ( '/' == substr( get_option( 'permalink_structure' ), -1 ) )
			$url = trailingslashit( $url );

		return $url;

	}

	/**
	 * Overwrites WordPress' default search rewrite rules with our own. Passes the entire search
	 * string to the facetious query var.
	 *
	 * @param $rules array List of rewrite rules for searches
	 * @return array New list of rewrite rules for searches
	 * @author John Blackbourn
	 **/
	function filter_search_rewrite_rules( array $rules ) {

		return array(
			$this->get_search_base() . '/(.+)/?$' => 'index.php?facetious=$matches[1]',
		);

	}

	/**
	 * Retrieve the part name (for use in a URL) for a given query variable
	 *
	 * @param $var string The name of a query variable
	 * @return string The corresponding name for use in a URL
	 * @author John Blackbourn
	 **/
	function get_search_part( $var ) {

		$search_parts = $this->get_search_parts();

		if ( isset( $search_parts[$var] ) )
			return $search_parts[$var];
		else
			return $var;

	}

	/**
	 * Retrieve the query variable for a given URL part
	 *
	 * @param $var string The name of a URL part
	 * @return string The corresponding query variable name
	 * @author John Blackbourn
	 **/
	function get_search_var( $part ) {

		$search_parts = array_flip( $this->get_search_parts() );

		if ( isset( $search_parts[$part] ) )
			return $search_parts[$part];
		else
			return $part;

	}

	/**
	 * Fetch all the available URL parts mapped to their query variable name
	 *
	 * @return array Array of URL parts keyed to their query variable
	 * @author John Blackbourn
	 **/
	function get_search_parts() {

		if ( !isset( $this->search_parts ) ) {
			$this->search_parts = apply_filters( 'facetious_search_parts', array(
				'paged'               => 'page',
				'facetious_post_type' => 'type',
				'category_name'       => 'category',
				'm'                   => 'month',
				's'                   => 'keyword',
			) );
		}

		return $this->search_parts;

	}

	/**
	 * Fetch the base URL part for Facetious searches. Defaults to WP_Rewrite's default search base, which is 'search'.
	 *
	 * @return string The base URL part for Facetious searches
	 * @author John Blackbourn
	 **/
	function get_search_base() {

		global $wp_rewrite;

		if ( !isset( $this->search_base ) )
			$this->search_base = apply_filters( 'facetious_search_base', $wp_rewrite->search_base );

		return $this->search_base;

	}

	/**
	 * Add 'facetious' to the list of available query variables.
	 *
	 * @filter query_vars
	 * 
	 * @param $vars array Array goes in!
	 * @return array Array comes out!
	 * @author John Blackbourn
	 **/
	function filter_query_vars( array $vars ) {
		$vars[] = 'facetious';
		$vars[] = 'facetious_post_type';
		return $vars;
	}

	/**
	 * Populates the request query variables with those from our 'facetious' query variable.
	 *
	 * @filter request
	 *
	 * @param $query array Query variables for the current request
	 * @return array Updated array of query variables from Facetious
	 * @author John Blackbourn
	 **/
	function filter_request( array $query ) {

		if ( !isset( $query['facetious'] ) )
			return $query;

		foreach ( $this->parse_search( $query['facetious'] ) as $key => $val )
			$query[$key] = $val;

		# Some plugins (eg. WPML) use $_GET['s'] directly, so we'll manually set it here to play nicely.
		if ( isset( $query['s'] ) )
			$_GET['s'] = addslashes( $query['s'] );

		return $query;

	}

	/**
	 * Parses a Facetious query string such as '/keyword/hello+world/foo/bar/' and returns its
	 * corresponding query variables
	 *
	 * @param $query string A Facetious query string
	 * @return array An associative array of query variables
	 * @author John Blackbourn
	 **/
	function parse_search( $query ) {
		
		$return = array();
		$parts  = explode( '/', $query );

		# Strip accidental empty parts (such as '/foo//bar/'):
		$parts  = array_filter( $parts );
		$parts  = array_values( $parts );

		# If we've got a trailing '/feed/' we need to push the default feed name onto the end
		if ( 'feed' == end( $parts ) )
			array_push( $parts, get_default_feed() );

		# If we've got an odd number of parts then the first part is the keyword search so we prepend the 's' part.
		if ( count( $parts ) % 2 )
			array_unshift( $parts, 's' );

		# Loop over every even-indexed part and populate our query variable array
		for ( $i = 0; $i < count( $parts ); $i = ( $i + 2 ) ) {

			$key = $this->get_search_var( $parts[$i] );
			$val = $this->decode( $parts[$i+1] );

			$return[$key] = $val;

		}

		return $return;

	}

	# http://www.jampmark.com/web-scripting/5-solutions-to-url-encoded-slashes-problem-in-apache.html
	function encode( $string ) {

		$string = urlencode( $string );
		$string = str_replace( array( '%2F', '%5C' ), array( '%252F', '%255C' ), $string );
		return $string;

	}

	function decode( $string ) {

		$string = str_replace( array( '%252F', '%255C' ), array( '%2F', '%5C' ), $string );
		$string = urldecode( $string );
		return $string;

	}

	/**
	 * Called on each admin screen load, this handles the upgrade routine when necessary.
	 *
	 * @action admin_init
	 *
	 * @return null
	 * @author John Blackbourn
	 **/
	function action_admin_init() {

		$op = 'facetious_dbv';

		# 1.0:
		if ( get_option( $op ) < 1 ) {

			flush_rewrite_rules();

			update_option( $op, 1 );

		}

	}

	/**
	 * Returns any current Facetious query as a WP_Query::query_vars 
	 * like array of parameters.
	 *
	 * @return array A WP_Query::query like array of parameters
	 * @author Simon Wheatley
	 **/
	function get_current_query() {
		return $this->parse_search( $GLOBALS[ 'wp_query' ]->query );
	}

	/**
	 * Load localisation files.
	 *
	 * @action init
	 *
	 * @return null
	 * @author John Blackbourn
	 */
	function action_init() {
		load_plugin_textdomain( 'facetious', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

}

defined( 'ABSPATH' ) or die();

Facetious::init();
