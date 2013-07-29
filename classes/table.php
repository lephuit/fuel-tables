<?php namespace Table;

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
    public static function forge($name = '_default_', array $attributes = array())
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
            static::$_instances[$name] = static::forge($attributes);
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
     * The table's footer-object
     * 
     * 
     * @access  protected
     * @var     \Table\Group_Footer
     */
    protected $_footer = null;
    
    /**
     * The table's header-object
     * 
     * @access  protected
     * @var     \Table\Group_Header
     */
    protected $_header = null;
    
    
    
    
    
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
     * Can be either 'header', 'footer', 'body', 'row', 'row_N', or an attribute
     *  name e.g., 'class'
     * 
     * @param   string  $property   The name of the property to get. Can be 'header',
     *                              'footer', 'row', 'row_N', or any key of the
     *                              table's attributes array
     * @param   mixed   $default    The default value to return if the attribute
     *                              cannot be found. If $property is 'row', then
     *                              $default must be the number of the row to return.
     *                              If omitted, the last row will be returned.
     *                              Defaults to null
     * @return  mixed               Returns the property that matched or the
     *                              Header|Footer|Body|Row-object
     */
    public function get($property, $default = null)
    {
        // Match magic properties header, footer, body
        if ( preg_match('/header|footer|body/', $property) )
        {
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
        // Append it? Then use our helper to add the attribute, otherwise just overwrite it
        $append === true && Helpers::add_attribute($this->_attributes, $property, $value) OR $this->_attributes[$property] = $value;
        
        // Return for chaining
        return $this;
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic set
     * 
     * Allows setting properties of the table directly. It can be any of the magic
     *  keywords 'header', 'footer', 'row' which takes the same arguments as the
     *  respective set_header(), set_footer(), add_row() methods. If $property is
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
        // If the property to-set is 'header', 'footer', or 'row', we allow
        //  "magic" methods
        if ( preg_match('/header|footer|body|row/', $property) )
        {
            // Set a row? Then add a row, otherwise set either 'header' or 'footer',
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
     * Countable Interface
     */
    
    /**
     * [count description]
     * @return [type] [description]
     */
    public function count()
    {
        return count($this->_rows);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    public function current()
    {
        return $this->_rows[$this->key()];
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
        return isset($this->_rows[$this->_curr_row]);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ArrayAccess Interface
     */
    
    public function offsetExists($offset)
    {
        return isset($this->_rows[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ( ! $this->offsetExists($offset) )
        {
            throw new OutOfBoundsException('Access to undefined index [' . $offset . ']');
        }
        
        return $this->_rows[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException('Cannot set index [' . $offset . '] as rows are read-only');
    }
    
    public function offsetUnset($offset)
    {
        if ( $this->offsetExists($offset) )
        {
            unset($this->_rows[$offset]);
        }
    }
    
}
