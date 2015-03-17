<?php
class CC_Cloud_Remote_Check {

    protected $out;

    /**
     * The URL to the secure cart66 cloud console
     *
     * @var string
     */
    protected $console;

    /**
     * The URL to cart66 over SSL
     *
     * @var string
     */
    protected $ssl;

    /**
     * The URL to cart66 over HTTP
     *
     * @var string
     */
    protected $http;

    public function __construct() {
        $this->console = 'https://manage.cart66.com';
        $this->ssl     = 'https://cart66.com';
        $this->http    = 'http://cart66.com';
    }

    public function run() {
        $out = array();
        $out[] = $this->test_get_secure_console();
        $out[] = $this->test_get_http();
        $out[] = $this->test_get_ssl();

        $results = '<ul>';
        foreach( $out as $msg ) {
            $results .= '<li>' . $msg . '</li>';
        }
        $results .= '</ul>';

        CC_Flash_Data::set( 'remote_call_test_results', $results );
    }

    public function test_get_secure_console() {
        $result = 'Failed to reach Cart66 Cloud secure console: ' . $this->console;
        $response = wp_remote_get( $this->console );

        if ( ! is_wp_error( $response ) ) {
            // Not an error, check for data received
            if( $this->received_content( $response ) ) {
                $result = 'Success: Connected to Cart66 Secure Console';
            }
        }
        else {
            $result .= ' <pre>' . print_r( $response->errors, true ) . '</pre>';
        }

        return $result;
    }

    public function test_get_ssl() {
        $result = 'Failed to reach Cart66 over SSL: ' . $this->ssl;
        $response = wp_remote_get( $this->ssl );

        if ( ! is_wp_error( $response ) ) {
            // Not an error, check for data received
            if( $this->received_content( $response ) ) {
                $result = 'Success: Connected to Cart66 over SSL';
            }
        }
        else {
            $result .= ' <pre>' . print_r( $response->errors, true ) . '</pre>';
        }

        return $result;
    }

    public function test_get_http() {
        $result = 'Failed to reach Cart66 over HTTP: ' . $this->http;
        $response = wp_remote_get( $this->http );

        if ( ! is_wp_error( $response ) ) {
            // Not an error, check for data received
            if( $this->received_content( $response ) ) {
                $result = 'Success: Connected to Cart66 over HTTP';
            }
        }
        else {
            $result .= ' <pre>' . print_r( $response->errors, true ) . '</pre>';
        }

        return $result;
    }

    /**
     * Return true if the response contains data, otherwise false
     *
     * @param array $response
     * @return boolean
     */
    protected function received_content( $response ) {
        $received_content = false;

        if ( isset( $response['response']['code'] ) && $response['response']['code'] == '200' && isset( $response['body'] ) && strlen( $response['body'] ) > 0) {
            $received_content = true;
        }

        return $received_content;
    }

}
