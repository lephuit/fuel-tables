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
    'Table\\Table' => __DIR__ . '/classes/table.php',
    
    'Table\\Helpers' => __DIR__ . '/classes/helpers.php',
    
    'Table\\ReadOnlyException'      => __DIR__ . '/classes/exceptions.php',
    'Table\\OutOfBoundsException'   => __DIR__ . '/classes/exceptions.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/table/bootstrap.php */

