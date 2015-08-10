<?php
class thc_cache_helper {
	const remote_holidays_key = 'thc_remote_holidays_key_';
	const remote_holidays_last_attempt_key = 'remote_holidays_last_attempt_key_';	
	const remote_holidays_previous_plugin_version_key = 'remote_holidays_previous_plugin_version_key';
	
	function get_remote_holidays_last_attempt($countryIso)
	{
		return get_option( remote_holidays_last_attempt_key . $countryIso );
	}
	
	function get_remote_holidays_previous_plugin_version()
	{
		return get_option( remote_holidays_previous_plugin_version_key );
	}
	
	function get_remote_holidays($countryIso)
	{
		return get_option( remote_holidays_key . $countryIso );
	}	
	
	function set_remote_holidays($plugin_holidays, $countryIso)
	{
		update_option( remote_holidays_key . $countryIso, $plugin_holidays );
	}
	
	function set_remote_holidays_last_attempt($now, $countryIso)
	{
		update_option( remote_holidays_last_attempt_key . $countryIso, $now );	
	}
	
	function set_remote_holidays_previous_plugin_version()
	{
		update_option( remote_holidays_previous_plugin_version_key, thc_constants::PLUGIN_VERSION );	
	}
	
	function clear_cache($countryIso)
	{
		update_option( remote_holidays_key . $countryIso, null );
		update_option( remote_holidays_last_attempt_key . $countryIso, null );	
		update_option( remote_holidays_previous_plugin_version_key, null );
	}
}
?>