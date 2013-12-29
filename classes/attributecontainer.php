<?php namespace Table;

/**
 * Part of the fuel-tables package
 *
 * @package     fuel-tables
 * @namespace   Table
 * @version     1.0-dev
 * @author      Gasoline Development Team
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2013 -- 2014 Gasoline Development Team
 * @copyright   2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

class AttributeContainer extends DataContainer {
    
    /**
     * [$attributes description]
     * @var array
     */
    protected $attributes = array();
    
    /**
     * [$meta description]
     * @var array
     */
    protected $meta = array();
    
    
    /**
     * Add an attribute value to the already defined attributes
     * 
     * @access  public
     * @param [type]  $attribute [description]
     * @param [type]  $value     [description]
     * @param boolean $prepend   [description]
     * 
     * @return  $this
     */
    public function add_attribute($attribute, $value = null, $prepend = false)
    {
        Helper::add_attribute($this->attributes, $attribute, $value, $prepend);
        
        return $this;
    }
    
    
    /**
     * Add a meta value to the already defined meta
     * 
     * @access  public
     * @param [type]  $meta    [description]
     * @param [type]  $value   [description]
     * @param boolean $prepend [description]
     * 
     * @return  this
     */
    public function add_meta($meta, $value = null, $prepend = false)
    {
        Helper::add_attribute($this->meta, $meta, $value, $prepend);
        
        return $this;
    }
    
    
    /**
     * Remove an attribute (or only attribute value) from the defined attributes
     * 
     * @access  public
     * @param  [type]  $attribute   [description]
     * @param  [type]  $value       [description]
     * @param  boolean $allow_empty [description]
     * 
     * @return [type]               [description]
     */
    public function delete_attribute($attribute, $value = null, $allow_empty = false)
    {
        Helper::remove_attribute($this->attributes, $attribute, $value, $allow_empty);
        
        return $this;
    }
    
    
    /**
     * Remove a meta (or only meta value) from the defined meta
     * 
     * @access  public
     * @param  [type]  $meta        [description]
     * @param  [type]  $value       [description]
     * @param  boolean $allow_empty [description]
     * @return [type]               [description]
     */
    public function delete_meta($meta, $value = null, $allow_empty = false)
    {
        Helper::remove_attribute($this->meta, $meta, $value, $allow_empty);
        
        return $this;
    }
    
    
    /**
     * [get_attribute description]
     * @param  [type] $attribute [description]
     * @param  [type] $default   [description]
     * @return [type]            [description]
     */
    public function get_attribute($attribute, $default = null)
    {
        return \Arr::get($this->attributes, $attribute, Helper::result($default));
    }
    
    
    /**
     * [get_attributes description]
     * @return [type] [description]
     */
    public function get_attributes()
    {
        return $this->attributes;
    }
    
    
    /**
     * [get_meta description]
     * @param  [type] $meta    [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public function get_meta($meta, $default = null)
    {
        return \Arr::get($this->meta, $meta, Helper::result($default));
    }
    
    
    /**
     * Checks whether a given attribute is set
     * 
     * @access  public
     * @param  [type]  $attribute      [description]
     * @param  [type]  $value          [description]
     * @param  boolean $case_sensitive [description]
     * 
     * @return boolean                 [description]
     */
    public function has_attribute($attribute, $value = null, $case_sensitive = false)
    {
        return Helper::has_attribute($this->attributes, $attribute, $value, $case_sensitive);
    }
    
    
    /**
     * Checks whether a given meta attribute is set
     * 
     * @access  public
     * @param  [type]  $meta           [description]
     * @param  [type]  $value          [description]
     * 
     * @param  boolean $case_sensitive [description]
     * @return boolean                 [description]
     */
    public function has_meta($meta, $value = null, $case_sensitive = false)
    {
        return Helper::has_attribute($this->meta, $meta, $value, $case_sensitive);
    }
    
    
    /**
     * [set_attribute description]
     * @param [type] $attribute [description]
     * @param [type] $value     [description]
     */
    public function set_attribute($attribute, $value = null)
    {
        if ( ! is_string($attribute) )
        {
            throw new \InvalidArgumentException('Only strings can be set as attribute');
        }
        
        $this->attributes[$attribute] = $value;
        
        return $this;
    }
    
    
    /**
     * [set_attributes description]
     * @param array $attributes [description]
     */
    public function set_attributes(array $attributes = array())
    {
        $this->attributes = $attributes;
        
        return $this;
    }
    
    
    /**
     * [set_meta description]
     * @param [type] $meta  [description]
     * @param [type] $value [description]
     */
    public function set_meta($meta, $value = null)
    {
        $this->meta[$meta] = $value;
        
        return $this;
    }
    
    
    /**
     * [set_metas description]
     * @param array $metas [description]
     */
    public function set_metas(array $metas = array())
    {
        $this->meta = $metas;
        
        return $this;
    }
    
}

/* End of file attributecontainer.php */
/* Location: ./fuel/packages/table/classes/attributecontainer.php */
