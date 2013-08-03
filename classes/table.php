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
 * @copyright  2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

use Countable;
use Iterator;
use ArrayAccess;

class Table implements Countable, Iterator, ArrayAccess {
    
    /**
     * Storage for all table-instances
     * 
     * @access  protected
     * @var     array
     */
    protected static $_instances = array();
    
    
    
    
    
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
    public static function forge(array $attributes = array())
    {
        // Return a new \Table\Table-object
        return new static($attributes);
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
        // New instance?
        if ( ! isset(static::$_instances[$name]) )
        {
            // Then forge it
            static::$_instances[$name] = static::forge();
        }
        
        // And return it
        return static::$_instances[$name];
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
    protected $_foot = null;
    
    /**
     * The table's head-object
     * 
     * @access  protected
     * @var     \Table\Group_head
     */
    protected $_head = null;
    
    /**
     * Stores the namespace model-name for getting the data
     * 
     * @access  protected
     * @var     string
     */
    protected $_model = null;
    
    /**
     * Keeps the query used to get data from the DB
     * 
     * @access  protected
     * @var     \Orm\Query
     */
    protected $_query = null;
    
    /**
     * Keeps the query options like 'limit', 'offset', 'order_by'
     * 
     * @access  protected
     * @var     array
     */
    protected $_query_opts = array();
    
    /**
     * The columns that are set via set_columns and needed to hydrate the table
     * 
     * @access  protected
     * @var     array
     */
    protected $_columns = array();
    
    protected $_config = array();
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Construct a new table-object and assign its default attributes
     * 
     * @access  public
     * 
     * @param   array   $attributes     The attributes to set for the table
     */
    public function __construct(array $attributes = array())
    {
        $this->_attributes = $attributes;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get some property of the table
     * 
     * Can be either 'head', 'foot', 'body', 'row', 'row_N', or an attribute
     *  name e.g., 'class'
     * 
     * @param   string  $property   The name of the property to get. Can be 'head',
     *                              'foot', 'row', 'row_N', or any key of the
     *                              table's attributes array
     * @param   mixed   $default    The default value to return if the attribute
     *                              cannot be found. If $property is 'row', then
     *                              $default must be the number of the row to return.
     *                              If omitted, the last row will be returned.
     *                              Defaults to null
     * @return  mixed               Returns the property that matched or the
     *                              head|foot|Body|Row-object
     */
    public function get($property, $default = null)
    {
        // Match magic properties head, foot, body
        if ( preg_match('/head|foot|body/', $property) )
        {
            if ( ! isset($this->{'_'.$property}) )
            {
                return $this->{'add_' . $property}();
                
                // throw new \OutOfBoundsException('Cannot get [' . $property . '] for table if it has not been created yet');
            }
            
            return $this->{'_'.$property};
        }
        // Match magic property 'row' or 'row_N'
        elseif ( 0 === strpos($property, 'row') )
        {
            // Either $property == 'row_4' OR 'row', if it's 'row', then the
            //  offset is (unfortunately) given in $default
            $offset = ( false !== strpos('row_', $property) ? substr($property, 4) : ( $default ? : count($this->_rows) - 1 ) );
            
            // And use the implemented ArrayAccess-Interface to return the requested
            //  row
            return $this[$offset];
        }
        
        // No magic property, so we will return the matching attribute (if it exists)
        //  otherwise $default
        return isset($this->_attributes[$property]) ? $this->_attributes[$property] : $default;
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
        if ( $property === 'attributes' )
        {
            if ( ! is_array($value) )
            {
                throw new InvalidArgumentException('To set attributes on the table an array must be provided but ' . gettype($value) . ' given');
            }
            
            $this->_attributes = $value;
        }
        else
        {
            // Append it? Then use our helper to add the attribute, otherwise just overwrite it
            $append === true && Helpers::add_attribute($this->_attributes, $property, $value) OR $this->_attributes[$property] = $value;
        }
        // Append it? Then use our helper to add the attribute, otherwise just overwrite it
        $append === true && Helpers::add_attribute($this->_attributes, $property, $value) OR $this->_attributes[$property] = $value;
        
        // Return for chaining
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
     * @return  \Table\Group_Head
     */
    public function set_columns(array $columns = array())
    {
        // We need to have a head-group or we will forge one
        $this->_head OR $this->add_head();
        
        // The default options we accept for a column
        $defaults = array(
            'attributes'    => array(),
            'use'           => null,
            'as'            => null,
            'sanitize'      => false,
        );
        
        // Loop over the given columns to add them
        foreach ( $columns as $column => $options )
        {
            // Got an array for the options?
            if ( is_array($options) )
            {
                // Does it contain any of the keys from $defaults? Then 
                // array_diff_assoc($defaults, $options) && $options = array('attributes' => $options);
                // Merge the given options with the defaults
                $options = \Arr::merge($defaults, $options);
                
                // What key to use to put inside the cells?
                $options['use'] OR $options['use'] = $column;
                
                // What to display in the table header?
                $options['as'] && $column = $options['as'] OR $column = $options['use'];
            }
            // $options is no array so we will swich $column and $options
            else
            {
                $column = $options;
                $options = $defaults;
            }
            
            // And add a new cell to the head by calling Cell_Head::forge() so
            //  we can chain to sanitize() as well
            $this->_head->add_cell(
                Cell::forge(
                    Cell::HEAD,
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
     * @see     \Table\Group_Head::set_columns()
     * 
     * @param   array   $columns    The columns to use for the header
     * @param   array   $attributes Attributes to pass to the header
     * 
     * @return  \Table\Group_Head
     */
    public function add_head(array $columns = array(), array $attributes = array())
    {
        return $this->_head = Group::forge(Group::HEAD, $columns, $attributes);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a footer to the table and return the footer-object
     * 
     * @access  public
     * 
     * @param   array   $attributes Attributes to pass to the footer
     * 
     * @return  \Table\Group_Foot
     */
    public function add_foot(array $attributes = array())
    {
        return $this->_foot = Group::forge(Group::FOOT, array(), $attributes);
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
        return $this->_body = Group::forge(Group::BODY, array(), $attributes);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a row to the body of the table
     * 
     * @access  public
     * 
     * @return  \Table\Group_Body
     */
    public function add_row(array $columns = array())
    {
        $this->_body OR $this->add_body();
        
        $this->_body->add_row($columns);
        
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
            
            $head = ( $this->_head ? $this->_head->render() : '' );
            
            $foot = ( $this->_foot ? $this->_foot->render() : '' );
            
            $body = ( $this->_body ? $this->_body->render() : '' );
            
            return html_tag('table', $this->_attributes, $head . PHP_EOL . $foot . PHP_EOL . $body);
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
        if ( ! $data )
        {
            if ( ! ( $this->_model && $this->_columns ) )
            {
                return $this;
                
                throw new HydrationException('No Model or columns set for table');
            }
            
            // Then build our query
            $q = $this->build_query();
            
            // And get the results
            $results = $q->get();
            
            // Got none?
            if ( ! $results )
            {
                // Well, then we're done
                return $this;
            }
            
            $data = array();
            
            // Loop over the results we gathered
            foreach ( $results as $result )
            {
                // Convert ORM objects to arrays
                $data[] = $result->to_array();
            }
        }
        
        // We don't want duplicate data inside the body, so assign a new body
        //  but keep the old attributes (if there's an old body)
        $body_attributes = ( $this->_body ? $this->_body->get('attributes') : array() );
        $body = $this->add_body(array(), $body_attributes);
        
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
    public function order_by($property, $dirn = 'ASC"')
    {
        if ( $property === true )
        {
            if ( $order_by = $this->_query_opts_by_config('order_by') )
            {
                $this->_query_opts['order_by'] = $order_by;
            }
            
            return $this;
        }
        
        // Taken from \Orm\Query
        if ( is_array($property) )
        {
            foreach ( $property as $p => $d )
            {
                if ( is_int($p) )
                {
                    is_array($d) ? $this->order_by($d[0], $d[1]) : $this->order_by($d, $direction);
                }
                else
                {
                    $this->order_by($p, $d);
                }
            }
            
            return $this;
        }
        
        // Ensure we have the key inside our query-options
        array_key_exists('order_by', $this->_query_opts) OR $this->_query_opts['order_by'] = array();
        
        // Store in our local query-opts
        $this->_query_opts['order_by'][] = array($property, $dirn);
        
        // For chaining
        return $this;
    }
    
    
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
    public function paginate($limit = null, $offset = null)
    {
        if ( $limit === true OR ( is_null($limit) && is_null($offset) ) )
        {
            extract($this->_query_opts_by_config('paginate'));
        }
        
        $this->limit($limit);
        $this->offset($offset);
        
        $this->set_config('paginate.enabled', true);
        
        return $this;
    }
    
    
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
    public function limit($limit)
    {
        return $this->query_option('limit', $limit);
    }
    
    
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
    public function offset($offset = 0)
    {
        return $this->query_option('offset', $offset);
    }
    
    
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
    public function query_option($option, $value)
    {
        $this->_query_opts = \Arr::merge($this->_query_opts, array($option => $value));
        
        return $this;
    }
    
    
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
    public function query_options(array $options = array())
    {
        if ( $options )
        {
            foreach ( $options as $option => $arguments )
            {
                $this->query_option($option, $arguments);
            }
        }
        
        return $this;
    }
    
    
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
    public function build_query()
    {
        // Already built a query?
        if ( $this->_query )
        {
            // Return it
            return $this->_query;
        }
        
        // Create the query
        $q = call_user_func(array($this->_model, 'query'));
        
        // Order by something?
        array_key_exists('order_by', $this->_query_opts) && $q->order_by($this->_query_opts['order_by']);
        
        // Limit by something?
        array_key_exists('limit', $this->_query_opts) && $q->limit($this->_query_opts['limit']);
        
        // Offset by something?
        array_key_exists('offset', $this->_query_opts) && $q->offset($this->_query_opts['offset']);
        
        // Where (i.e., filter) by something?
        array_key_exists('where', $this->_query_opts) && $q->where($this->_query_opts['where']);
        
        // Related models, too?
        array_key_exists('related', $this->_query_opts) && array_walk($this->_query_opts['related'], function($relation) use ($q) {
            $q = $q->related($relation);
        });
        
        // Done so far, assign it to the storage and return it
        return $this->_query = $q;
    }
    
    
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
    public function use_query(\Orm\Query $query)
    {
        $this->_query = $query;
        
        return $this;
    }
    
    
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
    public function set_config($key, $value = null)
    {
        \Arr::set($this->_config, $key, $value);
        
        return $this;
    }
    
    
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
    public function get_config($key, $default = null)
    {
        return \Arr::get($this->_config, $key, $default);
    }
    
    
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
    protected function _query_opts_by_config($key)
    {
        $config = array();
        
        switch ( $key )
        {
            case 'order_by':
                $default_sort = $this->get_config('sort.default');
                
                $cols = call_user_func(array('Input', $this->get_config('sort.method', 'get')), $this->get_config('sort.key.field', 'sort'), null);
                $dirns = call_user_func(array('Input', $this->get_config('sort.method', 'get')), $this->get_config('sort.key.dirn', 'dirn'), null);
                
                if ( $cols )
                {
                    $cols = explode('|', $cols);
                    $dirns = $dirns ? explode('|', $dirns) : array('asc');
                    
                    if ( count($dirns) == 1 )
                    {
                        $dirns = array_fill(0, count($cols), reset($dirns));
                    }
                    
                    foreach ( $cols as $k => $col )
                    {
                        $config[] = array($col, $dirns[$k]);
                    }
                }
                elseif ( $default_sort )
                {
                    $config = $default_sort;
                }
            break;
            case 'paginate':
                $default_offset = $this->get_config('paginate.offset', 0);
                $default_limit  = $this->get_config('paginate.limit', 25);
                
                $offset = call_user_func(array('Input', $this->get_config('paginate.method', 'get')), $this->get_config('paginate.key.offset', 'offset'), $default_offset);
                $limit = call_user_func(array('Input', $this->get_config('paginate.method', 'get')), $this->get_config('paginate.key.limit', 'limit'), $default_limit);
                
                ( (int) $offset === intval($offset) && $offset > 0 ) OR $offset = $default_offset;
                ( (int) $limit === intval($limit) && $limit > 0 ) OR $limit = $default_limit;
                
                $config = array('limit' => $limit, 'offset' => $offset);
            break;
            default:
                throw new InvalidArgumentException('Config-key to get must be a valid one, [' . $key . '] given');
            break;
        }
        
        return $config;
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic set
     * 
     * Allows setting properties of the table directly. It can be any of the magic
     *  keywords 'head', 'foot', 'row' which takes the same arguments as the
     *  respective set_head(), set_foot(), add_row() methods. If $property is
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
        // If the property to-set is 'head', 'foot', or 'row', we allow
        //  "magic" methods
        if ( preg_match('/head|foot|body|row/', $property) )
        {
            // Set a row? Then add a row, otherwise set either 'head' or 'foot',
            //  all by calling the respective methods
            $property == 'row' && call_user_func(array($this, 'add_row'), $value) OR call_user_func(array($this, 'set_' . $property), $value);
        }
        // No "magic" method, so $property is assumed an attribute;
        else
        {
            $this->set($property, $value);
        }
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
    public function __get($property)
    {
        return $this->get($property);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic __call method
     * 
     * Allows for getting and setting properties of the table via e.g.
     *  $table->get_head(),
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
        if ( false !== strpos($method, 'get_') OR false !== strpos($method, 'set_') )
        {
            $property = str_replace(array('set_', 'get_'), '', $method);
            $method = substr($method, 0, 3);
            array_unshift($args, $property);
            
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
        $this->_body OR $this->add_body();
        
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
