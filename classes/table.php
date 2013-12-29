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

class Table extends AttributeContainer {
    
    /**
     * [$instances description]
     * @var array
     */
    protected static $instances = array();
    
    
    /**
     * [forge description]
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public static function forge(array $attributes = array())
    {
        return new static($attributes);
    }
    
    
    /**
     * [instance description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function instance($name)
    {
        if ( ! array_key_exists($name, static::$instances) )
        {
            static::$instances[$name] = static::forge();
        }
        
        return static::$instances[$name];
    }
    
    
    
    
    
    /**
     * [$header description]
     * @var [type]
     */
    protected $header = null;
    
    /**
     * [$footer description]
     * @var [type]
     */
    protected $footer = null;
    
    
    
    /**
     * [__construct description]
     * @param array $attributes [description]
     */
    public function __construct(array $attributes = array())
    {
        $attributes && $this->set_attributes($attributes);
    }
    
    
    /**
     * [add_header description]
     * @param array $cells      [description]
     * @param array $attributes [description]
     */
    public function add_header($cells = array(), array $attributes = array())
    {
        foreach ( $cells as $content => $attributes )
        {
            if ( is_int($content) and is_string($attributes) )
            {
                $content    = $attributes;
                $attributes = array();
            }
            
            $this->get_header()->add_cell($content, $attributes);
        }
        
        return $this;
    }
    
    
    /**
     * [add_footer description]
     * @param array $cells      [description]
     * @param array $attributes [description]
     */
    public function add_footer($cells = array(), array $attributes = array())
    {
        foreach ( $cells as $content => $attributes )
        {
            if ( is_int($content) and is_string($attributes) )
            {
                $content    = $attributes;
                $attributes = array();
            }
            
            $this->get_footer()->add_cell($content, $attributes);
        }
        
        return $this;
    }
    
    
    /**
     * [get_header description]
     * @return [type] [description]
     */
    public function get_header()
    {
        if ( ! $this->header )
        {
            $this->header = \Table\Group::forge(array(), array(), \Table\Group::HEADER);
        }
        
        return $this->header;
    }
    
    
    /**
     * [get_footer description]
     * @return [type] [description]
     */
    public function get_footer()
    {
        if ( ! $this->footer )
        {
            $this->footer = \Table\Group::forge(array(), array(), \Table\Group::FOOTER);
        }
        
        return $this->footer;
    }
    
    
    /**
     * [set_header description]
     * @param Group $header [description]
     */
    public function set_header(Group $header)
    {
        if ( $header->get_type() != Group::HEADER )
        {
            throw new \RuntimeException('Argument 1 passed to Table\\Table::set_header() must be an instance of Table\\Group with a type of Table\\Group::HEADER');
        }
        
        $this->header = $header;
        
        return $this;
    }
    
    
    /**
     * [set_footer description]
     * @param Group $footer [description]
     */
    public function set_footer(Group $footer)
    {
        if ( $footer->get_type() != Group::FOOTER )
        {
            throw new \RuntimeException('Argument 1 passed to Table\\Table::set_footer() must be an instance of Table\\Group with a type of Table\\Group::FOOTER');
        }
        
        $this->footer = $footer;
        
        return $this;
    }
    
    
    /**
     * [render description]
     * @return [type] [description]
     */
    public function render()
    {
        try
        {
            $table = array();
            
            if ( $this->header )
            {
                $table[] = $this->header->render();
            }
            
            if ( $this->footer )
            {
                $table[] = $this->footer->render();
            }
            
            if ( $this->count_data() )
            {
                $body = array();
                
                foreach ( $this->get_data() as $row )
                {
                    $body[] = $row->render();
                }
                
                $table[] = html_tag('tbody', array(), implode(PHP_EOL, $body));
            }
            
            return html_tag('table', $this->get_attributes(), implode(PHP_EOL, $table));
        }
        catch ( \Exception $e )
        {
            return $e->getMessage();
        }
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

/* End of file table.php */
/* Location: ./fuel/packages/table/classes/table.php */
