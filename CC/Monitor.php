<?php

class CC_Monitor {

  protected $_current_memberships = array();

  public function __construct() {
    $this->_current_memberships = array('basic');
  }

  public function restrict_pages($the_content) {
    global $post;
    $visitor = new CC_Visitor();
    $admin = new CC_Admin();
    $message = '';

    // Check if page may be accessed
    if(!$visitor->can_view_post($post->ID)) {
      $admin = new CC_Admin();
      if($visitor->is_logged_in()) {
        $the_content = $admin->get_option('not_included');
      }
      else {
        $the_content = $admin->get_option('login_required');
      }
    }

    return $the_content;
  }

  /**
   * Filter posts so that the post is not found at all if the visitor is not allowed to see it
   * 
   * By default, the "page" post type is not filtered. Additional post types may be added to the 
   * unfiltered list of post types using the cc_unfiltered_post_types filter. Simply create a callback 
   * function that accepts an array parameter of post type names and returns an array of post type 
   * names that should not be filtered.
   * 
   * The returned array includes posts from post types that are not filtered and posts
   * from filtered post types that the visitor is allowed to view.
   *
   * @return array The filtered list of posts 
   */
  public function filter_posts($posts) {
    $visitor = new CC_Visitor();
    $filtered_posts = array();
    $unfiltered_post_types = apply_filters('cc_unfiltered_post_types', array('page-slurp', 'page'));
    foreach($posts as $post) {
      if(in_array($post->post_type, $unfiltered_post_types)  || $visitor->can_view_post($post->ID)) {
        $filtered_posts[] = $post;
      }
    }
    return $filtered_posts;
  }

  public function filter_pages($pages) {
    // CC_Log::write('Filtering pages :: count: ' . count($pages));
    for($i=0; $i < count($pages); $i++) {
      $page = $pages[$i];
      $visitor = new CC_Visitor();
      if(!$visitor->can_view_link($page->ID)) {
        unset($pages[$i]);
      }
    }
    return $pages;
  }

  public function filter_menus($classes, $item) {
    $visitor = new CC_Visitor();
    if(!$visitor->can_view_link($item->object_id)) {
      //CC_Log::write('Filtering menus by adding ccm-hidden class to: ' . $item->object_id);
      $classes[] = 'ccm-hidden';
    }
    return $classes;
  }


  public function enqueue_css() {
    $visitor = new CC_Visitor();
    if($visitor->is_logged_in()) {
      wp_enqueue_style('cc-logged-in', CC_URL . 'resources/css/logged-in.css');
    }
    else {
      wp_enqueue_style('cc-logged-out', CC_URL . 'resources/css/logged-out.css');
    }
    wp_enqueue_style('cc-members', CC_URL . 'resources/css/cc-members.css');
  }

}

