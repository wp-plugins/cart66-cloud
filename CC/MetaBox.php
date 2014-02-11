<?php

class CC_MetaBox {

  public static function add_memberships_box() {
    $admin = new CC_Admin();
    $selected_post_types = $admin->get_option('member_post_types');
    $screens = is_array($selected_post_types) ? $selected_post_types : array('post', 'page');
    $screens = apply_filters('ccm_meta_box_pages', $screens);

    foreach($screens as $screen) {
      add_meta_box(
        'ccm_membership_ids',
        __('Membership Requirements', 'cart66_memberships'),
        array(__CLASS__, 'render_memberships_box'),
        $screen,
        'side'
      );
    }
  }

  public static function render_memberships_box($post) {
    $memberships = array(
      array(
        'name' => 'Products unavailable',
        'sku' => ''
      )
    );
    if(current_user_can('edit_posts')) {
      try {
        $lib = new CC_Library();
        $memberships = $lib->get_expiring_products();
        CC_Log::write("Rendering memberships box using expiring products data: " . print_r($memberships, true));
      }
      catch(CC_Exception_API $e) {
        CC_Log::write("Failed to load memberships" . $e->getMessage());
      }
    }

    $requirements = get_post_meta($post->ID, '_ccm_required_memberships', true);
    self::prune_requirements($post->ID, $requirements, $memberships);
    $days = get_post_meta($post->ID, '_ccm_days_in', true);
    $when_logged_in = get_post_meta($post->ID, '_ccm_when_logged_in', true);
    $when_logged_out = get_post_meta($post->ID, '_ccm_when_logged_out', true);
    $post_type = get_post_type($post->ID);
    $access_denied_page_id = get_post_meta($post->ID, '_ccm_access_denied_page_id', true);

    $data = array(
      'memberships' => $memberships, 
      'requirements' => $requirements, 
      'days' => $days,
      'when_logged_in' => $when_logged_in,
      'when_logged_out' => $when_logged_out,
      'access_denied_page_id' => $access_denied_page_id,
      'post_type' => $post_type
    );

    echo CC_View::get(CC_PATH . 'views/admin/memberships_box.phtml', $data);
  }

  public static function save_membership_requirements() {
    // Don't do anything during autosaves
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }

    // Don't do anythingn if the nonce cannot be verified
    if( isset($_POST['ccm_membership_ids_nonce']) && 
      !wp_verify_nonce($_POST['ccm_membership_ids_nonce'], 'ccm_save_membership_ids')) { 
      return;
    }

    if(isset($_POST['post_ID'])) {
      $post_ID = $_POST['post_ID'];
      $membership_ids = (isset($_POST['ccm_membership_ids'])) ? $_POST['ccm_membership_ids'] : array();
      $days = (isset($_POST['_ccm_days_in'])) ? (int)$_POST['_ccm_days_in'] : 0;
      $when_logged_in = (isset($_POST['_ccm_when_logged_in'])) ? $_POST['_ccm_when_logged_in'] : '';
      $when_logged_out = (isset($_POST['_ccm_when_logged_out'])) ? $_POST['_ccm_when_logged_out'] : '';
      $access_denied_page_id = (isset($_POST['_ccm_access_denied_page_id'])) ? $_POST['_ccm_access_denied_page_id'] : '';
      update_post_meta($post_ID, '_ccm_required_memberships', $membership_ids);
      update_post_meta($post_ID, '_ccm_days_in', $days);
      update_post_meta($post_ID, '_ccm_when_logged_in', $when_logged_in);
      update_post_meta($post_ID, '_ccm_when_logged_out', $when_logged_out);
      update_post_meta($post_ID, '_ccm_access_denied_page_id', $access_denied_page_id);
    }
  }

  public static function prune_requirements($post_id, $requirements, $memberships) {
    $found_orphans = false;
    $cloud_skus = array();
    foreach($memberships as $m) {
      $cloud_skus[] = $m['sku'];
    }

    if(is_array($requirements)) {
      foreach($requirements as $key => $sku) {
        if(!in_array($sku, $cloud_skus)) {
          CC_Log::write("Pruning orphaned sku: $sku");
          unset($requirements[$key]);
          $found_orphans = true;
        }
      }
    }

    if($found_orphans) {
      if(count($requirements) == 0) {
        CC_Log::write("Deleting all membership requirements for post id: $post_id");
        delete_post_meta($post_id, '_ccm_required_memberships');
      }
      else {
        CC_Log::write("Saving pruned requirements: " . print_r($requirements, true));
        update_post_meta($post_id, '_ccm_required_memberships', $requirements);
      }
    }

  }

}
