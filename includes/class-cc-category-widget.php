<?php

class CC_Category_Widget extends WP_Widget {

    public function __construct() {
        $description = __( 'Product categories in your sidebar', 'cart66' );
        $widget_ops = array( 'classname' => 'CC_Category_Widget', 'description' => $description );

        $description = __( 'Cart66 Product Categories', 'cart66' );
        $this->WP_Widget( 'CC_Category_Widget', $description, $widget_ops );
    }

    /**
     * The form in the WordPress admin for configuring the widget
     */
    public function form( $instance ) {
        $title = __( 'Products', 'cart66' );
        $instance = wp_parse_args( $instance, array( 'title' => $title ) );
        $title = esc_attr( $instance['title'] );
        $data = array(
            'widget' => $this,
            'title' => $title
        );
        $view = CC_View::get( CC_PATH . 'views/widget/html-category-admin.php', $data);
        echo $view;
    }

    /**
     * Process the widget options to be saved
     */
    public function update( $new, $instance ) {
        $instance['title'] = esc_attr( $new['title'] );
        return $instance;
    }

    /**
     * Render the content of the widget
     */
    public function widget( $args, $instance ) {
        extract($args);

        $data = array(
            'before_title'  => $before_title,
            'after_title'   => $after_title,
            'before_widget' => $before_widget,
            'after_widget'  => $after_widget,
            'title'         => $instance['title']
        );

        $args = array (
            'taxonomy' => 'product-category'
        );

        $data['categories'] = get_categories( $args );
        $view = CC_View::get(CC_PATH . 'views/widget/html-category-sidebar.php', $data);
        echo $view;
    }

}
