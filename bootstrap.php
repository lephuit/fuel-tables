<?php

/**
 * Part of the Gasoline framework
 *
 * @package     Gasoline
 * @namespace   Table
 * @version     0.1-dev
 * @author      Gasoline Development Team
 * @author      Haro "WanWizard" Verton
 * @license     MIT License
 * @copyright   2013 Gasoline Development Team
 * @link        http://hubspace.github.io/gasoline
 */

Autoloader::add_classes(array(
    'Table\\Table'  => __DIR__ . '/classes/table.php',
    
    // Simple tables
    'Table\\Simple'  => __DIR__ . '/classes/table/simple.php',
    // Rows and groups
    'Table\\Simple\\Row'    => __DIR__ . '/classes/table/simple/row.php',
    'Table\\Simple\\Group'  => __DIR__ . '/classes/table/simple/group.php',
    // Header group and row
    'Table\\Simple\\Header\\Group'  => __DIR__ . '/classes/table/simple/header/group.php',
    'Table\\Simple\\Header\\Row'    => __DIR__ . '/classes/table/simple/header/row.php',
    // Footer group and row
    'Table\\Simple\\Footer\\Group'  => __DIR__ . '/classes/table/simple/footer/group.php',
    'Table\\Simple\\Footer\\Row'    => __DIR__ . '/classes/table/simple/footer/row.php',
    // Body group and row
    'Table\\Simple\\Body\\Group'    => __DIR__ . '/classes/table/simple/body/group.php',
    'Table\\Simple\\Body\\Row'      => __DIR__ . '/classes/table/simple/body/row.php',
    
    // Model tables
    'Table\\Model'  => __DIR__ . '/classes/table/model.php',
    // Rows and groups
    'Table\\Model\\Row'    => __DIR__ . '/classes/table/model/row.php',
    'Table\\Model\\Group'  => __DIR__ . '/classes/table/model/group.php',
    // Header group and row
    'Table\\Model\\Header\\Group'  => __DIR__ . '/classes/table/model/header/group.php',
    'Table\\Model\\Header\\Row'    => __DIR__ . '/classes/table/model/header/row.php',
    // Footer group and row
    'Table\\Model\\Footer\\Group'  => __DIR__ . '/classes/table/model/footer/group.php',
    'Table\\Model\\Footer\\Row'    => __DIR__ . '/classes/table/model/footer/row.php',
    // Body group and row
    'Table\\Model\\Body\\Group'    => __DIR__ . '/classes/table/model/body/group.php',
    'Table\\Model\\Body\\Row'      => __DIR__ . '/classes/table/model/body/row.php',
    
    // Exceptions
    'Table\\TableException' =>  __DIR__ . '/classes/exceptions.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/nestedset/bootstrap.php */

