# Fuel-tables

A FuelPHP package to handle tables (with themes)


## Usage


### Creating a table-instance
~~~php
<?php

/**
 * Simple
 */
$table = \Table\Table::forge('my-table');

/**
 * Adding attributes to the outer <table>
 */
$table = \Table\Table::forge(
    'my-table',
    array(
        'class' => 'table table-hover'
    )
);

/**
 * Adding attributes and columns on creating
 */
$table = \Table\Table::forge(
    'my-table',
    array(
        'class' => 'table table-hover'
    ),
    array(
        'Name',
        'Created At'
    )
);

?>
~~~

Instead of ```\Table\Table::forge()```, you may also use ```new \Table\Table()```

~~~php
<?php
    function forge($name = 'default', array $attributes = array(), array $columns = array()) {}
?>
~~~


### Accessing a previously defined table-instance

~~~php
<?php

$table = \Table\Table::instance('my-table');

?>
~~~

~~~php
<?php
    function instance($name = 'default') {}
?>
~~~


### Accessing the active table instance

~~~php
<?php

$active_table = \Table\Table::active();

?>
~~~


### Setting attributes later

~~~php
<?php

// Setting the attribute (overwriting previously defined attributes of the same key)
$table->set_attribute('class', 'table table-bordered');

// Appending to an attribute or setting it if it's not yet set
$table->set_attribute('class', 'table-bordered', true);

?>
~~~

~~~php
<?php
    function set_attribute($key, $value, $append = false) {}
?>
~~~


### Clearing attributes

~~~php
<?php

// Completely clear the attribute 'class' (if it exists)
$table->remove_attribute('class');

// Clear just the 'table-bordered' part from the attribute 'class'
$table->remove_attribute('class', 'table-bordered');

?>
~~~

~~~php
<?php
    function remove_attribute($attribute, $value = null) {}
?>
~~~


### Table headers

Two ways of setting the table head. Either by adding a thead-group and adding the columns manually

~~~php
<?php

// Create a thead group for the table
$head = $table->add_head();
// Or
$head = $table->get_head();

// Add a row to the thead so it's not empty
$row = $head->add_row();

// Adding cells to the thead row
$row = $row
    ->add_cell('Name')
    ->add_cell('Created At');

// Adding attributes to the th
$row = $row
    ->add_cell(
        'Name',
        array(
            'id' => 'name-th-head',
        )
    )
    ->add_cell('Created At');

// Adding cells with identifiers to the thead row
$row = $row
    ->add_cell(
        'Name',
        'name'
    )
    ->add_cell(
        'Created At',
        'created_at'
    );

// Adding identifiers and attributes
$row = $row
    ->add_cell(
        # Content to display inside th
        'Name',
        # Attributes
        array(
            'id' => 'name-th-head',
        ),
        # Identifier
        'name'
    )
    ->add_cell(
        'Created At',
        'created_at'
    );

?>
~~~

~~~php
<?php
    function \Table\Group\Head::add_cell($content, array $attributes = array(), $identifier = null);
?>
~~~

Or, table headers can also be set using ```set_columns()``` on the table ```$table``` itself:
~~~php
<?php

/**
 * Setting the column names manually
 */
$table->set_columns('Name', 'Created At');
// Or
$table->set_columns(array(
    'Name',
    'Created At'
));

/**
 * Setting column names with identifiers so the columns can be accessed per row
 * by the identifier later on.
 * 
 * As a matter of fact, the previous example above will result in the same ability
 * to access the columns by name except that it might be useful to have the columns
 * named by properties of the model(s) and the column headers to be set by the
 * localized name of the property
 */
$table->set_columns(array(
    'name'          => 'Name',
    'created_at'    => 'Created At',
));

/**
 * Adding attributes inside the <th> for each column head
 */
$table->set_columns(array(
    'name'          => array(
        'as'            => 'Name',
        'attributes'    => array(
            'id'    => 'name-th-head'
        ),
    ),
    'created_at'    => 'Created At',
));

?>
~~~


### Adding a row

~~~php
<?php

/**
 * This will add a row to the table thus tbody. This is also the default behavior
 * when calling \Table\Table::add_row();
 */
$body_row = $table->add_row();

// Add a row to thead/tfoo. Notice that there can be only one row inside either thead
// and tfoot so whenever you call add_row() on the head it will overwrite the
// previously added row (if there was any)
$head_row = $table->get_head()->add_row();
$foot_row = $table->get_foot()->add_row();

?>
~~~

### Adding a cell to a row

~~~php
<?php

$row = $table->add_row();

// By passing the content (and optionally attributes) to add_cell()
$row = $row->add_cell(
    'Content',
    array(
        'class' => 'odd-cell'
    )
);

// By forging or constructing a new cell and passing it to add_cell()
$cell = \Table\Body\Cell::forge(
    'Content',
    array(
        'class' => 'odd-cell'
    )
);

$row = $row->add_cell($cell);

?>
~~~

### Sanitizing content of a cell

This only works on cells of the tbody-group. Multiple sanitizers may be added by supplying an array as the third argument to add_cell(). But be careful: The sanitizers are applied in order of appearance in the array passed! This might result in unwanted output. By default, sanitizing is disabled for the cells

~~~php
<?php

$row = $table->add_row();

// By passing the content (and optionally attributes) to add_cell()
$row = $row->add_cell(
    'Content',
    array(
        'class' => 'odd-cell'
    ),
    'Security::htmlentities'
);

// By forging or constructing a new cell and passing it to add_cell()
$cell = \Table\Body\Cell::forge(
    'Content',
    array(
        'class' => 'odd-cell'
    ),
    'Security::htmlentities'
);

/**
 * Or
 */
$cell = \Table\Body\Cell::forge(
    'Content',
    array(
        'class' => 'odd-cell'
    )
);

// Add a sanitizer to the cell
$cell = $cell->sanitize('Security::htmlentities');

$row = $row->add_cell($cell);

?>
~~~

### Accessing cells that match identifier

~~~php
<?php

// Get all cells that match the identifier 'created_at' definied as we set the
// columns of the table. Note that the cells are returned by reference so you
// need to handle them by reference in future calls
$cells = $table->get_cells('created_at');

// Now you can loop over the cells and do whatever you want with them
foreach ( $cells as $row_no => &$cell )
{
    $cell = $cell
        ->set_attribute('class' => ( $row_no % 2 == 0 ? 'even' : 'odd' ));
        ->sanitize('Security::htmlentities');
}

?>
~~~

~~~php
<?php

/**
 * Table\Table
 */
function &get_cells($identifier)
{
    return $this->_body->get_cells($identifier);
}

/**
 * \Table\Group\Body
 */
function &get_cells($identifier)
{
    if ( ! $this->_rows )
    {
        return false;
    }
    
    $cells = array();
    
    foreach ( $this->_rows as $row )
    {
        if ( $cell = $row->get_cells($identifier) )
        {
            $cells[] = $cell;
        }
    }
    
    return $cells;
}

/**
 * \Table\Row\Body
 */
function &get_cells($identifier)
{
    if ( ! $this->_cells )
    {
        return false;
    }
    
    foreach ( $this->_cells as $cell )
    {
        if ( $cell->identified_by($identifier) )
        {
            return $cell;
        }
    }
    
    return false;
}

/**
 * \Table\Cell\Body
 */
function identified_by($identifier)
{
    return $this->_identifier = $identifier;
}

?>
~~~
