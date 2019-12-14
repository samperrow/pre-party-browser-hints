<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Send_Hints {

    public $hints = array();

    public function __construct () {
        add_action('wp_loaded', array($this, 'get_resource_hints'));
    }

    public function get_resource_hints () {
        global $wpdb;

        $opt = get_option('pprh_html_head');

        $table = $wpdb->prefix . 'pprh_table';
        $this->hints = $wpdb->get_results(
            $wpdb->prepare("SELECT url, hint_type, as_attr, type_attr, crossorigin FROM $table WHERE status = %s", 'enabled')
        );

        if ( ( ! is_array( $this->hints ) ) || count( $this->hints ) < 1 ) {
            return;
        }

        ('true' === $opt)
            ? add_action('wp_head', array($this, 'send_to_html_head'), 1, 0)
            : add_action('send_headers', array($this, 'send_in_http_header'), 1, 0);
    }

    // need to sanitize by removing anything other than link elems.
    public function send_to_html_head() {
        foreach ($this->hints as $key => $val) {
            $attrs = '';
            $attrs .= $this->add_html_attr( 'as', $val->as_attr );
            $attrs .= $this->add_html_attr( 'type', $val->type_attr );
            $attrs .= $this->add_html_attr( 'crossorigin', trim($val->crossorigin) );
            echo sprintf('<link href="%s" rel="%s"%s>', $val->url, $val->hint_type, $attrs);
        }
    }

    public function send_in_http_header() {
        $output = '';

        foreach ($this->hints as $key => $val) {
            $attrs = '';
            $attrs .= $this->add_header_attr( 'as', $val->as_attr );
            $attrs .= $this->add_header_attr( 'type', $val->type_attr );
            $attrs .= $this->add_header_attr( 'crossorigin', trim($val->crossorigin) );
            $str = sprintf('<%s>; rel=%s;%s', $val->url, $val->hint_type, $attrs );
            $str = rtrim( $str, ';' );
            $output .= $str . ', ';
        }

        $header_str = 'Link: ' . rtrim( $output, ';' );
        header( $header_str );
    }

    private function add_header_attr( $name, $val ) {
        return ( ! empty( $val ) ) ? " $name=$val" . ';' : '';
    }

    private function add_html_attr( $name, $val ) {
        return ( ! empty( $val ) ) ? " $name=\"$val\"" : '';
    }

}
