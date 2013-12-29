# Fuel-tables

A FuelPHP package to handle tables


## Usage


### Creating a table-instance
~~~php
<?php

/**
 * Simple
 */
$table = \Table\Table::forge();

/**
 * Adding attributes to the outer <table>
 */
$table = \Table\Table::forge(array(
    'class' => 'table table-hover'
));

?>
~~~

Instead of ```\Table\Table::forge()```, you may also use ```new \Table\Table()```

~~~php
<?php
    function forge(array $attributes = array()) {}
?>
~~~


### Accessing a previously defined table-instance or forging a new named table instance

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


### Setting attributes later

~~~php
<?php

// Setting the attribute (overwriting previously defined attributes of the same key)
$table->set_attribute('class', 'table table-bordered');

// Appending to an attribute or setting it if it's not yet set
$table->add_attribute('class', 'table-bordered');

?>
~~~

~~~php
<?php
    function set_attribute($key, $value = null) {}
    
    public function add_attribute($attribute, $value = null, $prepend = false) {}
?>
~~~


### Clearing attributes

~~~php
<?php

// Completely clear the attribute 'class' (if it exists)
$table->delete_attribute('class');

// Clear just the 'table-bordered' part from the attribute 'class'
$table->delete_attribute('class', 'table-bordered');

?>
~~~

~~~php
<?php
    function delete_attribute($attribute, $value = null, $allow_empty = false) {}
?>
~~~


### Table headers

Two ways of setting the table head. Either by adding a thead-group and adding the columns manually

~~~php
<?php

// Create a thead group for the table
$head = \Table\Group::forge(array(), array(), \Table\Group::HEADER);

// Adding cells to the header
$head = $head
    ->add_cell('Name')
    ->add_cell('Created At');

// Adding attributes to the th
$head = $head
    ->add_cell(
        'Name',
        array(
            'id' => 'name-th-head',
        )
    )
    ->add_cell('Created At');

?>
~~~

~~~php
<?php
    function \Table\Group::add_cell($cell = '', array $attributes = array(), $sanitizer = null) {}
?>
~~~

Or, table headers can also be set using ```set_columns()``` on the table ```$table``` itself:
~~~php
<?php

/**
 * Setting the column names manually
 */
$table->add_header(array(
    'Name',
    'Created At'
));
// Or (with $head from above)
$table->set_header($head);

?>
~~~


### Adding a row

~~~php
<?php

$table = \Table\Table::forge();

// Either like this:
$row = \Table\Row::forge();
$table[] = $row;

// Or like so
$table[] = new \Table\Row();

?>
~~~

### Adding a cell to a row

~~~php
<?php

$row = \Table\Row::forge();

// Either like this:
$cell = \Table\Cell::forge($content = '', array $attributes = array(), $sanitizer = null);
$row[] = $cell;

// Or like so
$row[] = new \Table\Cell($content = '', array $attributes = array(), $sanitizer = null);

?>
~~~

### Sanitizing content of a cell

This only works on cells of the tbody-group. A sanitizer may be added by supplying it as the third argument to the constructor. By default, sanitizing is disabled for the cells

~~~php
<?php

// Sanitizer can be supplied as a string
$cell = \Table\Cell::forge('<a href="foo://bar">This will be sanitized</a>', array(), 'Security::htmlentities');

// Or any valid callback (closures, function-names, methods, ...)
$cell = \Table\Cell::forge(
    '<a href="foo://bar">This will be sanitized</a>',
    array(),
    function($content) {
        return \Security::htmlentities($content);
    }
);

?>
~~~

### Accessing (Looping over) rows of the table

~~~php
<?php

foreach ( $table->get_data() as $row )
{
    foreach ( $row->get_data() as $cell )
    {
        // Do something with the current cell
    }
}

?>
~~~
