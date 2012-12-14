
The main template function for outputting a Facetious search form is `facetious( $args )`. If you want to ensure the site doesn't break if the Facetious plugin is deactivated, you can use `do_action( 'facetious', $args );` instead.

`$args` is an array of arguments thus:

'submit' - string  - The text for the submit button.
'echo'   - boolean - Whether to echo the form out or not.
'class'  - string  - The class name for the form.
'id'     - string  - The ID attribute for the form.
'fields' - array   - A list of fields to show in the form. See below.

Each item in the 'fields' array can be either:
 - A string name of a taxonomy,
 - An array of details for the field
 - One of 's' or 'm' for the keyword search input and month dropdown respectively.

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
