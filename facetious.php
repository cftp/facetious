<?php
/*
Plugin Name:  Facetious Search
Plugin URI:   https://github.com/cftp/facetious
Description:  A faceted search interface for WordPress
Version:      1.0.2
Author:       <a href="http://johnblackbourn.com/">John Blackbourn</a> and <a href="http://codeforthepeople.com/">Code for the People</a>
Text Domain:  facetious
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2012 John Blackbourn / Code for the People ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

require_once 'class.plugin.php';
require_once 'class.widget.php';
require_once 'template.php';

class Facetious extends Facetious_Plugin {

	/**
	 * Class constructor. Set up some filters and actions.
	 *
	 * @return null
	 * @author John Blackbourn
	 */
	function __construct() {

		# Actions:
		add_action( 'init',                 array( $this, 'init' ) );
		add_action( 'admin_init',           array( $this, 'maybe_upgrade' ) );
		add_action( 'template_redirect',    array( $this, 'template_redirect' ) );
		add_action( 'facetious',            'facetious' );

		# Filters:
		add_filter( 'query_vars',           array( $this, 'query_vars' ) );
		add_filter( 'search_rewrite_rules', array( $this, 'search_rewrite_rules' ) );
		add_filter( 'request',              array( $this, 'process_request' ) );

		# Set up the plugin from the parent class:
		parent::__construct( __FILE__ );

	}

	/**
	 * Redirects a client to a pretty URL after performing a Facetious search
	 *
	 * @return null
	 * @author John Blackbourn
	 **/
	function template_redirect() {

		global $wp_rewrite, $wp_query;

		$parts = array();
		$base  = $this->get_search_base();

		if ( !$wp_rewrite->using_permalinks() )
			return;

		# Note the explicit use of isset() on the 's' query var instead of using get_query_var('s'). The
		# condition we're looking for is the presence of the 's' query string parameter even when it's empty.
		if ( !isset( $wp_query->query['s'] ) )
			return;

		# Bail if we're already viewing a pretty URL
		if ( false !== strpos( $_SERVER['REQUEST_URI'], "/{$base}/" ) )
			return;

		# Build the array containing alternating keys and values
		foreach ( $wp_query->query as $key => $val ) {
			if ( '' !== $val ) {
				$parts[] = $this->get_search_part( $key );
				$parts[] = urlencode( stripslashes( $val ) );
			}
		}

		# Special case: If we've only got a keyword search parameter then we can strip the
		# leading 'keyword/' part for brevity
		if ( 2 == count( $parts ) and in_array( $this->get_search_part( 's' ), $parts ) )
			array_shift( $parts );

		$parts = implode( '/', $parts );

		wp_redirect( home_url() . "/{$base}/{$parts}/" );
		exit();

	}

	/**
	 * Overwrites WordPress' default search rewrite rules with our own. Passes the entire search
	 * string to the facetious query var.
	 *
	 * @param $rules array List of rewrite rules for searches
	 * @return array New list of rewrite rules for searches
	 * @author John Blackbourn
	 **/
	function search_rewrite_rules( $rules ) {

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
				'paged'         => 'page',
				'post_type'     => 'type',
				'category_name' => 'category',
				'm'             => 'month',
				's'             => 'keyword',
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
	 * @param $vars array Array goes in!
	 * @return array Array comes out!
	 * @author John Blackbourn
	 **/
	function query_vars( $vars ) {
		$vars[] = 'facetious';
		return $vars;
	}

	/**
	 * Populates the request query variables with those from our 'facetious' query variable.
	 *
	 * @param $query array Query variables for the current request
	 * @return array Updated array of query variables from Facetious
	 * @author John Blackbourn
	 **/
	function process_request( $query ) {

		if ( !isset( $query['facetious'] ) )
			return $query;

		foreach ( $this->parse_search( $query['facetious'] ) as $key => $val )
			$query[$key] = $val;

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
			$val = urldecode( $parts[$i+1] );

			$return[$key] = $val;

		}

		return $return;

	}

	/**
	 * Called on each admin screen load, this handles the upgrade routine when necessary.
	 *
	 * @return null
	 * @author John Blackbourn
	 **/
	function maybe_upgrade() {

		$op = 'facetious_dbv';

		# 1.0:
		if ( get_option( $op ) < 1 ) {

			flush_rewrite_rules();

			update_option( $op, 1 );

		}

	}

	/**
	 * Load localisation files.
	 *
	 * @return null
	 * @author John Blackbourn
	 */
	function init() {
		load_plugin_textdomain( 'facetious', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

}

defined( 'ABSPATH' ) or die();

global $facetious;

$facetious = new Facetious;
