<?php

/**
 * A simple base plugin class by John Blackbourn. Very much still under development.
 **/

class Facetious_Plugin {

	/**
	 * Class constructor
	 *
	 * @author John Blackbourn
	 **/
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Returns the URL for for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string URL
	 * @author John Blackbourn
	 **/
	public function plugin_url( $file = '' ) {
		return $this->plugin( 'url', $file );
	}

	/**
	 * Returns the filesystem path for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string Filesystem path
	 * @author John Blackbourn
	 **/
	public function plugin_path( $file = '' ) {
		return $this->plugin( 'path', $file );
	}

	/**
	 * Returns a version number for the given plugin file.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string Version
	 * @author John Blackbourn
	 **/
	public function plugin_ver( $file ) {
		return filemtime( $this->plugin_path( $file ) );
	}

	/**
	 * Returns the current plugin's basename, eg. 'my_plugin/my_plugin.php'.
	 *
	 * @return string Basename
	 * @author John Blackbourn
	 **/
	public function plugin_base() {
		return $this->plugin( 'base' );
	}

	/**
	 * Populates and returns the current plugin info.
	 *
	 * @author John Blackbourn
	 **/
	protected function plugin( $item, $file = '' ) {
		if ( !isset( $this->plugin ) ) {
			$this->plugin = array(
				'url'  => plugin_dir_url( $this->file ),
				'path' => plugin_dir_path( $this->file ),
				'base' => plugin_basename( $this->file )
			);
		}
		return $this->plugin[$item] . ltrim( $file, '/' );
	}

	/**
	 * A version of _n() which accepts already-localised strings as parameters
	 *
	 * @param string $singular The text that will be used if $number is 1
	 * @param string $plural The text that will be used if $number is not 1
	 * @param int $number The number to compare against to use either $singular or $plural
	 * @return string Either $singular or $plural text
	 * @author John Blackbourn
	 */
	public function n( $singular, $plural, $number ) {
		return ( 1 == $number ) ? $singular : $plural;
	}

}

defined( 'ABSPATH' ) or die();
