# What is Facetious?

Facetious lets you add a *facet*ed - often called an 'advanced' - search form to your WordPress website. It comes with a ready-to-use sidebar widget, plus a number of implementation options for developers.

A Facetious form can include dropdown lists of:

- taxonomy terms (tags, categories, and so on)
- months
- plus a conventional text input box.

You can also restrict it by post type _(singular)_, if you so desire.

Additionally, Facetious introduces some extra 'pretty permalink' structures around search queries, which may help if you're using a caching plugin like WP Super Cache. Also, they look a heck of a lot friendlier than `?s=polly&amp;post_type=pet&category=bird`. (You don't even have to have a faceted search form on your site to benefit from these, by the way.)

Please note - Facetious isn't a replacement search engine; it helps you construct the search query, but then it's over to WordPress to run the actual search.

Facetious is a product of [Code For The People Ltd](http://codeforthepeople.com), and is based on work done during 2012 on behalf of several UK public sector clients, among them:

* the [Commission on Devolution in Wales](http://commissionondevolutioninwales.independent.gov.uk)
* the [National Audit Office](http://www.nao.org.uk); and
* the Department for Culture, Media and Sport through its [Government Olympic Communication](http://goc2012.culture.gov.uk) site

... which is why we're keen to offer it for wider public use.

# Usage

The main template function for outputting a Facetious search form is `facetious( $args )`. If you want to ensure the site doesn't break if the Facetious plugin is deactivated, you can use `do_action( 'facetious', $args );` instead.

`$args` is an array of arguments thus:

'submit' - string  - The text for the submit button.
'echo'   - boolean - Whether to echo the form out or not.
'class'  - string  - The class name for the form.
'id'     - string  - The ID attribute for the form.
'fields' - array   - A list of fields to show in the form. See below.

Each item in the 'fields' array can be either:
* A string name of a taxonomy,
* An array of details for the field
* One of 's' or 'm' for the keyword search input and month dropdown respectively.

For each field specified as an array you can specify:

'label' - string - The descriptive text for this field. Defaults to the name of the taxonomy.
'class' - string - The class name for the field.
'id'    - string - The ID attribute for the field.
'all'   - string - The "All items" text for this field. Defaults to the 'all_items' label of the taxonomy.

Example 1:

do_action( 'facetious', array(
	'submit' => 'Search',
	'fields' => array(
		's',
		'category',
		'custom_tax_1',
		'custom_tax_2'
	)
) );

Example 2:

do_action( 'facetious', array(
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
) );
