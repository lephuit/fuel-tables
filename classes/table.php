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
     * The columns that are set via set_columns and needed to hydrate the table
     * 
     * @access  protected
     * @var     array
     */
    protected $_columns = array();
    
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
        // Return the attribute requested
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
            if ( ! is_array($value) and ! $value instanceof \ArrayAccess)
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
        foreach ( $columns as $identifier => $options )
        {
            // Got an array for the options?
            if ( is_array($options) )
            {
                // Does it contain any of the keys from $defaults? Then 
                // array_diff_assoc($defaults, $options) && $options = array('attributes' => $options);
                // Merge the given options with the defaults
                $options = \Arr::merge($defaults, $options);
                
                // What key to use to put inside the cells?
                $options['use'] OR ( $options['use'] = $identifier && $identifier = null );
                
                // What to display in the table header?
                $options['as'] && $identifier = \Lang::get($options['as'], array(), $options['as']);
            }
            // $options is no array so we assume $identifier to be the identifier and $options
            //  to be the value to display
            else
            {
                $identifier = $options;
                $options = $defaults;
            }
            
            // And add a new cell to the header by calling Cell_Header::forge() so
            //  we can chain to sanitize() as well
            $this->_header->add_cell(
                Cell::forge(
                    $identifier,
                    \Arr::get($options, 'attributes', array()),
                    Cell::HEADER
                )#->sanitize($options['sanitize'])
            );
            
            \Arr::delete($options, 'attributes');
            
            $this->_columns[] = $options;
        }
        
        // Return the table for chaining
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
    public function add_header(array $attributes = array())
    {
        $this->_header instanceof Group_Header OR $this->_header = Group::forge($attributes, Group::HEADER);
        
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
        $this->add_header();
        
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
        $this->_footer instanceof Group_Footer OR $this->_footer = Group::forge($attributes, Group::FOOTER);
        
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
        $this->_body instanceof Group_Body OR $this->_body = Group::forge($attributes, Group::BODY);
        
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
     * Get the current row from the body
     * 
     * @access  public
     * 
     * @return  \Table\Row_Body
     */
    public function & get_row()
    {
        return $this->get_body()->get_row();
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
            return $e->getMessage();
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
            return $this;
        }
        
        return $this;
        
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
        $this->set($property, $value);
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
        return count($this->get_body());
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    /**
     * Current row integer used for Iterator Interface
     * 
     * @access  protected
     * @var     int
     */
    protected $_curr_row = 0;
    
    
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
