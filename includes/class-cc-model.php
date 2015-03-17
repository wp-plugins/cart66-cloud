<?php

class CC_Model {

    /**
     * An array representation of a database table row or other internal data storage for the object
     *
     * @var array
     */
    protected $data;

    /**
     * If the data in the model has been validated the value is true.
     * If the data validation fails or the validate() function has never been called
     * the value is false. Data is assumed to be invalid until proven otherwise.
     *
     * @var boolean
     */
    protected $validated = false;

    /**
     * Construct a new model class.
     *
     * If the $data param is an associative array, then $data is set to the provided array.
     * If the $data param is an array, the $data is set to an associative array where the
     * array keys are the values in the provided array and the values are all empty strings.
     *
     * @param array $data The data array to use for storing attributes
     * @return void
     */
    public function __construct() {
        $this->data = array();
        if ( $data = func_get_arg(0) ) {
            if ( is_array( $data ) ) {
                if( cc_is_assoc( $data ) ) {
                    $this->data = $data;
                }
                else {
                    $this->data = array();
                    foreach ( $data as $key ) {
                        $this->data[ $key ] = '';
                    }
                }
            }
        }
    }

    /**
     * Set the value of one of the keys in the private $data array.
     *
     * @param string $key The key in the $data array
     * @param string $value The value to assign to the key
     * @return boolean
     */
    public function __set( $key, $value ) {
        $success = false;
        // Allow for easy customization of setting certain keys
        $override_function_name = '_set_' . $key;

        if ( method_exists( $this, $override_function_name ) ) {
            $success = $this->$override_function_name( $value );
        } elseif ( is_array( $this->data ) && array_key_exists( $key, $this->data ) ) {
            $this->data[ $key ] = $value;
            $success = true;
        }

        return $success;
    }

    /**
     * Get the value for the key from the private $data array.
     *
     * Return false if the requested key does not exist
     *
     * @param string $key The key from the $data array
     * @return mixed
     */
    public function __get( $key ) {
        $value = false;
        // Allow for easy customization of getting certain keys or faux attributes
        $override_function_name = '_get_' . $key;

        if ( method_exists( $this, $override_function_name ) ) {
            $value = $this->$override_function_name();
        } elseif ( is_array( $this->data ) && array_key_exists( $key, $this->data ) ) {
            $value = $this->data[ $key ];
        }

        return $value;
    }

    /**
     * Return true if the given $key in the private $data array is set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset( $key ) {
        return isset($this->data[ $key ]);
    }

    /**
     * Set the value of the $data array to null for the given key.
     *
     * @param string $key
     * @return void
     */
    public function __unset( $key ) {
        if ( array_key_exists( $key, $this->data ) ) {
            $this->data[ $key ] = null;
        }
    }

    /**
     * Return the private $data array
     *
     * @return mixed
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Return true if the given $key exists in the private $data array
     *
     * @param string $key
     * @return boolean
     */
    public function field_exists( $key ) {
        return array_key_exists( $key, $this->data );
    }

    /**
     * Reset all the values of the model to empty strings except for the id which is set to null
     *
     * @return void
     */
    public function clear() {
        foreach( $this->data as $key => $value ) {
            $value = ( $key == 'id' ) ? null : '';
            $this->data[ $key ] = $value;
        }
    }

    /**
     * Populate the data for the model from the given assoc array by matching the keys
     * of the private $data array with the keys in the given assoc array.
     *
     * The model is not cleared before setting the new values. Therefore, if the model
     * has an id value and the array does not contain an id key, then the original id
     * value in the model is not changed.
     *
     * @param array The source data
     * @return void
     */
    public function copy_from( array $data ) {
        foreach( $data as $key => $value ) {
            if ( $this->field_exists( $key ) ) {
                $this->$key = $value;
            }
        }
    }
}