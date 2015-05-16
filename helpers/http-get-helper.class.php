<?php
class http_get_helper {
	function get_day()
	{
		global $wp_query;
		
		$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');
		
		return $day;
	}
	
	function get_countryIso()
	{
		global $wp_query;
		
		$countryIso = isset($wp_query->query_vars['country']) ? $wp_query->query_vars['country'] : null;
		
		return $countryIso;
	}
} 
?>