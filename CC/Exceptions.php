<?php
// Exceptions used with Cart66 Cloud Toolkit

class CC_Exception extends Exception {
  public function get_message() {
    return parent::getMessage();
  }
}

class CC_Exception_API extends CC_Exception {}
class CC_Exception_API_InvalidPublicKey extends CC_Exception_API {}
class CC_Exception_API_InvalidSecretKey extends CC_Exception_API {}
class CC_Exception_API_CartNotFound extends CC_Exception_API {}

class CC_Exception_Store extends CC_Exception {}
class CC_Exception_Store_ReceiptNotFound extends CC_Exception_Store {}

class CC_Exception_Product extends CC_Exception {}

