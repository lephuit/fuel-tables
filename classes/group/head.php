<?php namespace Table;

class Group_Head extends Group {
    
    protected $_group_tag = 'thead';
    
    public function set_columns(array $columns = array())
    {
        if ( $columns && ! $this instanceof Group_Head )
        {
            throw new BadMethodCallException('Cannot set columns on table-body or table-foot');
        }
        
        if ( ! $columns )
        {
            $this->_rows = array();
            
            return $this;
        }
        
        $this->add_row();
        
        foreach ( $columns as $k => $column )
        {
            $this->add_cell( is_array($column) ? $k : $column, is_array($column) ? $column : array() );
        }
        
        return $this;
    }
    
}
