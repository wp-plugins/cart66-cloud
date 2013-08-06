<?php

class Cart66Cloud_Loader {
  
  public static function starts_with($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
  }

  public static function class_loader($class_name) {
    if(self::starts_with($class_name, 'CC_')) {
      $path = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
      $prefix = substr($class_name, 0, 3);
      $root = CC_PATH;
      if(self::starts_with($class_name, 'CC_Exception')) {
        include $root . 'CC/Exceptions.php';
      }
      else {
        include $root . $path . '.php';
      }
    }
    elseif($class_name == 'CC') {
      include CC_PATH . 'CC/CC.php';
    }
  }
  
}

// Register autoloader
spl_autoload_register(array('Cart66Cloud_Loader', 'class_loader'));
