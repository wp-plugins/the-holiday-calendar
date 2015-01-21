<?php
class http_get_helper {
	function get_day()
	{
		global $wp_query;
		
		$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');
		
		return $day;
	}
} 
?>