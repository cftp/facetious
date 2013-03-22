=== Facetious ===
Contributors: codeforthepeople, johnbillion, s1m0nd, simonwheatley
Tags: search, faceted, faceted search, advanced search
Requires at least: 3.4
Tested up to: 3.5
Stable tag: trunk
License: GPL v2 or later

Facetious lets you add a faceted - often called an 'advanced' - search form to your WordPress website.

== Description ==

Facetious lets you add a faceted - often called an 'advanced' - search form to your WordPress website. It comes with a ready-to-use sidebar widget, plus a number of implementation options for developers.

A Facetious form can include dropdown lists of:

 * Taxonomy terms (tags, categories, and custom taxonomies)
 * Months
 * Plus a conventional search input box

You can also restrict searches by post type _(singular)_, if you so desire.

Additionally, Facetious introduces an extra 'pretty permalink' structure around search queries, which may help your site performance if you're using a caching plugin like WP Super Cache. Also, they look a heck of a lot friendlier than `?s=polly&post_type=pet&category=bird`. (You don't even have to display a faceted search form on your site to benefit from these, by the way).

Please note - Facetious isn't a replacement search engine; it helps you construct the search query, but then it's over to WordPress to run the actual search.

= About =

Facetious is a product of [Code For The People Ltd](http://codeforthepeople.com), and is based on work done during 2012 on behalf of several UK public sector clients, among them:

 * The [Commission on Devolution in Wales](http://commissionondevolutioninwales.independent.gov.uk)
 * The [National Audit Office](http://www.nao.org.uk); and
 * The Department for Culture, Media and Sport through its [Government Olympic Communication](http://goc2012.culture.gov.uk) site

... which is why we're keen to offer it for wider public use.

[Fork me on GitHub!](http://github.com/cftp/facetious)

= Usage =

The simplest way to add a Facetious search form is to add the Facetious widget to your sidebar. You'll see a complete list of options so you can choose which fields to include in your search form.

= Advanced Usage =

Developers can use a template function to output a Facetious search form and have complete control over the form and its fields, such as text labels, class names, etc.

The main template function for outputting a Facetious search form is `facetious( $args )`. If you want to ensure the site doesn't break if the Facetious plugin is deactivated, you can use `do_action( 'facetious', $args );` instead.

'$args' is an array of arguments thus:

 * `submit` - string  - The text for the submit button.
 * `echo`   - boolean - Whether to echo the form out or not.
 * `class`  - string  - The class name for the form.
 * `id`     - string  - The ID attribute for the form.
 * `fields` - array   - A list of fields to show in the form. See below.

Each item in the 'fields' array can be either:

 * A string name of a taxonomy
 * An array of details for the field (see below)
 * One of `s`, `m` or `pt` for the keyword search input, month dropdown and post type dropdown respectively

For each field specified as an array you can specify:
 
 * `label`   - string - The descriptive text for this field. Defaults to the name of the taxonomy, or 'All types' for post types..
 * `class`   - string - The class name for the field.
 * `id`      - string - The ID attribute for the field.
 * `all`     - string - The "All items" text for this field. Defaults to the 'all_items' label of the taxonomy.
 * `options` - array  - For a taxonomy provide an array with the term slug as the key and the term name as the value, e.g. `array( 'term-1' => 'Term 1', 'term-2' => 'Term 2' );`, for post type supply an array of post type names.

Example 1:

`do_action( 'facetious', array(
	'submit' => 'Search',
	'fields' => array(
		's',
		'category',
		'custom_tax_1',
		'custom_tax_2'
	)
) );`

Example 2:

`do_action( 'facetious', array(
	'submit' => 'Search',
	'fields' => array(
		's',
		'custom_tax_1' => array(
			'label' => 'Select an option',
			'class' => 'my_tax_class',
			'id'    => 'my_tax_id',
			'all'   => 'All terms'
		)
		'custom_tax_2',
		'm'
	)
) );`

== Installation ==

You can install this plugin directly from your WordPress dashboard:

 1. Go to the *Plugins* menu and click *Add New*.
 2. Search for *Facetious*.
 3. Click *Install Now* next to the *Facetious* plugin.
 4. Activate the plugin.

Alternatively, see the guide to [Manually Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Usage =

The simplest way to add a Facetious search form is to add the Facetious widget to your sidebar. You'll see a complete list of options so you can choose which fields to include in your search form.

Please see the full plugin description for advanced usage.

== Frequently Asked Questions ==

= Does this plugin play nicely with WPML? =

Yep.

= Where can I help out with development? =

[Fork me on GitHub!](http://github.com/cftp/facetious) We welcome pull requests.

== Screenshots ==

1. A faceted search form in action
2. A customised faceted search form
3. The widget options panel

== Upgrade Notice ==

= 1.1.3 =
* Avoid some encoding and slashing issues. Add some basic default styling to fields in the Facetious widget.

== Changelog ==

= 1.1.3

* Avoid some encoding and slashing issues.
* Add some basic default stlying to fields in the Facetious widget.

= 1.1.2 =

* Introduce an accepted value in the fields to display a post type dropdown
* Add documentation for use of `options` value in a field for either post type or taxonomy

= 1.1.1 =

* Avoid double slashes in URLs when using WPML

= 1.1 =

* Introduce an 'options' argument for taxonomies in the template function

= 1.0.2 =

* Avoid a WPML redirection bug.

= 1.0.1 =

* Initial release.
