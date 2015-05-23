<?php
class session_helper {
	const remote_holidays_key = 'thc_remote_holidays_key';
	
	function get_remote_holidays() {
		if(array_key_exists(self::remote_holidays_key, $_SESSION)) {
			return $_SESSION[self::remote_holidays_key];
		}	
		
		return null;
	}
	
	function set_remote_holidays($holidays) {
		$_SESSION[self::remote_holidays_key] = $holidays;
	}
	
	function clear_session()
	{
		unset($_SESSION[self::remote_holidays_key]);
	}
}
?>