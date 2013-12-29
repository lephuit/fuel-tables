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

use ArrayAccess;
use Countable;

class DataContainer implements ArrayAccess, Countable {
    
    /**
     * Stores the data
     * 
     * @access  protected
     * @var     array()
     */
    protected $data = array();
    
    /**
     * Keeps the parent datacontainer, if set and enabled
     * 
     * @access  protected
     * @var     DataContainer
     */
    protected $parent;
    
    /**
     * Whether the parent is enabled
     * 
     * @access  protected
     * @var     boolean
     */
    protected $parent_enabled = false;
    
    /**
     * Allows datacontainers to read only
     * 
     * @access  protected
     * @var     boolean
     */
    protected $readonly = false;
    
    
    /**
     * Constructor to create a new datacontainer
     * 
     * @access  public
     * @param   array   $data       The data to set upon construction
     * @param   boolean $readonly   Boolean whether the data container is read only or not
     */
    public function __construct(array $data = array(), $readonly = false)
    {
        $this->data = $data;
        
        $this->readonly = $readonly;
    }
    
    
    /**
     * Set the parent of this data container
     * 
     * @access  public
     * @param   DataContainer   $parent     The parent to set
     * 
     * @return  DataContainer
     */
    public function set_parent(DataContainer $parent = null)
    {
        $this->parent = $parent;
        
        $this->enable_parent();
        
        return $this;
    }
    
    
    /**
     * Checks whether the data container has a parent data container assigned
     * 
     * @access  public
     * @return  boolean     Returns true if there's a parent assigned to this
     *                      data container, otherwise returns false
     */
    public function has_parent()
    {
        return $this->parent_enabled && ( $this->parent !== null );
    }
    
    
    /**
     * Enables the parent to be accessible
     * 
     * @access  public
     * 
     * @return  DataContainer
     */
    public function enable_parent()
    {
        $this->parent_enabled = true;
        
        return $this;
    }
    
    
    /**
     * Disable accessibility of the parent datacontainer
     * 
     * @access  public
     * 
     * @return  DataContainer
     */
    public function disable_parent()
    {
        $this->parent_enabled = false;
        
        return $this;
    }
    
    
    /**
     * Count the data that is set on the data container
     * 
     * @access  public
     * 
     * @return  integer     Returns the number of rows of $this->data
     */
    public function count_data()
    {
        return count($this->data);
    }
    
    
    /**
     * Check whether the data container has data set
     * 
     * @access  public
     * 
     * @return  boolean     Returns true if there's data set on the data container
     */
    public function has_data()
    {
        return ( $this->count_data() > 0 );
    }
    
    
    /**
     * Set the data even after construction
     * 
     * @access  public
     * @param   array   $data   Array of data to set
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  DataContainer
     */
    public function set_data(array $data)
    {
        if ( $this->readonly )
        {
            throw new \RuntimeException('Changing values on a read-only data container is not allowed');
        }
        
        $this->data = $data;
        
        return $this;
    }
    
    
    /**
     * Clear all the data of this container
     * 
     * @access  public
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  DataContainer
     */
    public function delete_data()
    {
        if ( $this->readonly )
        {
            throw new \RuntimeException('Changing values on a read-only data container is not allowed');
        }
        
        $this->data = array();
        
        return $this;
    }
    
    
    /**
     * Get the data of the container
     * 
     * Will merge the parent's data with this data if there's a parent container
     * 
     * @access  public
     * 
     * @return  array
     */
    public function get_data()
    {
        return ( $this->has_parent() ? \Arr::merge($this->parent->get_data(), $this->data) : $this->data );
    }
    
    
    /**
     * Set the readonly state
     * 
     * @access  public
     * @param   boolean $state  The state to set. Must be a (bool) convertible value
     * 
     * @return  DataContainer
     */
    public function readonly($state = true)
    {
        $this->readonly = (bool) $state;
        
        return $this;
    }
    
    
    /**
     * Returns the state of the containers read only status
     * 
     * @access  public
     * 
     * @return  boolean
     */
    public function is_readonly()
    {
        return $this->readonly;
    }
    
    
    /**
     * Checks whether a given key is inside the data of this container
     * 
     * @access  public
     * @param   mixed   $key    The key to check for
     * 
     * @return  boolean
     */
    public function has($key)
    {
        $result = \Arr::key_exists($this->data, $key);
        
        if ( ( false === $result ) && $this->has_parent() )
        {
            $result = $this->parent->has($key);
        }
        
        return $result;
    }
    
    
    /**
     * Get data from the container
     * 
     * @access  public
     * @param   mixed   $key        The value to return
     * @param   mixed   $default    Default value to return if $key was not found
     * 
     * @return  mixed               Returns either the value of $key if found,
     *                              otherwise the value $default
     */
    public function get($key, $default = null)
    {
        // Create a unique fail identifier
        $this_fail = uniqid('__FAIL__', true);
        
        // Grab the data from the key with $this_fail as default so we will allow
        // for values like ```null```
        $result = \Arr::get($this->data, $key, $this_fail);
        
        // No data found?
        if ( $result === $this_fail )
        {
            // Check if there's a parent
            if ( $this->has_parent() )
            {
                // Return the parent's data
                $result = $this->parent->get($key, $default);
            }
            // No parent
            else
            {
                // Evaluate the result 
                $result = Helper::result($default);
            }
        }
        
        // Return the reults
        return $result;
    }
    
    
    /**
     * Set a specific item of the data
     * 
     * @access  public
     * @param   mixed   $key    The key to set the data to
     * @param   mixed   $value  The value to set
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  DataContainer
     */
    public function set($key, $value = null)
    {
        // Read only?
        if ( $this->readonly )
        {
            // Modifying not allowed
            throw new \RuntimeException('Changing values on a read-only data container is not allowed');
        }
        
        // Setting a datacontainer?
        // if ( $value instanceof DataContainer )
        // {
        //     // Then we will set that datacontainer's parent
        //     $value->set_parent($this);
        // }
        
        // No key given?
        if ( $key === null )
        {
            // Then append
            $this->data[] = $value;
        }
        // Key given
        else
        {
            // Allow for dot-notated array keys
            \Arr::set($this->data, $key, $value);
        }
        
        // For cascading
        return $this;
    }
    
    
    /**
     * Unset data from the container
     * 
     * @access  public
     * @param   mixed   $key    The key to unset
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  boolean
     */
    public function delete($key)
    {
        if ( $this->readonly )
        {
            throw new \RuntimeException('Changing values on a read-only data container is not allowed');
        }
        
        if ( false === ( $result = \Arr::delete($this->data, $key) ) && $this->has_parent() )
        {
            $result = $this->parent->delete($key);
        }
        
        return $result;
    }
    
    
    /**
     * ArrayAccess Methods
     */
    
