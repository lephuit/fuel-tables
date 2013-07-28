<?php namespace Table;

class Helpers {
    
    public static function result($val)
    {
        return ( $val instanceof Closure ? $val() : $val );
    }
    
    
    /**
     * Add an attribute to the given array
     * 
     * @access  public
     * @static
     * 
     * @param   array   $array      The array to add the css-class to. Will be
     *                              changed by reference
     * @param   mixed   $value      The value or an array of values to add
     * @param   boolean $prepend    Whether to prepend the value(s) or not.
     *                              Defaults to false
     * 
     * @return  void
     */
    public static function add_attribute(array &$array, $key, $value, $prepend = false)
    {
        // Allow multiple values to add
        $value = (array) $value;
        
        // $key not yet in $array?
        if ( ! array_key_exists($key, $array) )
        {
            // Then it's easy
            $array[$key] = implode(" ", $value);
            
            return $array;
        }
        else
        {
            // Split the value of $key to an array so we can loop over it
            is_array($array[$key]) OR $array[$key] = preg_split('/\s+/', $array[$key]);
            
            // Loop over every to-add value
            foreach ( $value as $val )
            {
                // Not in array?
                if ( ! in_array($val, $array[$key]) )
                {
                    // Then prepend or append
                    $prepend ? array_unshift($array[$key], $val) : array_push($array[$key], $val);
                }
                // Otherwise
                else
                {
                    // If we need to prepend
                    if ( $prepend )
                    {
                        // Then first remove it
                        unset($array[$key][array_search($val, $array[$key])]);
                        // And prepend it
                        array_unshift($array[$key], $val);
                    }
                }
            }
        }
        
        // Implode the array we have just created
        $array[$key] = implode(' ', $array[$key]);
        
        // return $array;
    }
    
    
    //--------------------------------------------------------------------------
    
    /**
     * Remove an attribute from the given array
     * 
     * @access  public
     * @static
     * 
     * @param   array   $array      The array to remove the css-class from. Will be
     *                              changed by reference
     * @param   mixed   $value      The value or an array of values to remove
     * 
     * @return  void
     */
    public static function remove_attribute(array &$array, $key, $value)
    {
        // Allow multiple values to add
        $value = (array) $value;
        
        // $key not yet in $array?
        if ( ! isset($array[$key]) )
        {
            // That's easy, return $array
            return $array;
        }
        
        // Split the value of $key to an array so we can loop over it
        is_array($array[$key]) OR $array[$key] = preg_split('/\s+/', $array[$key]);
        
        // Loop over every value to remove
        foreach ( $value as $val )
        {
            // Search for the value, and if we found some
            if ( false !== ( $k = array_search($val, $array[$key]) ) )
            {
                // Remove it
                unset($array[$key][$k]);
            }
        }
        
        // And implode the array to a string
        $array[$key] = implode(' ', $array[$key]);
        
        // return $array;
    }
    
}
