<?php namespace Table;



abstract class Table {
    
    protected static $instances;
    
    public static function instance($name = 'default')
    {
        return static::forge($name);
    }
    
    
    public static function forge($name = 'default')
    {
        if ( ! isset(static::$instances[$name]) )
        {
            static::$instances[$name] = new static($name);
        }
        
        return static::$instances[$name];
    }
    
    
    
    
    
    abstract public function add_row();
    
    
    abstract public function set_header();
    
    
    abstract public function set_footer();
    
    
    abstract public function render();
    
    
    
    
    
    protected $_parts = array();
    
    
    protected $_attributes = array();
    
    
    public function get_header()
    {
        return $this->_group('header');
    }
    
    
    public function get_footer()
    {
        return $this->_group('footer');
    }
    
    public function get_body()
    {
        return $this->_group('body');
    }
    
    
    public function set($attribute, $value = null)
    {
        if ( $value === null && isset($this->_attributes[$attribute]) )
        {
            unset($this->_attributes[$attribute]);
            
            return $this;
        }
        
        $this->_attributes[$attribute] = $value;
        
        return $this;
    }
    
    
    public function get($attribute, $default = null)
    {
        return ( isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : $default);
    }
    
    
    
    protected function _group($part)
    {
        if ( ! isset($this->_parts[$part]) )
        {
            $class = get_called_class() . '\\' . ucwords($part) . '\\Group';
            
            $this->_parts[$part] = new $class;
        }
        
        return $this->_parts[$part];
    }
    
    
    protected function _render_attributes($attributes = null)
    {
        if ( $attributes === null )
        {
            $attributes = $this->_attributes;
        }
        
        if ( ! $attributes )
        {
            return '';
        }
        
        return ' ' . array_to_attr($attributes);
    }
    
    
    
    
    
    public function __toString()
    {
        return $this->render();
    }
    
    
    public function __set($attribute, $value = null)
    {
        return $this->set($attribute, $value);
    }
    
    
    public function __get($attribute)
    {
        return $this->get($attribute);
    }
    
}
