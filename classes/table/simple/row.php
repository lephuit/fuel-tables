<?php namespace Table\Simple;

class Row {
    
    protected $_row_tag = 'tr';
    
    protected $_cell_tag = 'td';
    
    protected $_cells = array();
    
    protected $_attributes = array();
    
    
    
    public function render()
    {
        if ( ! $this->_cells )
        {
            return '';
        }
        
        $row = '<' . $this->_row_tag . $this->_render_attributes() . '>';
        
        foreach ( $this->_cells as $cell )
        {
            $row .= $this->_render_cells($cell);
        }
        
        return $row . '</' . $this->_row_tag . '>';
    }
    
    
    public function __construct($cells, $attributes = array())
    {
        $this->_attributes = $attributes;
        
        $this->_cells = $this->_prepare_cells($cells);
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
    
    
    protected function _render_cells($data)
    {
        $cell = '';
        
        $cell .= '<' . $this->_cell_tag . $this->_render_attributes($data['attributes']) . '>';
        
        $cell .= $data['content'];
        
        $cell .= '</' . $this->_cell_tag . '>';
        
        return $cell;
    }
    
    
    protected function _prepare_cells($cells)
    {
        $prepared = array();
        
        // if ( ! $cells )
        // {
        //     return $prepared;
        // }
        
        foreach ( $cells as $content => $attributes )
        {
            if ( is_numeric($content) )
            {
                $content = $attributes;
                $attributes = array();
            }
            
            $prepared[] = compact('attributes', 'content');
        }
        
        return $prepared;
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
