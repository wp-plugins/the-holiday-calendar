<?php
class thc_helper
{
	function get_remote_events_as_posts($countryIso, $dateFormat, $widgetId = NULL, $date = NULL)
	{
		$showReadMore = http_get_helper::get_readmore();
		
		global $wp_query, $wp;
		$events = self::add_remote_events(array(), $countryIso, $dateFormat, $widgetId, $date);
		
		$posts = array();
		
		foreach($events as $event)
		{
			$post = new stdClass();
			
			$content = $event[1];
			
			if($showReadMore == '1')
			{
				$content .= '<br /><br /><a href="' . $event[3] . '" target="_blank" title="' . $event[1] . ' on TheHolidayCalendar.com">Read more..</a>';
			}
			
			//$post->ID = -1;
			$post->post_author = 1;
			$post->post_date = current_time('mysql');
			$post->post_date_gmt =  current_time('mysql', $gmt = 1);
			$post->post_content = $content;
			$post->post_title = $event[1];
			$post->post_excerpt = '';
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

	function add_remote_events($events, $countryIso, $dateFormat, $widgetId = NULL, $date = NULL)
	{
		$rows = session_helper::get_remote_events();
		
		if($rows == null) {	
			$url = 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=' . thc_constants::PLUGIN_VERSION . '&amountOfHolidays=15&fromDate=' . date('Y-m') . '-01&pluginId=' . (!is_null($widgetId) ? $widgetId : '00000000-0000-0000-0000-000000000000') . '&url=' . site_url() . '&countryIso=' . $countryIso . '&dateFormat=' . $dateFormat;				
			$result = wp_remote_get($url, array('timeout' => 3));
			
			if(is_wp_error( $result ))
			{
				return $events;
			}
			
			$rows = explode("\r\n", $result['body']);		
		
			session_helper::set_remote_events($rows);
		}
		
		//echo var_dump($rows);
		foreach($rows as $row)
		{			
			if(!empty($row))
			{
				$splitted = explode('=', $row);
				
				if(is_null($date) || $date == $splitted[2])
				{
					$events[] = array($splitted[0], $splitted[1], $splitted[2], $splitted[3]);
				}
			}
		}
		
		return $events;
	}
	
	function formatDate($dateToFormat, $format)
	{
		$dateToFormat = date_create_from_format('Y-m-d', $dateToFormat);
		/*
			0: dd-mm-yy
			1: dd.mm.yy
			2: dd.mm.yyyy
			3: dd/mm/yy
			4: dd/mm/yyyy
			5: mm/dd/yyyy (US)
			6: yy/mm/dd
			7: yyyy? m? d?
		*/

		switch ($format)
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
				return date_format($dateToFormat,"Y? m? d?");//return dateToFormat.ToString("yyyy? M? d?", CultureInfo.InvariantCulture);
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
	
	function validateDate($date, $format)
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
}
?>