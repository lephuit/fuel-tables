<?php

/**
 * Part of the fuel-tables package
 *
 * @package     fuel-tables
 * @namespace   Table
 * @version     1.0-dev
 * @author      Gasoline Development Team
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2013 -- 2014 Gasoline Development Team
 * @copyright   2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

Autoloader::add_classes(array(
    'Table\\AttributeContainer' => __DIR__ . '/classes/attributecontainer.php',
    'Table\\Cell'               => __DIR__ . '/classes/cell.php',
    'Table\\DataContainer'      => __DIR__ . '/classes/datacontainer.php',
    'Table\\Group'              => __DIR__ . '/classes/group.php',
    'Table\\Helper'             => __DIR__ . '/classes/helper.php',
    'Table\\Row'                => __DIR__ . '/classes/row.php',
    'Table\\Table'              => __DIR__ . '/classes/table.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/table/bootstrap.php */
