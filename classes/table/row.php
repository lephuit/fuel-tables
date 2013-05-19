<?php namespace Table;

class Row {
    
    protected $cells = array();
    
    protected $attributes = array();
    
    public function __construct($cells = array(), $attributes = array())
    {
        $this->cells        = $cells;
        $this->attributes   = $attributes;
    }
    
    
    public function render($cell_type = 'td')
    {
        $rendered = '';
        
        if ( count($this->cells) )
        {
            $rendered = '<tr ' . array_to_attr($this->attributes). '>'. PHP_EOL;
            
            foreach ( $this->cells as $cell )
            {
                if ( is_array($cell) )
                {
                    $keys = array_keys($cell);
                    $attr = array_shift($cell);
                    $cell = $keys[0];
                    
                    $rendered .= '<' . $cell_type . ' ' . array_to_attr($attr) . '>';
                    $rendered .= $cell;
                    $rendered .= '</' . $cell_type . '>';
                }
                else
                {
                    $rendered .= '<' . $cell_type . '>';
                    $rendered .= $cell;
                    $rendered .= '</' . $cell_type . '>';
                }
            }
            
            $rendered .= '</tr>' . PHP_EOL;
        }
        
        return $rendered;
    }
    
    
    public function __toString()
    {
        return $this->render();
    }
    
}
