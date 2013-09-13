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

abstract class Cell {
    
    /**
     * Constants that define the cell-type
     */
    const BODY = 'Body';
    const HEADER = 'Header';
    const FOOTER = 'Footer';
    
    
    /**
     * Keeps the html-attributes of the cell
     * 
     * @access  protected
     * @var     array
     */
    protected $_attributes = array();
    
    /**
     * Keeps the content of the cell
     * 
     * @access  protected
     * @var     array
     */
    protected $_content = '';
    
    /**
     * Keeps the sanitizer to be applied to the content on rendering
     * 
     * @access  protected
     * @var     string
     */
    protected $_sanitizer = null;
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Forge a new table-cell with the given attributes
     * 
     * @access  public
     * 
     * @param   string  $content        The content of the cell
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{cell_tag}>'
     */
    public static function forge($content = null, array $attributes = array(), $type = Cell::BODY)
    {
        $class = 'Table\\Cell_' . ucwords($type);
        
        return new $class($content, $attributes);
    }
    
    
    
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Create a new table-cell with the given attributes
     * 
     * @access  public
     * 
     * @param   string  $content        The content of the cell
     * @param   array   $attributes     Array of attributes to set for the
     *                                  wrapping '<t{cell_tag}>'
     */
    public function __construct($content = null, array $attributes = array())
    {
        $content !== null && $this->_content = $content;
        $attributes && $this->_attributes = $attributes;
    }
    
    
    public function set_content($content = null)
    {
        $this->_content = $content;
        
        return $this;
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
        $this->_content = Helpers::result($this->_content);
        
        $content = (
            $this->_content !== null
                ? ( $this->_sanitizer
                    ? Helpers::result($sanitizer, $this->_content)
                    : $this->_content
                )
                : ''
        );
        
        return html_tag(
            $this->_cell_tag,
            $this->_attributes,
            $content
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
        // Attribute to get, so return that one (if found, otherwise $default)
        return \Arr::get($this->_attributes, $property, $default);
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
     * @param   string  $value      The value of the attribute to remove. If omitted
     *                              it will empty or unset the attribute.
     *                              Defaults to null
     * @param   boolean $purge      Whether to remove empty-value keys from the
     *                              attributes array
     * 
     * @return  \Table\Cell
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
     * @return  \Table\Cell
     */
    public function clear($attribute)
    {
        return $this->remove($attribute, null, true);
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
     *                              will be deactivated for the cell, if omitted
     *                              the filter will be 'Security::htmlentities'.
     *                              Defaults to 'Security::htmlentities'
     * 
     * @return  \Table\Cell
     */
    public function sanitize($callback = 'Security::htmlentities')
    {
        $this->_sanitizer = $callback;
        
        // For chaining
        return $this;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Check whether the cell has sanitizers defined
     * 
     * @access  public
     * 
     * @return  boolean     Returns true if there are registered sanitizers, otherwise
     *                      false
     */
    public function has_sanitizer()
    {
        return isset($this->_sanitizer);
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Get all registered sanitizers for the current cell
     * 
     * @access  public
     * 
     * @return  mixed   Returns an array of sanitizers or false if there are none
     */
    public function get_sanitizer()
    {
        return $this->has_sanitizer() ? $this->_sanitizer : false;
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
