<?php
class thc_settings_helper
{
	const OPTION_NAME = 'thc_settings';
	const DATE_FORMAT_KEY = 'thc_date_format';
	const HIDE_READMORE_KEY = 'thc_hide_read_more';	
	const SHOW_DATE_IN_TITLE_KEY = 'thc_show_date_in_title';
	
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
	
	function get_show_date_in_title()
	{
		$option = get_option( self::OPTION_NAME );
		
		return $option[self::SHOW_DATE_IN_TITLE_KEY];
	}
}
?>