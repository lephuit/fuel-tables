<?php

/**
 * Part of the fuel-Table-package
 *
 * @package     Table
 * @namespace   Table
 * @version     0.1-dev
 * @author      Gasoline Development Team
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2013 Gasoline Development Team
 * @copyright   2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

Autoloader::add_classes(array(
    // Base
    'Table\\Table' => __DIR__ . '/classes/table.php',
    
    // Table Groups
    'Table\\Group'          => __DIR__ . '/classes/group.php',
    'Table\\Group_Body'     => __DIR__ . '/classes/group/body.php',
    'Table\\Group_Header'   => __DIR__ . '/classes/group/header.php',
    'Table\\Group_Footer'   => __DIR__ . '/classes/group/footer.php',
    
    // Table Row
    'Table\\Row' => __DIR__ . '/classes/row.php',
    
    // Table Cells
    'Table\\Cell'           => __DIR__ . '/classes/cell.php',
    'Table\\Cell_Body'      => __DIR__ . '/classes/cell/body.php',
    'Table\\Cell_Header'    => __DIR__ . '/classes/cell/header.php',
    'Table\\Cell_Footer'    => __DIR__ . '/classes/cell/footer.php',
    
    // Helpers
    'Table\\Helpers' => __DIR__ . '/classes/helpers.php',
    
    // Exceptions
    'Table\\ReadOnlyException'          => __DIR__ . '/classes/exceptions.php',
    'Table\\OutOfBoundsException'       => __DIR__ . '/classes/exceptions.php',
    'Table\\InvalidArgumentException'   => __DIR__ . '/classes/exceptions.php',
    'Table\\BadMethodCallException'     => __DIR__ . '/classes/exceptions.php',
    'Table\\HydrationException'         => __DIR__ . '/classes/exceptions.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/table/bootstrap.php */

