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

abstract class Group implements ArrayAccess, Countable, Iterator {
    
    /**
     * Supported types of groups
     */
    const BODY = 'Body';
    const FOOTER = 'Footer';
    const HEADER = 'Header';
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Forge a new table-group with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $columns        An array of columns to use
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{group_tag}>'
     */
    public static function forge(array $attributes = array(), $type = Group::BODY)
    {
        $class = 'Table\\Group_' . ucwords($type);
        
        return new $class($attributes, $type);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Forge a new row-instance
     * 
     * @access  public
     * @static
     * 
     * @param   array   $attributes     The attributes to put inside the row's
     *                                  opening tag
     * 
     * @return  \Table\Cell
     */
    public static function new_row(array $attributes = array(), $type = Row::BODY)
    {
        return Row::forge($attributes, $type);
    }
    
    
    
    
    
    /**
     * Keeps the group's tag like e.g., 'thead', 'tbody', or 'tfoot'
     * 
     * Must be implemented by the respective group itself
     * 
     * @access  protected
     * @var     string
     */
    // protected $_group_tag;
    
    /**
     * Keeps the html-attributes of the group
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * Meta values to store
     * 
     * @access  protected
     * @var     array
     */
    protected $_meta = array();
    
    /**
     * Keeps the rows added to the group
     * 
     * @access  protected
     * @var     array
     */
    protected $_rows = array();
    
    /**
     * Keeps the current row
     * 
     * @access  protected
     * 
     * @var     \Table\Row
     */
    protected $_row = null;
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Create a new table-group with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $columns        An array of columns to use
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{group_tag}>'
     */
    public function __construct(array $attributes = array(), $type)
    {
        $this->_attributes  = $attributes;
        $this->_type        = $type;
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Renders the group's content
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-group with rows
     */
    public function render()
    {
        return html_tag(
            $this->_group_tag,
            $this->_attributes,
            ( $this->_rows
                ? implode(
                    PHP_EOL,
                    array_map(
                        function($row)
                        {
                            return $row->render();
                        },
                        $this->_rows
                    )
                )
                : ''
            )
        );
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an attribute of the group
     * 
     * @access  public
     * 
     * @param   string      $attribute  The attr
     * @param   string      $value      The value of the attribute to set
     * @param   boolean     $mode       The mode of setting the attribute. If
     *                                  omitted, $value will be set as the only
     *                                  value for the attribute. If set to 1,
     *                                  $value will be appended, if set to -1
     *                                  it will be prepended.
     *                                  Defaults to false i.e., overwrite
     * 
     * @return  \Table\Group
     */
    public function set_attribute($attribute, $value = null, $mode = false)
    {
        // Prepend?
        if ( $mode === -1 )
        {
            Helpers::add_attribute($this->_attributes, $attribute, $value, true);
        }
        // Any other case we will append
        elseif ( $mode === 1 )
        {
            Helpers::add_attribute($this->_attributes, $attribute, $value, false);
        }
        // Not adding, but setting i.e., replacing
        else
        {
            $this->_attributes[$attribute] = $value;
        }
        
        // And a chainable return
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * [set_meta description]
     * @param [type] $meta  [description]
     * @param [type] $value [description]
     */
    public function set_meta($meta, $value = null)
    {
        $this->_meta[$meta] = $value;
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get a property from the group. Either an attribute or a row
     * 
     * @param   string  $property   The property to get. Can be 'row' or 'row_N'
     *                              to return the last or N-th row.  If it does not
     *                              match, then $property is assumed an attribute
     * @param   mixed   $default    The default value to return if no matching
     *                              attribute was found. In case $property == 'row',
     *                              $default can be used to indicate the number of
     *                              the row to get
     * 
     * @return  mixed   Returns the value of $property, if a row then \Table\Row_{group_tag}
     */
    public function get_attribute($property, $default = null)
    {
        if ( $property === 'attributes' )
        {
            return $this->_attributes;
        }
        
        // Assume an attribute, so return that one (if found, otherwise $default)
        return \Arr::get($this->_attributes, $property, $default);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * [get_meta description]
     * @param  [type] $meta    [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public function get_meta($meta = null, $default = null)
    {
        if ( is_null($meta) )
        {
            return $this->_meta;
        }
        
        return \Arr::get($this->_meta, $meta, $default);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add an attribute to the array of attributes
     * 
     * @access  public
     * 
     * @param   string  $attribute  Name of the attribute to add e.g., 'class'
     * @param   mixed   $value      The value to set for $attribute
     * @param   boolean $prepend    Whether to prepend (false) or append (true)
     *                              $value to the classes' attributes.
     *                              Defaults to false
     * 
     * @return  \Table\Group
     */
    public function add_attribute($attribute, $value, $prepend = false)
    {
        return $this->set_attribute($attribute, $value, $prepend === false ? 1 : -1);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a row to the current group
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   array   $values     Array of values to add into the cells of the
     *                              row.
     * @param   array   $attributes Attributes to add to the row-opening tag
     * 
     * @return  \Table\Row          Returns the just created row-object
     */
    public function & add_row(array $values = array(), array $attributes = array())
    {
        $row = ( $values instanceof Row ? $values : static::new_row(array(), $attributes, str_replace('Table\\Group_', '', get_called_class()))->add_cells($values) );
        
        $this->_rows[]  =& $row;
        $this->_row     =& $row;
        
        return $row;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Append a cell to the current row
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   string  $value      The value of the cell
     * @param   array   $attributes Array of html-attributes of the cell
     * 
     * @return   \Table\Row
     */
    public function & add_cell($value = '', array $attributes = array())
    {
        $row = $this->_row ? : $this->add_row();
        
        $cell = $row->add_cell($value, $attributes);
        
        return $cell;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add multiple cells at once to the last row
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   array   $values     An array of values or an array of
     *                              value => attributes.
     * 
     * @return   \Table\Group
     */
    public function add_cells(array $values = array())
    {
        if ( ! $values )
        {
            return $this;
        }
        
        foreach ( $values as $value => $attributes )
        {
            if ( ! is_array($attributes) )
            {
                $value = $attributes;
                $attributes = array();
            }
            
            $this->add_cell($value, $attributes);
        }
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Remove an attribute's value
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute's name to remove
     * @param   string  $value      The value of the attribute to remove
     * 
     * @return  \Table\Group
     */
    public function remove($attribute, $value = null, $purge = false)
    {
        Helpers::remove_attribute($this->_attributes, $attribute, $value, $purge);
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Clear an attribute i.e., remove it completely
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute's name to remove
     * 
     * @return  \Table\Group
     */
    public function clear($attribute)
    {
        return $this->remove($attribute, null, true);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Support echoing the group by using __toString as a wrapper for render()
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-group with rows
     */
    public function __toString()
    {
        return $this->render();
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic set to set attributes
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute to set
     * @param   string  $value      The value to set.  Defaults to null
     */
    public function __set($attribute, $value = null)
    {
        $this->set_attribute($attribute, $value);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Magic get that does nothing but return the classes' get() result
     * 
     * @access  public
     * @param   string  $property   The property to get
     * 
     * @return  mixed   Returns the value for $property, if not found null
     */
    public function __get($property)
    {
        return $this->get_attribute($property);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * [__call description]
     * @param  [type] $method [description]
     * @param  array  $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args = array())
    {
        // Throw an exception
        throw new BadMethodCallException('Call to undefined method ' . get_called_class() . '::' . $method . '()');
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Countable Interface
     */
    
    public function count()
    {
        return count($this->_rows);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    /**
     * For Iterator Interface
     * 
     * @access  protected
     * @var     integer
     */
    protected $_curr_row = 0;
    
    
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
            throw new OutOfBoundsException('Access to undefined row-index [' . $offset . ']');
        }
        
        return $this->_rows[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException('Cannot set row-index [' . $offset . '] as rows are read-only');
    }
    
    public function offsetUnset($offset)
    {
        if ( $this->offsetExists($offset) )
        {
            unset($this->_rows[$offset]);
        }
    }
    
}
