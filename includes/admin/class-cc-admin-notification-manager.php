<?php

class CC_Admin_Notification_Manager {

    protected $option_name;

    protected $notifications;


    public function __construct() {
        $this->option_name = 'cart66_dismissed_notifications';

        $this->notifications = get_option( $this->option_name, array() );
        if ( ! is_array( $this->notifications ) ) {
            $this->notifications = array();
        }
        // CC_Log::write( 'Loading notifications from database: ' . print_r( $this->notifications, true) );
    }

    /**
     * Return the array of dismissed notification names
     *
     * @return array
     */
    public function get_notifications() {
        return $this->notifications;
    }

    /**
     * Add the names of notifications that have been dismissed
     *
     * @var string $name The name of the notification being dismissed
     */
    public function dismiss( $name ) {
        if ( ! in_array( $name, $this->notifications ) ) {
            $this->notifications[] = $name;
            $this->save();
        }
    }

    /**
     * Remove the given notification name from the array of dismissed notifications
     *
     * @var string $name
     */
    public function clear( $name ) {
        $key = array_search( $name, $this->notifications );
        if ( false !== $key ) {
            unset( $this->notifications[ $key ] );
            $this->save();
        }
    }

    /**
     * Remove all the dismissed notifications names
     */
    public function clear_all() {
        $this->notifications = array();
        $this->save();
    }

    /**
     * Return true if the given $name has not been dismissed, otherwise false.
     * 
     * If the given name is in the $notifications array it has been dismissed and 
     * should not be displayed.
     *
     * @var string $name The name of the notification
     * @return boolean
     */
    public function show( $name ) {
        $show = true;
        if ( is_array( $this->notifications ) && in_array( $name, $this->notifications ) ) {
            $show = false;
        } 
        return $show;
    }

    public function save() {
        CC_Log::write( 'Saving admin notifications' );
        update_option( $this->option_name, $this->notifications );
    }

}
