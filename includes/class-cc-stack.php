<?php

class CC_Stack {

    /**
     * A list of items which can be printed
     *
     * All of the items in the array need to be able to be converted to strings. 
     * If they are custom objects, consider 
     * @var array
     */
    public $items;

    public function __construct( $items = array() ) {
        $this->items = $items;
    }

    /**
     * Reset the stack to an empty array
     */
    public function reset() {
        $this->items = array();
    }

    /**
     * Add an item to the end of the stack
     *
     * @var mixed $item;
     */
    public function add( $item ) {
        $this->items[] = $item;
    }

    /**
     * Remove all items from the stack that match the given item
     *
     * @var mixed $item
     */
    public function remove( $item ) {
        foreach( $this->items as $key => $value ) {
            if( $item == $value ) {
                unset( $this->items[ $key ] );
            }
        }
    }

    /**
     * Print a csv list of the items
     */
    public function to_csv() {
        return implode( ', ', $this->items );
    }


    public function __toString() {
        return implode (' ', $this->items );
    }
}
