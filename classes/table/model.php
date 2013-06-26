<?php namespace Table;

class Model extends Table {
    
    protected $_data;
    
    protected $_filter = array();
    
    protected $_columns = array();
    
    public function set_data($data)
    {
        $this->_data = $data;
        
        return $this;
    }
    
    public function apply($column, $callback)
    {
        // if ( ! isset($this->_filter[$column]) )
        // {
            $this->_filter[$column] = $callback;
        // }
        
        return $this;
    }
    
    
    public function get_filter($column)
    {
        return ( isset($this->_filter[$column]) ? $this->_filter[$column] : null);
    }
    
    
    public function call_filter($column)
    {
        if ( $filter = $this->get_filter($column) )
        {
            $args = func_get_args();
            
            return call_user_func_array($filter, array_splice($args, 1));
        }
        
        return false;
    }
    
    
    public function add_row()
    {
        $values = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        return $this->_group('body')->add_row($values, $attributes);
    }
    
    public function set_header()
    {
        $headers = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        $headers = $this->_prepare_headers($headers);
        
        return $this->_group('header')->add_row($headers, $attributes);
    }
    
    public function set_footer()
    {
        $values = @func_get_arg(0);
        
        $attributes = @func_get_arg(1) ? : array();
        
        return $this->_group('footer')->add_row($values, $attributes);
    }
    
    public function render()
    {
        try
        {
            $table = '<table' . $this->_render_attributes() . '>';
            
            $this->_fill_table();
            
            $table .= $this->get_header()->render();
            
            $table .= $this->get_footer()->render();
            
            $table .= $this->get_body()->render();
            
            return $table . '</table>';
        }
        catch ( \Exception $e )
        {
            return $e->getMessage();
        }
    }
    
    
    protected function _prepare_headers($headers)
    {
        if ( ! $headers )
        {
            throw new \Table\TableException('No headers provided for table');
        }
        
        $prepared = array();
        $filters  = array();
        $columns  = array();
        
        foreach ( $headers as $column => $args )
        {
            $content = '';
            $attributes = array();
            // $column = false;
            $filter = false;
            
            if ( is_numeric($column) )
            {
                $column = false;
                $header = $args;
                $attributes = array();
            }
            else
            {
                if ( is_array($args) )
                {
                    if ( count($args) == 1 )
                    {
                        $keys = array_keys($args);
                        $header = reset($keys);
                        $filter = $args[$header];
                    }
                    else
                    {
                        $header = isset($args['header']) ? $args['header'] : '';
                        $attributes = isset($args['attributes']) ? $args['attributes'] : array();
                        $filter = isset($args['filter']) ? $args['filter'] : false;
                    }
                }
                else
                {
                    $header = $args;
                }
            }
            
            $prepared[$header] = $attributes;
            $columns[]  = $column;
            $filters[$column] = $filter;
        }
        
        if ( $filters )
        {
            foreach ( $filters as $col => $filter )
            {
                $filter && $this->apply($col, $filter);
            }
        }
        
        $this->_columns = $columns;
        
        return $prepared;
    }
    
    
    protected function _fill_table()
    {
        if ( ! $this->_data )
        {
            return;
        }
        
        $body = $this->get_body();
        
        foreach ( $this->_data as $data )
        {
            // $data = $data->to_array();
            $row = array();
            
            foreach ( $this->_columns as $column )
            {
                if ( ! $column )
                {
                    $row[] = '';
                    
                    continue;
                }
                
                if ( isset($data->{$column}) )
                {
                    $value = $this->call_filter($column, $data->{$column}, $data) ? : $data->{$column};
                }
                else
                {
                    $value = $this->call_filter($column, $data) ? : '';
                }
                
                $row[] = $value;
            }
            
            $body->add_row($row);
        }
    }
    
}