    /**
     * Check whether an offset is set
     * 
     * @access  public
     * 
     * @param   mixed   $offset     The offset to check
     * 
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }
    
    
    /**
     * Get an offset via array access notation
     * 
     * For example, ```echo $bag['key']``` or ```echo $bag['foo.bar']``` or simply
     * ```echo $bag[3]```
     * 
     * @access  public
     * @param   mixed   $offset The offset to get
     * 
     * @throws  OutOfBoundsException    If the data container does not have the offset
     * 
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, function() use ($offset) {
            throw new \OutOfBoundsException('Access to undefined index: ' . $offset);
        });
    }
    
    
    /**
     * Set a specific item of the data
     * 
     * @access  public
     * @param   mixed   $key    The key to set the data to
     * @param   mixed   $value  The value to set
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  DataContainer
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }
    
    
    /**
     * Unset data from the container
     * 
     * @access  public
     * @param   mixed   $key    The key to unset
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  boolean
     */
    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }
    
    
    /**
     * Count the number of data of this container
     * 
     * @access  public
     * 
     * @return  integer
     */
    public function count()
    {
        return count($this->get_data());
    }
    
    
    /**
     * Magically get data from the container
     * 
     * @access  public
     * @param   mixed   $key        The value to return
     * @param   mixed   $default    Default value to return if $key was not found
     * 
     * @return  mixed               Returns either the value of $key if found,
     *                              otherwise the value $default
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    
    /**
     * Magically checks whether a given key is inside the data of this container
     * 
     * @access  public
     * @param   mixed   $key    The key to check for
     * 
     * @return  boolean
     */
    public function __isset($key)
    {
        return $this->has($key);
    }
    
    
    /**
     * Magically set a specific item of the data
     * 
     * @access  public
     * @param   mixed   $key    The key to set the data to
     * @param   mixed   $value  The value to set
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  DataContainer
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }
    
    
    /**
     * Magically unset data from the container
     * 
     * @access  public
     * @param   mixed   $key    The key to unset
     * 
     * @throws  RuntimeException    If the data container is read only
     * 
     * @return  boolean
     */
    public function __unset($key)
    {
        return $this->delete($key);
    }
    
    
    /**
     * Magic call method to allow for easier setting and getting of data
     * 
     * @access  public
     * 
     * @param   string  $method     Magic method that's supposed to be called
     * @param   array   $arguments  Array of arguments that was passed to the method
     *                              call
     * 
     * @return  mixed               Returns the result of whatever method was
     *                              called, either a variable value (for set_*)
     *                              or boolean (for get_*)
     */
    public function __call($method, $arguments = array())
    {
        if ( preg_match('/(?<method>(s|g)et)\_(?<property>[a-zA-Z0-9]+)/', $method, $matches) )
        {
            return $this->{$matches['method']}($matches['property'], array_shift($arguments));
        }
        
        throw new \BadMethodCallException('Invalid method: ' . get_called_class() . '::' . $method);
    }
    
}

/* End of file datacontainer.php */
/* Location: ./fuel/packages/fuel-tables/classes/datacontainer.php */
