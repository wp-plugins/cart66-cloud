<?php

class CC_Admin_Settings_Text_Area extends CC_Admin_Settings_Field {

    public $cols;
    public $rows;
    public $css_classes;

    public function __construct( $title, $key, $value='' ) {
        parent::__construct( $title, $key, $value );
        $this->cols = '50';
        $this->rows = '10';
        $this->css_classes = new CC_Stack();
    }

    public function render( $args ) {
        $this->css_classes->add( 'text-large' );
        $this->css_classes->add( 'code' );
        $field = $this->header;

        $field .= '<textarea name="' . $args['option_name'] . '[' . $this->key . ']"';
        $field .= ' id="' . $args['option_name'] . '_' . $this->key . '"';
        $field .= ' rows="' . $this->rows . '"';
        $field .= ' cols="' . $this->cols . '"';
        $field .= ' class="' . $this->css_classes . '">';
        $field .= $this->value;
        $field .= '</textarea>';

        if ( isset( $this->description ) ) {
            $field .= '<p class="description">' . $this->description . '</p>';
        }

        if ( isset( $this->footer ) ) {
            $field .= $this->footer;
        }

        echo $field;
    }

}
