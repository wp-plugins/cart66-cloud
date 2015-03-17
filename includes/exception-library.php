<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Cart66 Exceptions
 *
 * Exception classes used with Cart66 Cloud Toolkit
 */

/**
 * Base exception class for Cart66
 */
class CC_Exception extends Exception {
  public function get_message() {
    return parent::getMessage();
  }
}


/**
 * Exceptions used for API calls
 */
class CC_Exception_API extends CC_Exception {}
class CC_Exception_API_InvalidPublicKey extends CC_Exception_API {}
class CC_Exception_API_InvalidSecretKey extends CC_Exception_API {}
class CC_Exception_API_CartNotFound extends CC_Exception_API {}

/**
 * Exceptions used for the Cart66 Store
 */
class CC_Exception_Store extends CC_Exception {}
class CC_Exception_Store_ReceiptNotFound extends CC_Exception_Store {}

/**
 * Exceptions used for Cart66 Products
 */
class CC_Exception_Product extends CC_Exception {}

/**
 * Exceptions used for Settings API problems
 */
class CC_Exception_Settings_Field_Unknown extends CC_Exception {}