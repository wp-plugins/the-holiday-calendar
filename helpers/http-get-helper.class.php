<?php
class http_get_helper {
	function get_day()
	{
		global $wp_query;
		
		$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');
		
		return $day;
	}
	
	function get_readmore()
	{
		global $wp_query;
		
		$readmore = isset($wp_query->query_vars['readmore']) ? $wp_query->query_vars['readmore'] : '0';
		
		return $readmore;
	}
} 
?>