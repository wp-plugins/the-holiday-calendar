<?php
class session_helper {
	const remote_events_key = 'thc_remote_events_keyfghfh';
	
	function get_remote_events() {
		if(array_key_exists(self::remote_events_key, $_SESSION)) {
			return $_SESSION[self::remote_events_key];
		}	
		
		return null;
	}
	
	function set_remote_events($events) {
		$_SESSION[self::remote_events_key] = $events;
	}
}
?>