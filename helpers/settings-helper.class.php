<?php
class thc_settings_helper
{
	const OPTION_NAME = 'thc_settings';
	const DATE_FORMAT_KEY = 'thc_date_format';
	const HIDE_READMORE_KEY = 'thc_hide_read_more';
	
	function get_date_format()
	{
		$option = get_option( self::OPTION_NAME );
		
		return $option[self::DATE_FORMAT_KEY];
	}
	
	function get_hide_readmore()
	{
		$option = get_option( self::OPTION_NAME );
		
		return $option[self::HIDE_READMORE_KEY];
	}
}
?>