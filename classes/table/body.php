<?php namespace Table;

class Body {
    
    protected $rows = array();
    
    protected $attributes = array();
    
    public function __construct() {}
    
    
    public function add_row($cells = array(), $attributes = array())
    {
        $this->rows[] = new Row($cells, $attributes);
    }
    
    
    public function add_rows($rows = array())
    {
        if ( $rows )
        {
            foreach ( $rows as $row )
            {
                $this->add_row($row);
            }
        }
    }
    
    
    public function render()
    {
        $rendered = '';
        
        if ( count($this->rows) )
        {
            $rendered = '<tbody ' . array_to_attr($this->attributes). '>' . PHP_EOL;
            
            foreach ( $this->rows as $row )
            {
                $rendered .= $row->render();
            }
            
            $rendered .= '</tbody>' . PHP_EOL;
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
