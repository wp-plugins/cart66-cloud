<?php
class CC_SettingsPage {

  protected $_errors = array();
  protected $_warnings = array();
  protected $_success = array();

  public function __construct() {
    $this->clear_messages();
  }

  /**
   * Clear the error messages, warning messages, and success messages arrays
   */
  public function clear_messages() {
    $this->_errors = array();
    $this->_warnings = array();
    $this->_success = array();
  }

  public function render() {
    $templates = CC_PageSlurp::get_page_templates();

	  // Look for selected page template
	  $selected_template = CC_PageSlurp::get_selected_page_template();

	  $data = array(
	    'templates' => $templates,
	    'selected_page_template' => $selected_template,
	    'redirect_type' => get_site_option('cc_redirect_type'),
      'logging' => get_site_option('cc_logging'),
      'product_loader' => get_site_option('cc_product_loader', 'server'),
      'slurp_mode' => get_site_option('cc_page_slurp_mode', 'physical')
	  );

    $view = CC_View::get(CC_PATH . 'views/admin/main_settings.phtml', $data);
    echo $view;
  }

  public function save_settings() {
    $settings = CC_Common::scrub('cc_settings', $_POST);
    if(is_array($settings)) {
      foreach($settings as $key => $value) {
        $value = trim($value);
        CC_Log::write('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Cart66 Cloud settings $key => $value");
        $old_value = get_site_option($key);
        if($value != $old_value) {
          if(!update_site_option($key, $value)) {
            $this->_errors[] = __('Failed to save: ' . $key);
          }
        }

        if($key == 'cc_page_slurp_mode' && $value = 'physical') {
          // Create physical page slurp template page
          CC_PhysicalPageSlurp::create_template();
        }
      }

      $subdomain = CC_Library::get_subdomain(TRUE);
      if($subdomain) {
        update_site_option('cc_subdomain', $subdomain);
      }

    }

    if(count($this->_errors)) {
      throw new CC_Exception('Failed to save Cart66 Cloud settings');
    }
  }

}
