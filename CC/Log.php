<?php

if(!defined('CC_DEBUG')) {
  $logging = get_site_option('cc_logging');
  $logging = $logging == 1 ? true : false;
  define('CC_DEBUG', $logging);
}

class CC_Log {

  public static function write($data) {
    if(defined('CC_DEBUG') && CC_DEBUG) {
      $backtrace = debug_backtrace();
      $file = $backtrace[0]['file'];
      $line = $backtrace[0]['line'];
      $date = date('m/d/Y g:i:s a');
      $tz = '- Server time zone ' . date_default_timezone_get();
      $out = "CC ========== $date $tz ==========\nFile: $file" . ' :: Line: ' . $line . "\n$data";
      $dir = dirname(dirname(__FILE__));
      $filename = $dir . '/log.txt';
      if(is_writable($dir)) {
        file_put_contents($filename, $out . "\n\n", FILE_APPEND);
      }
    }
  }

}
