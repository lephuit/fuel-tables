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

class Group extends AttributeContainer {
    
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
     * @param  array  $rows       [description]
     * @param  array  $attributes [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    public static function forge(array $rows = array(), array $attributes = array(), $type = BODY)
    {
        return new static($rows, $attributes, $type);
    }
    
    
    
    
    
    /**
     * [__construct description]
     * @param array  $rows       [description]
     * @param array  $attributes [description]
     * @param [type] $type       [description]
     */
    public function __construct(array $rows = array(), array $attributes = array(), $type = BODY)
    {
        $this->set_type($type);
        $attributes && $this->set_attributes($attributes);
        
        foreach ( $rows as $cells => $attributes )
        {
            if ( is_int($cells) && ( ! is_array($attributes) ) )
            {
                $cells      = $attributes;
                $attributes = array();
            }
            
            $this->add_row($cells, $attributes);
        }
    }
    
    
    /**
     * [add_row description]
     * @param array $cells      [description]
     * @param array $attributes [description]
     */
    public function add_row($cells = array(), array $attributes = array())
    {
        return $this->set(
            null,
            (
                $cells instanceof \Table\Row
                ?
                    $cells
                :
                    \Table\Row::forge(
                        $cells,
                        $attributes
                    )
                    ->set_type(
                        $this->get_type()
                    )
            )
        );
    }
    
    
    /**
     * [get_row description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function get_row($number)
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
     * [add_cell description]
     * @param string $cell       [description]
     * @param array  $attributes [description]
     * @param [type] $sanitizer  [description]
     */
    public function add_cell($cell = '', array $attributes = array(), $sanitizer = null)
    {
        if ( ! $this->count_data() )
        {
            $this->add_row();
        }
        
        $this->get_row('last')
            ->set(
                null,
                (
                    $cell instanceof \Table\Cell
                    ?
                        $cell
                    :
                        \Table\Cell::forge(
                            $cell,
                            $attributes,
                            $sanitizer
                        )
                        ->set_type(
                            $this->get_type()
                        )
                )
            );
        
        return $this;
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
        return $this->set_meta('type', $type);
    }
    
    
    /**
     * [render description]
     * @return [type] [description]
     */
    public function render()
    {
        $rows = array();
        
        foreach ( $this->get_data() as $row )
        {
            $rows[] = $row->render();
        }
        
        return html_tag($this->translate_type(), $this->get_attributes(), implode(PHP_EOL, $rows));
    }
    
    
    /**
     * [translate_type description]
     * @return [type] [description]
     */
    protected function translate_type()
    {
        $type = $this->get_type();
        
        return ( $type == Group::HEADER ? 'thead' : ( $type == GROUP::FOOTER ? 'tfoot' : 'tbody' ) );
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

/* End of file group.php */
/* Location: ./fuel/packages/table/classes/group.php */
