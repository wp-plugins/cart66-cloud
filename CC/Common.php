<?php

class CC_Common {

  public static function get_version_number() {
    $version = '0.0.0';

    if(!function_exists('get_plugin_data')) {
      require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    $plugin_data = get_plugin_data(CC_PATH . '/cart66-cloud.php');

    if(is_array($plugin_data) && isset($plugin_data['Version'])) {
      $version = $plugin_data['Version'];
    }

    return $version;
  }

  /**
   * Return true if the provided slug is part of the page request
   */
  public static function match_page_request($slug) {
		global $wp;
		global $wp_query;

    $match = false;
    if(strtolower($wp->request) == strtolower($slug) ||
      (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == $slug)
    ) { $match = true; }
    return $match;
  }

  /**
   * Return true if the provided array is an associative array.
   *
   * @param array $array The array to inspect
   * @return boolean True if array is assoc
   */
  public static function is_assoc($array) {
    return is_array($array) && !is_numeric(implode('', array_keys($array)));
  }

  public static function unavailable_product_data() {
    $product_data = array(
      array('id' => 0, 'sku' => '', 'price' => '', 'name' => 'Products Unavailable')
    );
    return $product_data;
  }

  /**
	 * Return the scrubbed value from the source array for the given key.
	 * If the given $key is not in the source array, return NULL
	 * If the source parameter is not provided, use the $_REQUEST array
	 *
	 * This function uses scrub_value() to remove the following characters:
	 * < > \ : ; `
	 *
	 * Pass in the type 'int' to cast the returned value to an integer
	 *
	 * @param string $key
	 * @param array (Optional) $source
   * @param string (Optoinal) $type (int is the only recognized type cast)
	 * @return mixed
	 */
	public static function scrub($key, $source=null, $type=null) {
	  // Set $source to $_REQUEST global if not defined
	  if(!isset($source)) {
	    $source = $_REQUEST;
	  }

    $value = null;
    if(isset($source[$key])) {
      $value = self::deep_clean($source[$key]);
    }

    if(isset($type)) {
      if($type == 'int') {
        $value = (int)$value;
      }
    }

    return $value;
  }

  public static function deep_clean(&$data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        if(is_array($value)) {
          $data[$key] = self::deep_clean($value);
        }
        else {
          $value = strip_tags($value);
          $data[$key] = self::scrub_value($value);
        }
      }
    }
    else {
      $data= strip_tags($data);
      $data = self::scrub_value($data);
    }
    return $data;
  }


  public static function strip_slashes($value) {
    if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')) {
      $value = self::strip_slashes_deep($value); 
    }
    return $value;
  }

  public static function strip_slashes_deep($value) {
    $value = is_array($value) ?  array_map(array('CC_Common', 'strip_slashes_deep'), $value) : stripslashes($value);
    return $value;
  }

  /**
   * Remove the following characters: < > \ : ; `
   */
  private static function scrub_value($value) {
    $value = preg_replace('/[<>\\\\:;`]/', '', $value);
    return $value;
  }

  /**
   * Return a random string that contains only numbers or uppercase letters or
   * for added entropy, lowercase letters and symbols.
   *
   * The default length of the string is 14 characters.
   *
   * @param int (Optional) $length The number of characters in the string. Default: 14
   * @param string (Optional) $entropy 'lower' includes lower case letters, 'symbols' includes lower case letters and symbols
   * @return string
   */
  public static function rand_string($length = 14, $entropy='none') {
    $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if($entropy == 'lower') {
      $chrs .= 'abcdefghijklmnopqrstuvwxyz';
    }
    elseif($entropy == 'symbols') {
      $chrs .= 'abcdefghijklmnopqrstuvwxyz!@#%^&*()+~:';
    }
    for($i=0; $i<$length; $i++) {
      $loc = mt_rand(0, strlen($chrs)-1);
      $string .= $chrs[$loc];
    }
    return $string;
  }

  public static function starts_with($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
  }

}
