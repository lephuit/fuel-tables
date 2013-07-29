<?php namespace Table;

use ArrayAccess;
use Countable;
use Iterator;

class Row implements ArrayAccess, Countable, Iterator {
    
    /**
     * Keeps the html-attributes of the row
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * Keeps the rows added to the row
     * 
     * @access  protected
     * @var     array
     */
    protected $_cells = array();
    
    /**
     * For ArrayAccess
     * 
     * @access  protected
     * @var     integer
     */
    protected $_curr_cell = 0;
    
    /**
     * Keeps the type of the row (head, foot, or body)
     * 
     * @access  protected
     * @var     string
     */
    protected $_type;
    
    
    protected $_row_tag = 'tr';
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Create a new table-row with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{row_tag}>'
     */
    public function __construct($type, array $values = array(), array $attributes = array())
    {
        $this->_type        = $type;
        $this->_attributes  = $attributes;
        
        if ( $values )
        {
            foreach ( $values as $val )
            {
                $this->add_cell($val, array(), $type);
            }
        }
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Renders the row's content
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-row with rows
     */
    public function render()
    {
        return html_tag(
            $this->_row_tag,
            $this->_attributes,
            ( $this->_cells
                ? implode(
                    PHP_EOL,
                    array_map(
                        function($cell)
                        {
                            return $cell->render();
                        },
                        $this->_cells
                    )
                )
                : ''
            )
        );
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an attribute of the row
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
     * @return  \Table\Row
     */
    public function set($attribute, $value = null, $mode = false)
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
     * Get a property from the row. Either an attribute or a row
     * 
     * @param   string  $property   The property to get. Can be 'cell' or 'cell_N'
     *                              to return the last or N-th row.  If it does not
     *                              match, then $property is assumed an attribute
     * @param   mixed   $default    The default value to return if no matching
     *                              attribute was found. In case $property == 'cell',
     *                              $default can be used to indicate the number of
     *                              the row to get
     * 
     * @return  mixed   Returns the value of $property, if a row then \Table\Row
     */
    public function get($property, $default = null)
    {
        // Match magic properties starting with 'cell'
        if ( preg_match('/^cell/', $property) )
        {
            // Get the offset. Either $property was 'cell_N' and we want 'N' or it
            //  was 'cell' and then we will use $default as the offset
            $offset = ( false !== strpos('cell_', $property) ? substr($property, 4) : ( $default ? : count($this->_cells) - 1 ) );
            
            // Use ArrayAccess to return
            return $this[$offset];
        }
        
        // Assume an attribute, so return that one (if found, otherwise $default)
        return array_key_exists($property, $this->_attributes) ? $this->_attributes[$property] : $default;
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
     * @return  \Table\Row
     */
    public function add($attribute, $value, $prepend = false)
    {
        return $this->set($attribute, $value, $prepend === false ? 1 : -1);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add a cell to the row
     * 
     * @access  public
     * 
     * @param   mixed   $values     The values to add into the row
     * @param   array   $attributes Array of attributes to pass along to the
     *                              row
     * 
     * @return  \Table\Row
     */
    public function add_cell($value, array $attributes = array())
    {
        $class = 'Table\\Cell_' . $this->_type;
        
        $this->_cells[] = ( $value instanceof $class ? $value : new $class($value, $attributes) );
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Allows to skip one or more cells
     * 
     * @access  public
     * 
     * @param   integer     $skip   The number of cells to skip.  Defaults to 1
     * 
     * @return  \Table\Row
     */
    public function skip_cell($skip = 1)
    {
        // Make sure we have just integers
        $skip = intval($skip);
        
        // And then add as many empty cells as requested
        do
        {
            $this->add_cell('');
            $skip--;
        }
        while ( $skip > 0 );
        
        // For chaining
        return $this;
    }
    
    
    /**
     * Remove an attribute's value
     * 
     * @access  public
     * 
     * @param   string  $attribute  The attribute's name to remove
     * @param   string  $value      The value of the attribute to remove. If omitted
     *                              it will empty or unset the attribute.
     *                              Defaults to null
     * @param   boolean $purge      Whether to remove empty-value keys from the
     *                              attributes array
     * 
     * @return  \Table\Row
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
     * @return  \Table\Row
     */
    public function clear($attribute)
    {
        return $this->remove($attribute, null, true);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Support echoing the row by using __toString as a wrapper for render()
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-row with rows
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
        $this->set($attribute, $value);
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
        return $this->get($property);
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
        return count($this->_cells);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Iterator Interface
     */
    
    public function current()
    {
        return $this->_cells[$this->key()];
    }
    
    public function rewind()
    {
        $this->_curr_cell = 0;
    }
    
    public function key()
    {
        return $this->_curr_cell;
    }
    
    public function next()
    {
        ++$this->_curr_cell;
    }
    
    public function valid()
    {
        return isset($this->_cells[$this->_curr_cell]);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * ArrayAccess Interface
     */
    
    public function offsetExists($offset)
    {
        return isset($this->_cells[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ( ! $this->offsetExists($offset) )
        {
            throw new OutOfBoundsException('Access to undefined column-index [' . $offset . ']');
        }
        
        return $this->_cells[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException('Cannot set index [' . $offset . '] as columns are read-only');
    }
    
    public function offsetUnset($offset)
    {
        if ( $this->offsetExists($offset) )
        {
            unset($this->_cells[$offset]);
        }
    }
    
}
