<?php namespace Table;

class Simple extends Table {
    
    public function add_row()
    {
        $values = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        return $this->_group('body')->add_row($values, $attributes);
    }
    
    public function set_header()
    {
        $values = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        return $this->_group('header')->add_row($values, $attributes);
    }
    
    public function set_footer()
    {
        $values = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        return $this->_group('footer')->add_row($values, $attributes);
    }
    
    public function render()
    {
        $table = '<table' . $this->_render_attributes() . '>';
        
        $table .= $this->get_header()->render();
        
        $table .= $this->get_footer()->render();
        
        $table .= $this->get_body()->render();
        
        return $table . '</table>';
    }
    
}
