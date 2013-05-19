<?php namespace Table;

class Footer {
    
    protected $row = null;
    
    protected $attributes = array();
    
    public function __construct() {}
    
    
    public function render()
    {
        $rendered = '';
        
        if ( count($this->row) )
        {
            $rendered = '<thead ' . array_to_attr($this->attributes). '>' . PHP_EOL;
            
            $rendered .= $this->row->render();
            
            $rendered .= '</thead>' . PHP_EOL;
        }
        
        return $rendered;
    }
    
    
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
    
    
    public function add_row($cells = array(), $attributes = array())
    {
        $this->row = new Row($cells, $attributes);
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
