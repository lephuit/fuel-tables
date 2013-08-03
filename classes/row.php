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
    public static function forge($type, array $values = array(), array $attributes = array())
    {
        return new static($type, $values, $attributes);
    }
    
    
    
    
    
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
        
        $values && $this->add_cells($values);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set or unset the callback to sanitize the content
     * 
     * @access  public
     * 
     * @param   mixed   $callback   The callback to use for sanitizing. Must be
     *                              a callable string (e.g., 'Security::htmlentities')
     *                              or a closure. If set to false, then sanitation
     *                              will be deactivated for all cells, if omitted
     *                              the filter will be 'Security::htmlentities'.
     *                              If an array, it must have the same number of
     *                              values as there are cells so that each cell
     *                              has its own sanitizer.
     *                              Defaults to 'Security::htmlentities'
     * 
     * @return  \Table\Row
     */
    public function sanitize($callback = 'Security::htmlentities')
    {
        // Not an array of callbacks?
        if ( ! is_array($callback) )
        {
            // Just make it an array with all the same callbacks
            $callback = array_fill(0, count($this->_cells), $callback);
        }
        
        // // Allow passing multiple callbacks per cell, but we need to ensure having
        // //  the same number of sanitizers as we have cells
        // if ( count($callback) < count($this->_cells) )
        // {
        //     // We don't, so throw an exception
        //     throw new InvalidArgumentException('Number of callbacks must be equal to the number of cells');
        // }
        // // And in case there have been too many callbacks, we will just strip off
        // //  the ones too many
        // elseif ( count($callback) > count($this->_cells) )
        // {
        //     $callback = @array_splice($callback, 0, count($this->_cells));
        // }
        
        // Loop over every callback
        foreach ( $callback as $k => $_callback )
        {
            // If the callback is either 1, or -1, then we assume it's the mode
            //  and the key is the actual callback
            if ( ( $_callback === 1 ) OR ( $_callback === -1 ) )
            {
                $_mode = $_callback;
                $_callback = $k;
                // And tell the respective cell to use that callback
                $this->_cells[$k]->sanitize($_callback, $_mode);
            }
            // Callback is false? Then reset callback for the cell
            elseif ( $_callback === false )
            {
                $this->_cells[$k]->sanitize($_callback);
            }
            // Otherwise the callback is unchanged and appended
            else
            {
                $this->_cells[$k]->sanitize($_callback, 0);
            }
        }
        
        // Done, return me for chaining
        return $this;
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
        // Lazy as we are we will be using fuel's html_tag
        return html_tag(
            // The row-tag to open (basically always 'tr')
            $this->_row_tag,
            // The attributes
            $this->_attributes,
            // See if we have cells
            ( $this->_cells
                // Then implode those with PHP_EOL
                ? implode(
                    PHP_EOL,
                    // After calling the 'render' method of every cell
                    array_map(
                        function($cell)
                        {
                            return $cell->render();
                        },
                        $this->_cells
                    )
                )
                // No rows, then there's no body but we cannot use false, as this
                //  would result in '<tr />' instead of '<tr></tr>' due to html_tag's
                //  logic
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
        
        $cell = ( $value instanceof $class ? $value : Cell::forge($this->_type, $value, $attributes) );
        
        $this->_cells[] =& $cell;
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Add multiple cells to this row
     * 
     * @access  public
     * @see     \Table\Cell
     * 
     * @param   array   $values     An array of values or an array of
     *                              value => attributes.
     * 
     * @return   \Table\Row
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
