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

class Helper {
    
    /**
     * Evaluates the passed variable
     * 
     * @static
     * @access  public
     * @param   mixed   $val    The value to evaluate. If it's a callable "thing",
     *                          it will be evaluated with the arguments passed after
     *                          $val, otherwise it will just be returned as it is.
     * 
     * @return  mixed
     */
    public static function result($val)
    {
        return ( is_callable($val) ? call_fuel_func_array($val, @array_splice(func_get_args(), 1)) : $val );
    }
    
    
    /**
     * Add an attribute to the given array
     * 
     * @access  public
     * @static
     * 
     * @param   array   $array      The array to add the css-class to. Will be
     *                              changed by reference
     * @param   string  $attribute  Name of attribute to add value to
     * @param   mixed   $value      The value or an array of values to add
     * @param   boolean $prepend    Whether to prepend the value(s) or not.
     *                              Defaults to false
     * 
     * @return  void
     */
    public static function add_attribute(array &$array, $attribute, $value, $prepend = false)
    {
        // Allow multiple values to be added
        is_array($value) OR $value = preg_split('/\s+/', $value);
        
        // $attribute not yet in $array?
        if ( ! array_key_exists($attribute, $array) )
        {
            // Then it's easy
            $array[$attribute] = implode(' ', $value);
            
            return $array;
        }
        else
        {
            // Split the value of $attribute to an array so we can loop over it
            is_array($array[$attribute]) OR $array[$attribute] = preg_split('/\s+/', $array[$attribute]);
            
            // In case of prepending we need to reverse $value (or in other words
            // iterate over it from the last to the first value to keep the order)
            $prepend && $value = array_reverse($value);
            
            // Loop over every to-add value
            foreach ( $value as $val )
            {
                // Not in the target array?
                if ( ! in_array($val, $array[$attribute]) )
                {
                    // Then prepend or append
                    $prepend ? array_unshift($array[$attribute], $val) : array_push($array[$attribute], $val);
                }
                // Otherwise
                else
                {
                    // If we need to prepend
                    if ( $prepend )
                    {
                        // Then first remove it
                        unset($array[$attribute][array_search($val, $array[$attribute])]);
                        // And prepend it
                        array_unshift($array[$attribute], $val);
                    }
                }
            }
        }
        
        // Implode the array we have just created
        $array[$attribute] = implode(' ', $array[$attribute]);
    }
    
    
    /**
     * Remove an attribute from the given array
     * 
     * @access  public
     * @static
     * 
     * @param   array   $array      The array to remove the css-class from. Will be
     *                              changed by reference
     * @param   string  $attribute  Name of attribute to remove value from
     * @param   mixed   $value      The value or an array of values to remove
     * 
     * @return  void
     */
    public static function remove_attribute(array &$array, $attribute, $value = null, $allow_empty = false)
    {
        // Allow multiple values to add
        $value = (array) $value;
        
        // $attribute not in $array?
        if ( ! isset($array[$attribute]) )
        {
            // That's easy: void
            return;
        }
        
        // Otherwise, if no value is given we will empty or purge the target array
        if ( $value === null )
        {
            if ( $allow_empty === true )
            {
                $array[$attribute] = null;
            }
            else
            {
                unset($array[$attribute]);
            }
            
            return;
        }
        
        // Split the value of $attribute to an array so we can loop over it
        is_array($array[$attribute]) OR $array[$attribute] = preg_split('/\s+/', $array[$attribute]);
        
        // Loop over every value to remove
        foreach ( $value as $val )
        {
            // Search for the value, and if we found some
            if ( false !== ( $k = array_search($val, $array[$attribute]) ) )
            {
                // Remove it
                unset($array[$attribute][$k]);
            }
        }
        
        // If empty values are allowed
        if ( $allow_empty === true )
        {
            // Implode the array
            $array[$attribute] = implode(' ', $array[$attribute]);
            
        }
        // If empty values are not allowed
        else
        {
            // Unset the empty key
            unset($array[$attribute]);
        }
    }
    
    
    /**
     * Check whether the given attribute is inside the array
     * 
     * @access  public
     * @static
     * 
     * @param   array   $array              The array to check
     * @param   string  $attribute          The attribute to search
     * @param   string  $value              The value to find inside $array
     * @param   boolean $case_sensitive     Whether to check case_sensitive or not.
     *                                      Defaults to false
     * 
     * @return  boolean     Returns true if $value is in $array, otherwise false
     */
    public static function has_attribute($array, $attribute, $value = null, $case_sensitive = false)
    {
        // Key not in array? Then value cannot be there either
        if ( ! isset($array[$attribute]) )
        {
            return false;
        }
        
        // If no value is to be searched for, just check whether the attribute exists
        if ( is_null($value) )
        {
            return array_key_exists($attribute, $array);
        }
        
        // Split the value of $attribute to an array so we can loop over it
        is_array($array[$attribute]) OR $array[$attribute] = preg_split('/\s+/', $array[$attribute]);
        
        // Return case-sensitive or case-insensitive search
        return $case_sensitive ? in_array($value, $array[$attribute]) : in_arrayi($value, $array[$attribute]);
    }
    
    
    /**
     * Translate any array notated key to a dot-notated key
     * 
     * @access  public
     * @static
     * 
     * @param   string  $key    The key to translate from array notated to dot notated
     * 
     * @return  string  Returns the formatted string with dots as separator
     */
    public static function post_key_to_dot($key)
    {
        return trim(str_replace(array('[', ']'), array('.', ''), $key), '.');
    }
    
}

/* End of file helper.php */
/* Location: ./fuel/packages/fuel-tables/classes/helper.php */
