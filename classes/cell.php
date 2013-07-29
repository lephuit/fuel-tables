<?php namespace Table;

abstract class Cell {
    
    /**
     * Keeps the html-attributes of the cell
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * Keeps the cells added to the cell
     * 
     * @access  protected
     * @var     array
     */
    protected $_content = array();
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Create a new table-cell with the given attributes
     * 
     * @access  public
     * 
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{cell_tag}>'
     */
    public function __construct($content, array $attributes = array())
    {
        $this->_content = $content;
        $this->_attributes = $attributes;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Renders the cell's content
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-cell with cells
     */
    public function render()
    {
        return html_tag(
            $this->_cell_tag,
            $this->_attributes,
            $this->_content ? : ''
        );
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Set an attribute of the cell
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
     * @return  \Table\Cell
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
     * Get a property from the cell. Either an attribute or a cell
     * 
     * @param   string  $property   The property to get. Can be 'cell' or 'cell_N'
     *                              to return the last or N-th cell.  If it does not
     *                              match, then $property is assumed an attribute
     * @param   mixed   $default    The default value to return if no matching
     *                              attribute was found. In case $property == 'cell',
     *                              $default can be used to indicate the number of
     *                              the cell to get
     * 
     * @return  mixed   Returns the value of $property, if a cell then \Table\Cell
     */
    public function get($property, $default = null)
    {
        // Match magic properties starting with 'cell'
        if ( 0 === strpos('cell', $property) )
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
     * @return  \Table\Cell
     */
    public function add($attribute, $value, $prepend = false)
    {
        return $this->set($attribute, $value, $prepend === false ? 1 : -1);
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
     * @return  \Table\Cell
     */
    public function remove($attribute, $value)
    {
        Helpers::remove_attribute($this->_attributes, $attribute, $value);
        
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
     * @return  \Table\Cell
     */
    public function clear($attribute)
    {
        // To avoid errors, we wil
        if ( array_key_exists($attribute, $this->_attributes) )
        {
            unset($this->_attributes[$attribute]);
        }
        
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Support echoing the cell by using __toString as a wrapper for render()
     * 
     * @access  public
     * 
     * @return  string  Returns the html-string of the table-cell with cells
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
    
}
