<?php
/**
 * Wrapper class for WordPress add_settings_section
 *
 * The callback for rendering the display markup is render()
 *
 * @author reality66
 * @since 2.0
 * @package CC\Admin\Settings
 */
class CC_Admin_Settings_Section {

    /**
     * The option group
     *
     * @var string
     */
    public $option_group;

    /**
     * The displayed title of the section
     *
     * @var string
     */
    public $title;

    /**
     * The description of the settings section
     *
     * @var string
     */
    public $description;

    /**
     * An array of CC_Admin_Settings_Field objects representing the fields attached to this section
     *
     * @var array
     */
    protected $fields;

    public function __construct( $title, $option_group ) {
        $this->title = $title;
        $this->option_group = $option_group;
        $this->fields = array();
    }

    /**
     *
     * The callback function referenced in add_settings_section()
     *
     * This function receives a single optional argument, which is an array with three elements.
     *   - $id
     *   - $title
     *   - $callback
     *
     * Override this function to provide a description for this section of settings.
     *
     * This function should echo its output.
     *
     * @param array $args
     * @return void
     */
    public function render( $args ) {
        echo $this->description;
    }

    /**
     * Attach a CC_Admin_Settings_Field to this section.
     *
     * @param CC_Admin_Settings_Field $field
     * @return void
     */
    public function add_field( CC_Admin_Settings_Field $field ) {
        $this->fields[] = $field;
    }

    public function clear_fields() {
        $this->fields = array();
    }

    public function add_settings_fields( $page_slug ) {

        // CC_Log::write( 'Adding all settings fields to section ' . $this->option_group . ' count: ' . count( $this->fields ) );

        foreach( $this->fields as $field ) {

            add_settings_field(
                $field->key,                           // String used in the id attribute of HTML tags
                $field->title,                         // Title of the field
                array($field, 'render'),               // Callback function to render field
                $page_slug,                            // Menu slug: 4th parameter from add_menu_page()
                $this->option_group,                   // The section of the settings page: Section ID from add_settings_section()
                array( 
                    'option_name' => $this->option_group
                )
            );
        }

    }
}
