<?php
class request_helper {
	const queryIsModifiedKey = 'thc_request_is_modified';
	const queryWasModifiedKey = 'thc_request_was_modified';
	
	function get_query_is_modified()
	{
		if(!array_key_exists(self::queryIsModifiedKey, $_REQUEST))
		{
			return null;
		}
		
		return $_REQUEST[self::queryIsModifiedKey];
	}
	
	function set_query_is_modified($value)
	{
		$_REQUEST[self::queryIsModifiedKey] = $value;
	}
	
	function get_query_was_modified()
	{
		if(!array_key_exists(self::queryWasModifiedKey, $_REQUEST))
		{
			return null;
		}
		
		return $_REQUEST[self::queryWasModifiedKey];
	}
	
	function set_query_was_modified($value)
	{
		$_REQUEST[self::queryWasModifiedKey] = $value;
	}
}
?>