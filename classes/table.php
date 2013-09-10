<?php namespace Table;

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

use ArrayAccess;
use Countable;
use Iterator;

class Table implements ArrayAccess, Countable, Iterator {
    
    /**
     * Storage for all table-instances
     * 
     * @access  protected
     * @static
     * @var     array
     */
    protected static $_instances = array();
    
    /**
     * Keeps the active table instance
     * 
     * @access  protected
     * @static
     * @var     \Table\Table
     */
    protected static $_instance = null;
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Forge a new table-instance with the given name and attributes
     * 
     * 
     * @access  public
     * @static
     * 
     * @param   string  $name           Unique name to identiy the table
     * @param   array   $attributes     Array of attributes to use for the table
     * 
     * @return  \Table\Table
     */
    public static function forge($name = 'default', array $attributes = array(), array $headers = array())
    {
        // New instance?
        if ( ! isset(static::$_instances[$name]) )
        {
            // Then forge it and make it the active instance
            static::$_instances[$name] = new static($attributes, $headers);
        }
        
        // And return it
        return static::$_instance = static::$_instances[$name];
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Return a table-instance or forge a new one if it doesn't exist
     * 
     * 
     * @access  public
     * @static
     * 
     * @param   string  $name   Name to identify the table
     * 
     * @return  \Table\Table
     */
    public static function instance($name = '_default_')
    {
        // Return an instance that was forged or found within the previously forged
        //  instances
        return static::forge($name);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Return the active table instance
     * 
     * @access  public
     * @static
     * 
     * @return  \Table\Table
     */
    public static function active()
    {
        return static::$_instance ? : static::instance();
    }
    
    
    
    
    
    /**
     * The table's attributes e.g., class, id, ...
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * The table's body-object
     * 
     * @access  protected
     * @var     \Table\Group_Body
     */
    protected $_body = null;
    
    /**
     * Current row integer used for Iterator
     * 
     * @access  protected
     * @var     int
     */
    protected $_curr_row = 0;
    
    /**
     * The table's foot-object
     * 
     * 
     * @access  protected
     * @var     \Table\Group_foot
     */
    protected $_footer = null;
    
    /**
     * The table's header-object
     * 
     * @access  protected
     * @var     \Table\Group_Header
     */
    protected $_header = null;
    
    /**
     * Stores the namespace model-name for getting the data
     * 
     * @access  protected
     * @var     string
     */
    // protected $_model = null;
    
    /**
     * Keeps the query used to get data from the DB
     * 
     * @access  protected
     * @var     \Orm\Query
     */
    // protected $_query = null;
    
    /**
     * Keeps the query options like 'limit', 'offset', 'order_by'
     * 
     * @access  protected
     * @var     array
     */
    // protected $_query_opts = array();
    
    /**
     * The columns that are set via set_columns and needed to hydrate the table
     * 
     * @access  protected
     * @var     array
     */
    // protected $_columns = array();
    
    /**
     * Configuration for the table-instance
     * 
     * @access  protected
     * @var     array
     */
    protected $_config = array();
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Construct a new table-object and assign its default attributes
     * 
     * @access  public
     * 
     * @param   array   $attributes     The attributes to set for the table
     */
    public function __construct(array $attributes = array(), array $headers = array())
    {
        // Set the attributes, if given
        $attributes && $this->_attributes = $attributes;
        
        // Set the headers, if given
        $headers && $this->headers($headers);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get some property of the table
     * 
     * @param   string  $property   The name of the property to get.
     * @param   mixed   $default    The default value to return if the attribute
     *                              cannot be found.  Defaults to null
     * @return  mixed               Returns the property that matched $default it
     *                              property was not found
     */
    public function get($property, $default = null)
    {
        // No magic property, so we will return the matching attribute (if it exists)
        //  otherwise $default
        return \Arr::get($this->_attributes, $property, $default);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an attribute property of the table e.g., 'class'
     * 
     * @access  public
     * 
     * @param   string  $property   The name of the property to set
     * @param   mixed   $value      The value to set for $property
     * @param   boolean $append     Whether to append $property to the existing
     *                              attributes or to overwrite it.
     *                              Defaults to false i.e., overwriting
     * 
     * @return  \Table\Table        Returns the object for chaining
     */
    public function set($property, $value = null, $append = false)
    {
        // Allow setting all attributes at once
        if ( $property === 'attributes' )
        {
            if ( ! is_array($value) )
            {
                throw new InvalidArgumentException('To set attributes on the table an array must be provided but ' . gettype($value) . ' given');
            }
            
            $this->_attributes = $value;
            
            return $this;
        }
        
        // Append it? Then use our helper to add the attribute, otherwise just overwrite it
        $append === true && Helpers::add_attribute($this->_attributes, $property, $value) OR $this->_attributes[$property] = $value;
        
        // Return for chaining
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Remove a specific attribute value or all values for the given attribute
     * 
     * For example, if the table's class were to be 'table table-bordered table-zebra',
     *  $table->remove('class', 'table-zebra') would only remove 'table-zebra' form
     *  the class. $table->remove('class') would remove the 'class'-property from
     *  the attributes array
     * 
     * @param   string      $property   The property to manipulate
     * @param   string|null $value      Desired value to remove form the property
     *                                  or null to purge the whole property
     * 
     * @return  \Table\Table            Returns the table-object for chaining
     */
    public function remove($property, $value = null)
    {
        Helpers::remove_attribute($this->_attributes, $property, $value);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set the columns used for the table (basically the headers)
     * 
     * @access  public
     * @see     \Table\Group::set_columns()
     * 
     * @param   array   $columns    Array of column names or an advanced array
     * 
     * @return  \Table\Group_Header
     */
    public function headers(array $columns = array())
    {
        // We need to have a head-group
        $this->add_header();
        
        // The default options we accept for a column
        $defaults = array(
            'attributes'    => array(),
            'use'           => null,
            'as'            => null,
            'sanitize'      => false,
        );
        
        // Loop over the given columns to add them
        foreach ( $columns as $identifier => $heading )
        {
            // Got an array for the options?
            if ( is_array($heading) )
            {
                // Does it contain any of the keys from $defaults? Then 
                // array_diff_assoc($defaults, $options) && $options = array('attributes' => $options);
                // Merge the given options with the defaults
                $heading = \Arr::merge($defaults, $heading);
                
                // What key to use to put inside the cells?
                $heading['use'] OR $heading['use'] = $column;
                
                // What to display in the table header?
                $heading['as'] && $column = $heading['as'] OR $column = $heading['use'];
            }
            // $options is no array so we assume $column to be the identifier and $options
            //  to be the value to display
            else
            {
                $identifier 
                $column = $options;
                $options = $defaults;
            }
            
            // And add a new cell to the header by calling Cell_Header::forge() so
            //  we can chain to sanitize() as well
            $this->_header->add_cell(
                Cell::forge(
                    Cell::HEADER,
                    $column,
                    $options['attributes']
                )#->sanitize($options['sanitize'])
            );
            
            unset($options['attributes']);
            
            $this->_columns[] = $options;
        }
        
        // Return the table for chaining
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set the namespace model's name to work with and get data from
     * 
     * @access  public
     * 
     * @param   string  $model      Namespaced name of model
     * @param   boolean $hydrate    Automatically hydrate the data right after
     *                              assigning the model.  Defaults to false
     * 
     * @return  \Table\Table
     */
    public function set_model($model, $hydrate = false)
    {
        $this->_model = $model;
        
        if ( $conditions = $model::condition('order_by') )
        {
            $this->set_config('sort.default', $conditions);
        }
        
        $hydrate && $this->hydrate();
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a header to the table and return the header-object
     * 
     * @access  public
     * @see     \Table\Group_Header::set_columns()
     * 
     * @param   array   $columns    The columns to use for the header
     * @param   array   $attributes Attributes to pass to the header
     * 
     * @return  \Table\Group_Header
     */
    public function add_header(array $columns = array(), array $attributes = array())
    {
        $this->_header OR $this->_header = Group::forge(Group::HEADER, $columns, $attributes);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get the table's head object
     * 
     * @access  public
     * 
     * @return  \Table\Group\Header
     */
    public function & get_header()
    {
        $this->add_header;
        
        return $this->_header;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a footer to the table and return the footer-object
     * 
     * @access  public
     * 
     * @param   array   $attributes Attributes to pass to the footer
     * 
     * @return  \Table\Group_Footer
     */
    public function add_footer(array $attributes = array())
    {
        $this->_footer OR $this->_footer = Group::forge(Group::FOOTER, array(), $attributes);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get the table's foot object
     * 
     * @access  public
     * 
     * @return  \Table\Group_Footer
     */
    public function & get_footer()
    {
        $this->add_footer();
        
        return $this->_footer;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a body to the table and return the body-object
     * 
     * @access  public
     * 
     * @param   array   $attributes Attributes to pass to the body
     * 
     * @return  \Table\Group_Body
     */
    public function add_body(array $attributes = array())
    {
        $this->_body OR $this->_body = Group::forge(Group::BODY, array(), $attributes);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get the table's body object
     * 
     * @access  public
     * 
     * @return  \Table\Group\Body
     */
    public function & get_body()
    {
        $this->add_body();
        
        return $this->_body;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a row to the body of the table
     * 
     * @access  public
     * 
     * @return  \Table\Group_Body
     */
    public function add_row(array $columns = array(), array $attributes = array())
    {
        $this->get_body()->add_row($columns, $attributes);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Render the table and all its parts
     * 
     * @access  public
     * 
     * @return  string          Returns the generated HTML-table or the error message
     *                          if rendering failed
     */
    public function render()
    {
        try
        {
            $this->hydrate();
            
            $header = ( $this->_header ? $this->_header->render() . PHP_EOL : '' );
            
            $footer = ( $this->_footer ? $this->_foot->render() . PHP_EOL : '' );
            
            $body = ( $this->_body ? $this->_body->render() . PHP_EOL : '' );
            
            return html_tag('table', $this->_attributes, $header . $footer . $body);
        }
        catch ( \Exception $e )
        {
            if ( \Fuel::$env == \Fuel::DEVELOPMENT )
            {
                return $e->getMessage();
            }
            
            throw new \HttpServerErrorException();
        }
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Hydrate the table with data from the database or the data added manually
     * 
     * @access  public
     * 
     * @return  \Table\Table
     */
    public function hydrate(array $data = array())
    {
        // We don't want duplicate data inside the body, so assign a new body
        //  but keep the old attributes (if there's an old body)
        $body_attributes = ( $this->_body ? $this->_body->get('attributes') : array() );
        $body = $this->add_body($body_attributes);
        
        foreach ( $data as $_data )
        {
            // Create a new row for the data
            $row = $body->add_row();
            
            // And loop over the columns we need to set
            foreach ( $this->_columns as $column )
            {
                // Got a column and found a value to put inside the cell?
                if ( $column['use'] && null !== ( $val = \Arr::get($_data, $column['use'], null) ) )
                {
                    // Then forge a new cell of type 'body' and also apply the sanitation
                    $row->add_cell(
                        Cell::forge(Cell::BODY, $val)
                        ->sanitize($column['sanitize'])
                    );
                }
                // Otherwise, skip it
                else
                {
                    $row->skip_cell();
                }
            }
        }
        
        // For chaining
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Order the rows of the table by the given property/properties and dirn
     * 
     * Internally used on __construct if auto-init is configured true
     * 
     * @access  public
     * 
     * @param   string|array    $property   The property to sort by or an array
     *                                      of property => dirn
     * @param   string          $dirn       The direction to order by. Either 'ASC'
     *                                      or 'DESC'.  Defaults to 'ASC"'
     * 
     * @return  \Table\Table
     */
    // public function order_by($property, $dirn = 'ASC"')
    // {
    //     if ( $property === true )
    //     {
    //         if ( $order_by = $this->_query_opts_by_config('order_by') )
    //         {
    //             $this->_query_opts['order_by'] = $order_by;
    //         }
            
    //         return $this;
    //     }
        
    //     // Taken from \Orm\Query
    //     if ( is_array($property) )
    //     {
    //         foreach ( $property as $p => $d )
    //         {
    //             if ( is_int($p) )
    //             {
    //                 is_array($d) ? $this->order_by($d[0], $d[1]) : $this->order_by($d, $direction);
    //             }
    //             else
    //             {
    //                 $this->order_by($p, $d);
    //             }
    //         }
            
    //         return $this;
    //     }
        
    //     // Ensure we have the key inside our query-options
    //     array_key_exists('order_by', $this->_query_opts) OR $this->_query_opts['order_by'] = array();
        
    //     // Store in our local query-opts
    //     $this->_query_opts['order_by'][] = array($property, $dirn);
        
    //     // For chaining
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set or activate the pagination options
     * 
     * @access  public
     * 
     * @param   bool|int    $limit      The limit to use or true to activate automatic
     *                                  pagination rendering
     * @param   int         $offset     The offset to use. If pagewise-pagination
     *                                  is configure, then $offset is the page,
     *                                  otherwise it will be a real offset
     * 
     * @return  \Table\Table
     */
    // public function paginate($limit = null, $offset = null)
    // {
    //     if ( $limit === true OR ( is_null($limit) && is_null($offset) ) )
    //     {
    //         extract($this->_query_opts_by_config('paginate'));
    //     }
        
    //     $this->limit($limit);
    //     $this->offset($offset);
        
    //     $this->set_config('paginate.enabled', true);
        
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set a manual or modify an existing limit of the results
     * 
     * @access  public
     * 
     * @param   int     $limit      The limit to use
     * 
     * @return  \Table\Table
     */
    // public function limit($limit)
    // {
    //     return $this->query_option('limit', $limit);
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an option or modify a previously set offset
     * 
     * @access  public
     * 
     * @param   int     $offset     The offset to use
     * 
     * @return  \Table\Table
     */
    // public function offset($offset = 0)
    // {
    //     return $this->query_option('offset', $offset);
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set custom query option like 'related', 'where', ...
     * 
     * @access  public
     * 
     * @param   string  $option     The option to set e.g., 'related', 'where', ...
     * @param   mixed   $value      The option-value to pass along. Must be conform
     *                              with the respective method of \Orm\Query::$option
     * 
     * @return  \Table\Table
     */
    // public function query_option($option, $value)
    // {
    //     $this->_query_opts = \Arr::merge($this->_query_opts, array($option => $value));
        
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set multiple custom query options like 'related', 'where', ...
     * 
     * @access  public
     * @see     \Table\Table::query_option()
     * 
     * @param   array   $query_option   Array of query options formatted to work
     *                                  with \Table\Table::query_option()
     * 
     * @return  \Table\Table
     */
    // public function query_options(array $options = array())
    // {
    //     if ( $options )
    //     {
    //         foreach ( $options as $option => $arguments )
    //         {
    //             $this->query_option($option, $arguments);
    //         }
    //     }
        
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Build the query used to gather data
     * 
     * Takes care of the set pagination-, filtering-, searching-, etc options
     * 
     * @access  public
     * 
     * @return  \Orm\Query
     */
    // public function build_query()
    // {
    //     // Already built a query?
    //     if ( $this->_query )
    //     {
    //         // Return it
    //         return $this->_query;
    //     }
        
    //     // Create the query
    //     $q = call_user_func(array($this->_model, 'query'));
        
    //     // Order by something?
    //     array_key_exists('order_by', $this->_query_opts) && $q->order_by($this->_query_opts['order_by']);
        
    //     // Limit by something?
    //     array_key_exists('limit', $this->_query_opts) && $q->limit($this->_query_opts['limit']);
        
    //     // Offset by something?
    //     array_key_exists('offset', $this->_query_opts) && $q->offset($this->_query_opts['offset']);
        
    //     // Where (i.e., filter) by something?
    //     array_key_exists('where', $this->_query_opts) && $q->where($this->_query_opts['where']);
        
    //     // Related models, too?
    //     array_key_exists('related', $this->_query_opts) && array_walk($this->_query_opts['related'], function($relation) use ($q) {
    //         $q = $q->related($relation);
    //     });
        
    //     // Done so far, assign it to the storage and return it
    //     return $this->_query = $q;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Update the internal query e.g., after adding externally some 'related'
     * 
     * @access  public
     * 
     * @param   \Orm\Query  $query  The updated/modified query
     * 
     * @return  \Table\Table
     */
    // public function use_query(\Orm\Query $query)
    // {
    //     $this->_query = $query;
        
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Allows for setting a config like the pagination options or anything else
     * 
     * @access  public
     * @see     \Arr::set
     * 
     * @param   string  $key        The key of the option to set
     * @param   mixed   $value      The value to set for $key
     * 
     * @return  \Table\Table
     */
    // public function set_config($key, $value = null)
    // {
    //     \Arr::set($this->_config, $key, $value);
        
    //     return $this;
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get configuration for table like pagination, ordering, ...
     * 
     * @access  public
     * 
     * @param   string  $key        The key of the config array to get
     * @param   mixed   $default    The default value to return if $key cannot
     *                              be found
     * 
     * @return  mixed   Returns the config value of $key, or $default if not found
     */
    // public function get_config($key, $default = null)
    // {
    //     return \Arr::get($this->_config, $key, $default);
    // }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get query-options from the table configuration
     * 
     * @access  protected
     * 
     * @param   string  $key    The key to get e.g., 'order_by', 'filter', 'paginate'
     * 
     * @return  array           Returns the array formatted properly for usage in
     *                          $this->_query_opts
     */
    // protected function _query_opts_by_config($key)
    // {
    //     $config = array();
        
    //     switch ( $key )
    //     {
    //         case 'order_by':
    //             $default_sort = $this->get_config('sort.default');
                
    //             $cols = call_user_func(array('Input', $this->get_config('sort.method', 'get')), $this->get_config('sort.key.field', 'sort'), null);
    //             $dirns = call_user_func(array('Input', $this->get_config('sort.method', 'get')), $this->get_config('sort.key.dirn', 'dirn'), null);
                
    //             if ( $cols )
    //             {
    //                 $cols = explode('|', $cols);
    //                 $dirns = $dirns ? explode('|', $dirns) : array('asc');
                    
    //                 if ( count($dirns) == 1 )
    //                 {
    //                     $dirns = array_fill(0, count($cols), reset($dirns));
    //                 }
                    
    //                 foreach ( $cols as $k => $col )
    //                 {
    //                     $config[] = array($col, $dirns[$k]);
    //                 }
    //             }
    //             elseif ( $default_sort )
    //             {
    //                 $config = $default_sort;
    //             }
    //         break;
    //         case 'paginate':
    //             $default_offset = $this->get_config('paginate.offset', 0);
    //             $default_limit  = $this->get_config('paginate.limit', 25);
                
    //             $offset = call_user_func(array('Input', $this->get_config('paginate.method', 'get')), $this->get_config('paginate.key.offset', 'offset'), $default_offset);
    //             $limit = call_user_func(array('Input', $this->get_config('paginate.method', 'get')), $this->get_config('paginate.key.limit', 'limit'), $default_limit);
                
    //             ( (int) $offset === intval($offset) && $offset > 0 ) OR $offset = $default_offset;
    //             ( (int) $limit === intval($limit) && $limit > 0 ) OR $limit = $default_limit;
                
    //             $config = array('limit' => $limit, 'offset' => $offset);
    //         break;
    //         default:
    //             throw new InvalidArgumentException('Config-key to get must be a valid one, [' . $key . '] given');
    //         break;
    //     }
        
    //     return $config;
    // }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic set
     * 
     * Allows setting properties of the table directly. It can be any of the magic
     *  keywords 'head', 'foot', 'row' which takes the same arguments as the
     *  respective set_header(), set_foot(), add_row() methods. If $property is
     *  non of these reserved keywords, it will be set as an attribute of the
     *  table
     * 
     * @access  public
     * 
     * @param   string  $property   The property to set
     * @param   mixed   $value      The value to set for $property
     */
    public function __set($property, $value = null)
    {
        // // If the property to-set is 'head', 'foot', or 'row', we allow
        // //  "magic" methods
        // if ( preg_match('/head|foot|body|row/', $property) )
        // {
        //     // Set a row? Then add a row, otherwise set either 'head' or 'foot',
        //     //  all by calling the respective methods
        //     $property == 'row' && call_user_func(array($this, 'add_row'), $value) OR call_user_func(array($this, 'set_' . $property), $value);
        // }
        // No "magic" method, so $property is assumed an attribute;
        // else
        // {
            $this->set($property, $value);
        // }
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get a property of the table
     * 
     * 
     * @access  public
     * 
     * @param   string  $property   The name of the property to get
     * 
     * @return  mixed   Returns the value of the property or null if it does not exist
     */
    public function & __get($property)
    {
        return $this->get($property);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic __call method
     * 
     * Allows for getting and setting properties of the table via e.g.
     *  $table->get_header(),
     *    or
     *  $table->set_class('active')
     * 
     * @access  public
     * 
     * @param   string  $method     The non-existing method that was being called
     * @param   array   $args       The arguments passed to the original method-call
     * 
     * @throws  BadMethodCallException  If method cannot be translated to neither get()
     *                                  or set()
     * 
     * @return  mixed   Returns either the result of set() or get() or throws an Exception
     */
    public function __call($method, $args = array())
    {
        // Allow magic 'get_***' and 'set_***'
        if ( false !== strpos($method, 'get_') OR false !== strpos($method, 'set_') )
        {
            // Get the property by extracting everything after the first underscore
            $property = substr($method, 4);
            // Get the method which is either 'set' or 'get'
            $method = substr($method, 0, 3);
            // And unshift the arguments by the property
            array_unshift($args, $property);
            
            // Call the respective set() or get() method
            return call_user_func_array(array($this, $method), $args);
        }
        
        // Throw an exception
        throw new \BadMethodCallException('Call to undefined method ' . get_called_class() . '::' . $method . '()');
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic __toString method to render the table
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table
     */
    public function __toString()
    {
        return $this->render();
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Countable Interface
     */
    
    /**
     * [count description]
     * @return [type] [description]
     */
    public function count()
    {
        $this->add_body();
        
        return count($this->_body);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    public function current()
    {
        return $this->_body[$this->key()];
    }
    
    public function rewind()
    {
        $this->_curr_row = 0;
    }
    
    public function key()
    {
        return $this->_curr_row;
    }
    
    public function next()
    {
        ++$this->_curr_row;
    }
    
    public function valid()
    {
        return isset($this->_body[$this->_curr_row]);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ArrayAccess Interface
     */
    
    public function offsetExists($offset)
    {
        return $this->_body && isset($this->_body[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ( ! $this->offsetExists($offset) )
        {
            throw new OutOfBoundsException('Access to undefined index [' . $offset . ']');
        }
        
        return $this->_body[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException('Cannot set index [' . $offset . '] as rows are read-only');
    }
    
    public function offsetUnset($offset)
    {
        if ( $this->offsetExists($offset) )
        {
            unset($this->_body[$offset]);
        }
    }
    
}
