<?php
class thc_calendar {
	/* draws a calendar */
	function draw_calendar($month,$year,$sundayFirst, $events, $dateFormat, $countryIso){	
		$today = date('j');
		/* draw table */
		$calendar = '<table cellpadding="0" cellspacing="0" class="thc-calendar">';
		$calendar.= '<caption>' . date('F') . ' ' . date('Y') . '</caption>';
		
		/* table headings */
		$headings = '';	
		if($sundayFirst)
		{	
			$headings = array('S','M','T','W','T','F','S');
		}
		else
		{
			$headings = array('M','T','W','T','F','S','S');
		}
		
		$calendar.= '<thead><tr class="thc-calendar-row"><th class="thc-calendar-day-head" scope="col">'.implode('</th><th class="thc-calendar-day-head" scope="col">',$headings).'</th></tr></thead>';

		/* days and weeks vars now ... */
		$running_day = -1;
		if($sundayFirst)
		{
			$running_day = date('w',mktime(0,0,0,$month,1,$year));
		}
		else
		{
			$running_day = date('N',mktime(0,0,0,$month,1,$year)) - 1;
		}
		
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		$days_in_this_week = 1;
		$day_counter = 0;
		$dates_array = array();

		/* row for week one */
		$calendar.= '<tr class="thc-calendar-row">';

		/* print "blank" days until the first of the current week */
		for($x = 0; $x < $running_day; $x++):
			$calendar.= '<td class="thc-calendar-day-np"> </td>';
			$days_in_this_week++;
		endfor;

		/* keep going with days.... */
		for($list_day = 1; $list_day <= $days_in_month; $list_day++):
			$highlightCode = $list_day == $today ? ' thc-today' : '';
			$calendar.= '<td class="thc-calendar-day' . $highlightCode . '">';
				/* add in the day number */
				
				/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
				$currentDate = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($list_day, 2, "0", STR_PAD_LEFT);
				$foundEvents = self::searchForEvents($currentDate, $events);
				
				$columnContent = '';
				$numberOfEvents = count($foundEvents);
				if($numberOfEvents > 0)
				{
					$caption = '';
					$separator = $numberOfEvents > 1 ? '- ' : '';
					foreach($foundEvents as $foundEvent)
					{
						$caption.= $separator . addslashes($events[$foundEvent][1]);
						$separator = '\r\n' . $separator;
					}
					
					$url = get_post_type_archive_link(thc_constants::POSTTYPE);
					$url = add_query_arg(array('date' => $currentDate), $url);
					$url = add_query_arg(array('dateFormat' => $dateFormat), $url);
					$url = add_query_arg(array('country' => $countryIso), $url);
					
					$columnContent = '<a class="thc-highlight" title="' . $caption . '" href="' . $url . '">' . $list_day . '</a>';
				}
				else
				{
					$columnContent = $list_day;
				}
				
				$calendar.= '<div class="thc-day-number">'.$columnContent.'</div>';				
			$calendar.= '</td>';
			if($running_day == 6):
				$calendar.= '</tr>';
				if(($day_counter+1) != $days_in_month):
					$calendar.= '<tr class="thc-calendar-row">';
				endif;
				$running_day = -1;
				$days_in_this_week = 0;
			endif;
			$days_in_this_week++; $running_day++; $day_counter++;
		endfor;

		/* finish the rest of the days in the week */
		if($days_in_this_week < 8):
			for($x = 1; $x <= (8 - $days_in_this_week); $x++):
				$calendar.= '<td class="thc-calendar-day-np"> </td>';
			endfor;
		endif;

		/* final row */
		$calendar.= '</tr>';

		/* end the table */
		$calendar.= '</table>';
		
		/* all done, return result */
		return $calendar;
	}

	function searchForEvents($date, $array) {
	   $events = array();
	   foreach ($array as $key => $val) {
		   if ($val[2] == $date) {
			   $events[] = $key;
		   }
	   }
	   return $events;
	}
}
?>