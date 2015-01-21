<?php
class thc_helper
{
	function get_remote_events_as_posts($countryIso, $dateFormat, $widgetId = NULL, $date = NULL)
	{
		$events = self::add_remote_events(array(), $countryIso, $dateFormat, $widgetId, $date);
		
		$posts = array();
		
		foreach($events as $event)
		{
			$post = new stdClass();
			$post->post_type = thc_constants::POSTTYPE;		
			$post->post_content = $event[1];
			$post->post_title = $event[1];
			$post->comment_status = 'closed';
			
			$posts[] = $post;
		}
		
		return $posts;
	}

	function add_remote_events($events, $countryIso, $dateFormat, $widgetId = NULL, $date = NULL)
	{
		$url = 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=' . thc_constants::PLUGIN_VERSION . '&amountOfHolidays=15&fromDate=' . date('Y-m') . '-01&pluginId=' . (!is_null($widgetId) ? $widgetId : '00000000-0000-0000-0000-000000000000') . '&url=' . site_url() . '&countryIso=' . $countryIso . '&dateFormat=' . $dateFormat;
			
		$result = wp_remote_get($url, array('timeout' => 3));
		
		if(!is_wp_error( $result ))
		{
			$rows = explode("\r\n", $result['body']);
			//echo var_dump($rows);
			foreach($rows as $row)
			{			
				if(!empty($row))
				{
					$splitted = explode('=', $row);
					
					if(is_null($date) || $date == $splitted[2])
					{
						$events[] = array($splitted[0], $splitted[1], $splitted[2]);
					}
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
				return date_format($dateToFormat,"m/d/Y");//return DateHelper.FormatUSDateShort(dateToFormat);
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