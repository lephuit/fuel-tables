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

class Cell extends AttributeContainer {
    
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
     * @param  string $content    [description]
     * @param  array  $attributes [description]
     * @param  [type] $sanitizer  [description]
     * @return [type]             [description]
     */
    public static function forge($content = '', array $attributes = array(), $sanitizer = null)
    {
        return new static($content, $attributes, $sanitizer);
    }
    
    
    
    
    
    /**
     * [__construct description]
     * @param string $content    [description]
     * @param array  $attributes [description]
     * @param [type] $sanitizer  [description]
     */
    public function __construct($content = '', array $attributes = array(), $sanitizer = null)
    {
        $this->set_content($content);
        $this->set_type(Cell::BODY);
        
        $attributes && $this->set_attributes($attributes);
        $sanitizer && $this->set_sanitizer($sanitizer);
    }
    
    
    /**
     * [delete_sanitizer description]
     * @return [type] [description]
     */
    public function delete_sanitizer()
    {
        return $this->delete_meta('sanitizer');
    }
    
    
    /**
     * [has_sanitizer description]
     * @return boolean [description]
     */
    public function has_sanitizer()
    {
        return $this->has_meta('sanitizer');
    }
    
    
    /**
     * [get_content description]
     * @return [type] [description]
     */
    public function get_content()
    {
        return $this->get_meta('content');
    }
    
    
    /**
     * [get_sanitizer description]
     * @return [type] [description]
     */
    public function get_sanitizer()
    {
        return $this->get_meta('sanitizer');
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
     * [set_content description]
     * @param [type] $content [description]
     */
    public function set_content($content)
    {
        return $this->set_meta('content', $content);
    }
    
    
    /**
     * [set_sanitizer description]
     * @param [type] $sanitizer [description]
     */
    public function set_sanitizer($sanitizer)
    {
        return $this->add_meta('sanitizer', $sanitizer);
    }
    
    
    /**
     * [set_type description]
     * @param [type] $type [description]
     */
    public function set_type($type = Cell::BODY)
    {
        if ( ! in_array($type, array('HEADER' => Cell::HEADER, 'BODY' => Cell::BODY, 'FOOTER' => Cell::FOOTER)) )
        {
            throw new \InvalidArgumentException('Invalid cell type ' . $type . ' given.');
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
        return html_tag(
            $this->translate_type(),
            $this->get_attributes(),
            (
                $this->has_sanitizer()
                ?
                    Helper::result(
                        $this->get_sanitizer(),
                        $this->get_content()
                    )
                :
                    Helper::result(
                        $this->get_content()
                    )
            )
        );
    }
    
    
    /**
     * [translate_type description]
     * @return [type] [description]
     */
    protected function translate_type()
    {
        return ( $this->get_type() == Cell::HEADER ? 'th' : 'td' );
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

/* End of file cell.php */
/* Location: ./fuel/packages/table/classes/cell.php */
