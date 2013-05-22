<?php namespace Table\Model;

class Group {
    
    // protected $_group_tag;
    
    protected $_rows = array();
    
    protected $_attributes = array();
    
    public function render()
    {
        if ( ! $this->_rows )
        {
            return '';
        }
        
        $group = '<' . $this->_group_tag . $this->_render_attributes() . '>';
        
        foreach ( $this->_rows as $row )
        {
            $group .= $row->render();
        }
        
        return $group . '</' . $this->_group_tag . '>';
    }
    
    public function add_row($values, $attributes = array())
    {
        $class = str_replace('Group', 'Row', get_called_class());
        $this->_rows[] = new $class($values, $attributes);
        
        return $this;
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
    
    
    protected function _render_attributes()
    {
        if ( ! $this->_attributes )
        {
            return '';
        }
        
        return ' ' . array_to_attr($this->_attributes);
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
