<?php namespace Table;



class Table {
    
    
    protected static $instances = array();
    
    
    public static function forge($name = 'default')
    {
        if ( ! isset(static::$instances[$name]) )
        {
            static::$instances[$name] = new static();
        }
        
        return static::$instances[$name];
    }
    
    
    public static function instance($name = 'default')
    {
        return static::forge($name);
    }
    
    
    
    protected $attributes = array();
    
    protected $header = null;
    
    protected $footer = null;
    
    protected $body = null;
    
    
    public function __construct() {}
    
    
    public function set($attribute, $value = null)
    {
        if ( $value === null )
        {
            if ( isset($this->attributes[$attribute]) )
            {
                unset($this->attributes[$attribute]);
            }
        }
        else
        {
            $this->attributes[$attribute] = $value;
        }
        
        return $this;
    }
    
    
    public function get($attribute, $default = null)
    {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : $default;
    }
    
    
    public function set_header($cells = array(), $attributes = array())
    {
        $this->get_header()->add_row($cells, $attributes);
    }
    
    
    public function add_row($cells, $attributes = array())
    {
        $this->get_body()->add_row($cells, $attributes);
        
        return $this;
    }
    
    
    public function add_rows($rows = array())
    {
        $this->get_body()->add_rows($rows);
        
        return $this;
    }
    
    
    public function render()
    {
        $table = $this->_table_open();
        
        $table .= ( $this->header ? $this->header->render() : '' );
        
        $table .= ( $this->body ? $this->body->render() : '' );
        
        $table .= ( $this->footer ? $this->footer->render() : '' );
        
        $table .= $this->_table_close();
        
        return $table;
    }
    
    
    public function get_header()
    {
        isset($this->header) OR $this->header = new Header();
        
        return $this->header;
    }
    
    
    public function get_body()
    {
        isset($this->body) OR $this->body = new Body();
        
        return $this->body;
    }
    
    
    public function get_footer()
    {
        isset($this->footer) OR $this->footer = new Footer();
        
        return $this->footer;
    }
    
    
    
    protected function _table_open()
    {
        return '<table ' . array_to_attr($this->attributes) . '>';
    }
    
    
    protected function _table_close()
    {
        return '</table>';
    }
    
    
    
    public function __set($attribute, $value = null)
    {
        return $this->set($attribute, $value);
    }
    
    
    public function __get($attribute)
    {
        return $this->get($attribute);
    }
    
    
    public function __toString()
    {
        return $this->render();
    }
    
}
