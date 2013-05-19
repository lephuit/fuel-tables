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
    
    'Table\\Header' => __DIR__ . '/classes/table/header.php',
    'Table\\Footer' => __DIR__ . '/classes/table/footer.php',
    'Table\\Body'   => __DIR__ . '/classes/table/body.php',
    
    'Table\\Row'    => __DIR__ . '/classes/table/row.php',
    
    'Table\\TableException' =>  __DIR__ . '/classes/exceptions.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/nestedset/bootstrap.php */

