<?php
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
/**
 * Sets plugin config option if value is different from current/default
 * @param string $p_name  option name
 * @param string $p_value value to set
 * @return void
 */
function config_set_if_needed( $p_name, $p_value ) {
    if ( $p_value != plugin_config_get( $p_name ) ) {
        plugin_config_set( $p_name, $p_value );
    }
}

$t_redirect_url = plugin_page( 'config_page', true );
layout_page_header( null, $t_redirect_url );
layout_page_begin();
config_set_if_needed( 'smsApiKey' , gpc_get_string( 'token' ) );

html_operation_successful( $t_redirect_url );
layout_page_end();