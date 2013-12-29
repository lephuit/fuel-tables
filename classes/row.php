<?php namespace Table;

/**
 * Part of the fuel-tables package
 *
 * @package     fuel-tables
 * @namespace   Table
 * @version     1.0-dev
 * @author      Gasoline Development Team
 * @author      Fuel Development Team
 * @license     MIT License
 * @copyright   2013 -- 2014 Gasoline Development Team
 * @copyright   2010 - 2013 Fuel Development Team
 * @link        http://hubspace.github.io/fuel-tables
 */

class Row extends AttributeContainer {
    
    /**
     * 
     */
    const HEADER = 'HEADER';
    
    /**
     * 
     */
    const BODY = 'BODY';
    
    /**
     * 
     */
    const FOOTER = 'FOOTER';
    
    
    
    
    
    /**
     * [forge description]
     * @param  array  $cells      [description]
     * @param  array  $attributes [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public static function forge($cells = array(), array $attributes = array(), $type = Row::BODY)
    {
        return new static($cells, $attributes);
    }
    
    
    
    
    
    /**
     * [__construct description]
     * @param array  $cells      [description]
     * @param array  $attributes [description]
     * @param [type] $type       [description]
     */
    public function __construct($cells = array(), array $attributes = array(), $type = Row::BODY)
    {
        $this->set_type($type);
        
        $attributes && $this->set_attributes($attributes);
        
        if ( $cells )
        {
            foreach ( $cells as $content => $attributes )
            {
                if ( is_int($content) && ( ! is_array($attributes) ) )
                {
                    $content    = $attributes;
                    $attributes = array();
                }
                
                $this->add_cell($content, $attributes);
            }
        }
    }
    
    
    /**
     * [add_cell description]
     * @param [type] $content    [description]
     * @param array  $attributes [description]
     * @param [type] $sanitizer  [description]
     */
    public function add_cell($content, array $attributes = array(), $sanitizer = null)
    {
        return $this->set(
            null,
            (
                $content instanceof \Table\Cell
                ?
                    $content
                :
                    \Table\Cell::forge(
                        $content,
                        $attributes,
                        $sanitizer
                    )
                    ->set_type(
                        $this->get_type()
                    )
                )
        );
    }
    
    
    /**
     * [get_cell description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function get_cell($number)
    {
        if ( $number == 'first' )
        {
            return reset($this->data);
        }
        elseif ( $number == 'last' )
        {
            return end($this->data);
        }
        elseif ( (int) $number === intval($number) )
        {
            $number = $number--;
        }
        
        return $this->get($number);
    }
    
    
    /**
     * [get_type description]
     * @return [type] [description]
     */
    public function get_type()
    {
        return $this->get_meta('type');
    }
    
    
    /**
     * [set_type description]
     * @param [type] $type [description]
     */
    public function set_type($type)
    {
        if ( ! in_array($type, array('HEADER' => Row::HEADER, 'BODY' => Row::BODY, 'FOOTER' => Row::FOOTER)) )
        {
            throw new \InvalidArgumentException('Invalid row type ' . $type . ' given.');
        }
        
        $this->set_meta('type', $type);
        
        return $this;
    }
    
    
    /**
     * [render description]
     * @return [type] [description]
     */
    public function render()
    {
        $cells = '';
        
        foreach ( $this->get_data() as $cell )
        {
            $cells .= $cell->render();
        }
        
        return html_tag('tr', $this->get_attributes(), $cells);
    }
    
    
    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString()
    {
        return $this->render();
    }
    
}

/* End of file row.php */
/* Location: ./fuel/packages/table/classes/row.php */
