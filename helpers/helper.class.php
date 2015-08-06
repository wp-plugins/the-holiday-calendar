<?php
class thc_helper
{
	function get_remote_events_as_posts($countryIso, $widgetId = NULL, $date = NULL)
	{	
		$hide_readmore = thc_settings_helper::get_hide_readmore();
		
		global $wp_query, $wp;
		$events = self::add_remote_events(array(), $countryIso, $widgetId, $date);
		
		$posts = array();
		
		foreach($events as $event)
		{
			$post = new stdClass();			
			
			$teaser = $event[4];
			
			if(!empty($teaser))
			{
				$teaser = '<p>' . $teaser . '</p>';
			}
			
			if($hide_readmore == 0)
			{				
				$content = $teaser . thc_translation_helper::get_read_more_text($event);
				
				$read_more_text_id = uniqid();
				
				$read_more_texts = request_helper::get_read_more_texts();				
				$read_more_texts[$read_more_text_id] = $content;	

				request_helper::set_read_more_texts($read_more_texts);
				
				$post->post_excerpt = $content . '<!--' . thc_constants::EXCERPT_MARKER_PREFIX . $read_more_text_id . '-->';
			}
			else
			{
				$content = !empty($teaser) ? $teaser : '<p>' . $event[1] . '</p>';				
				$post->post_excerpt = '';
			}
			
			//$post->ID = -1;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt =  current_time('mysql', $gmt = 1);
			$post->post_content = $content;
			$post->post_title = $event[1];
			$post->post_status = 'publish';
			$post->ping_status = 'closed';
			$post->post_password = '';
			//$post->post_name = '?' . $_SERVER['QUERY_STRING'];
			$post->to_ping = '';
			$post->pinged = '';
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_content_filtered = '';
			$post->post_parent = 0;
			//$post->guid = get_home_url('/' . $post->post_name); // use url instead?
			$post->menu_order = 0;
			$post->post_type = thc_constants::POSTTYPE;
			$post->post_mime_type = '';
			$post->comment_status = 'closed';
			$post->comment_count = 0;
			$post->filter = 'raw';
			$post->ancestors = array(); // 3.6
			
			$posts[] = $post;
		}
		// reset wp_query properties to simulate a found page
		unset($wp_query->query['error']);
		$wp->query = array();
		$wp_query->query_vars['error'] = '';
		$wp_query->is_404 = FALSE;
		
		$wp_query->found_posts += count($events);
		$wp_query->post_count += count($events);
		
		$wp_query->posts = $posts;
		
		return $posts;
	}

	function add_remote_events($events, $countryIso, $widgetId = NULL, $date = NULL, $fromDate = '2000-01-01')
	{	
		$plugin_holidays = session_helper::get_remote_holidays();
		
		if($plugin_holidays == null) {
			$url = 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=' . thc_constants::PLUGIN_VERSION . '&amountOfHolidays=1000&fromDate=' . $fromDate . '&pluginId=' . (!is_null($widgetId) ? $widgetId : '00000000-0000-0000-0000-000000000000') . '&url=' . site_url() . '&countryIso=' . $countryIso . '&dateFormat=' . thc_settings_helper::get_date_format();				
			
			$result = wp_remote_get($url, array('timeout' => 3));
			
			if(is_wp_error( $result ))
			{
				return $events;
			}
			
			$plugin_holidays = self::convert_json_to_plugin_holidays($result['body']);
		
			session_helper::set_remote_holidays($plugin_holidays);
		}
		
		//echo var_dump($rows);
		foreach($plugin_holidays as $plugin_holiday)
		{			
			if(is_null($date) || $date == $plugin_holiday->{'date'})
			{
				$events[] = array($plugin_holiday->formattedDate, $plugin_holiday->title, $plugin_holiday->{'date'}, $plugin_holiday->url, $plugin_holiday->teaser);
			}
		}
		
		return $events;
	}
	
	function convert_json_to_plugin_holidays($json_string)
	{
		$plugin_holidays = array();
		$json_holidays = json_decode($json_string);			
		
		foreach($json_holidays as $json_holiday)
		{
			$plugin_holidays[] = thc_plugin_holiday::create_from_object( $json_holiday );
		}
		
		return $plugin_holidays;
	}
	
	function formatDate($dateToFormat)
	{
		list($year, $month, $day) = sscanf($dateToFormat, '%04d-%02d-%02d');
		$dateToFormat = new DateTime("$year-$month-$day");
		
		//$dateToFormat = date_create_from_format('Y-m-d', $dateToFormat);
		/*
			0: dd-mm-yy
			1: dd.mm.yy
			2: dd.mm.yyyy
			3: dd/mm/yy
			4: dd/mm/yyyy
			5: mm/dd/yyyy (US)
			6: yy/mm/dd
			7: yyyy년 m월 d일
		*/
		
		$dateFormat = thc_settings_helper::get_date_format();
		
		switch ($dateFormat)
		{
			case 0:
				return date_format($dateToFormat,"d-m-y");//dateToFormat.ToString("dd-MM-yy", CultureInfo.InvariantCulture);
			case 1:
				return date_format($dateToFormat,"d.m.y");//return dateToFormat.ToString("dd.MM.yy", CultureInfo.InvariantCulture);
			case 2:
				return date_format($dateToFormat,"d.m.Y");//return dateToFormat.ToString("dd.MM.yyyy", CultureInfo.InvariantCulture);
			case 3:
				return date_format($dateToFormat,"d/m/y");//return dateToFormat.ToString("dd/MM/yy", CultureInfo.InvariantCulture);
			case 4:
				return date_format($dateToFormat,"d/m/Y");//return dateToFormat.ToString("dd/MM/yyyy", CultureInfo.InvariantCulture);
			case 5:
				return date_format($dateToFormat,"n/j/Y");//return DateHelper.FormatUSDateShort(dateToFormat);
			case 6:
				return date_format($dateToFormat,"y/m/d");//return dateToFormat.ToString("yy/MM/dd", CultureInfo.InvariantCulture);
			case 7:
				return date_format($dateToFormat,"Y년 m월 d일");//return dateToFormat.ToString("yyyy년 m월 d일", CultureInfo.InvariantCulture);
		}

		throw new InvalidOperationException("Date format not supported");
	}
	
	function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	function validate_us_date($test_date) {
		$date_regex = '/^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/';
		return preg_match($date_regex, $test_date);
	}
	
	function get_difference_in_days($date1, $date2) {
		$datediff = strtotime($date2) - strtotime($date1);
		
		return floor($datediff/(60*60*24));
	}
	 
	function is_external_post()
	{
		return get_the_ID() == 0;
	}
}
?>