<?php
class thc_widget {
	function show($args, $instance) {
		extract( $args );
		// these are the widget options
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) {
		  echo $before_title . $title . $after_title;
		}

		$displayMode = isset($instance['displayMode']) ? $instance['displayMode'] : '0';	   
		$firstDayOfWeek = isset($instance['firstDayOfWeek']) ? $instance['firstDayOfWeek'] : '0';

		$includeHolidays = !isset($instance['includeThcEvents2']) || $instance['includeThcEvents2'] == '1';
		$countryIso = $includeHolidays ? (isset($instance['country2']) ? $instance['country2'] : 'US') : null;
		$numberOfHolidays = isset($instance['numberOfHolidays']) ? $instance['numberOfHolidays'] : '3';

		global $wp_query;
		$yearToShow = $displayMode == 1 && isset($wp_query->query_vars['thc-month']) ? substr($wp_query->query_vars['thc-month'], 0, 4) : date('Y');	
		$monthToShow = $displayMode == 1 && isset($wp_query->query_vars['thc-month']) ? intval(substr($wp_query->query_vars['thc-month'], 4, 2)) : date('n');
		
		echo '<div class="thc-widget-content">';
		echo '<div class="thc-holidays" style="display:table; border-collapse: collapse;">';		
		
		$args = array(
				'post_type'  => thc_constants::POSTTYPE,
				'meta_query' => array(
					array(
						'key'     => 'eventDate',
						'value'   => $yearToShow . '-' . str_pad($monthToShow, 2 , '0', STR_PAD_LEFT) . '-' . ($displayMode == 0 ? date('d') : '01'),
						'compare' => '>=',
					),
				),
				'orderby' => 'eventDate',
				'order' => 'ASC',
				'posts_per_page' => $displayMode == 0 ? $numberOfHolidays : 100
			);
			
			$query = new WP_Query( $args );	
			$events = array();
			
			// The Loop
			if ( $query->have_posts() ) {
				$separator = '';
				while ( $query->have_posts() ) {
					$query->the_post();
					
					$eventDate = get_post_meta( $query->post->ID, 'eventDate', true );
					
					request_helper::set_surpress_title_filter(true);
					request_helper::set_surpress_title_filter(false);		
					
					$url = get_permalink();					
					
					$eventDateEnd = get_post_meta( $query->post->ID, 'eventDateEnd', true);
					
					if(empty($eventDateEnd))
					{
						$eventDateEnd = $eventDate; //use $eventDate as default for backwards compatibility
					}

					$days_difference = thc_helper::get_difference_in_days($eventDate, $eventDateEnd);

					for($i = 0; $i <= $days_difference; $i++)
					{						
						$currentEventDate = date('Y-m-d', strtotime($eventDate. ' + ' . $i . ' days'));
					
						$event = new thc_event();
						
						$event->formattedDate = thc_helper::formatDate($currentEventDate);
						$event->title = get_the_title();
						$event->eventDate = $currentEventDate;
						$event->url = $url;
						$event->isExternal = false;
						
						$events[] = $event;						
					}
				}
			}
			
			/* Restore original Post Data */
			wp_reset_postdata();
			
			if($displayMode == 0)
			{
				if($includeHolidays)
				{
					$fromDate = $yearToShow . '-' . str_pad($monthToShow, 2 , '0', STR_PAD_LEFT) . '-' . date('d');
					
					$events = thc_helper::add_remote_events($events, $countryIso, $instance['unique_id'], null, $fromDate);
				}
			
				usort($events, array(self, 'sortByDate'));
				
				$events = array_slice($events, 0, $numberOfHolidays);
				
				foreach($events as $event)
				{
					if($event->isExternal)
					{
						$url = get_post_type_archive_link(thc_constants::POSTTYPE);
						$url = add_query_arg(array('date' => $event->eventDate), $url);
						$url = add_query_arg(array('country' => $countryIso), $url);
					}
					
					echo '<div class="thc-holiday" style="display: table-row;">';
					$eventTitle = '<a href="' . $url . '" title="' . $event->title . '"' . (!$event->isExternal ? 'class="customEvent"' : '') . '>' . $event->title . '</a>';
					
					echo '<div class="date" style="display: table-cell; padding-right: 10px;">' . $event->formattedDate . '</div><div class="name" style="display: table-cell; padding-bottom: 10px;">' . $eventTitle . '</div>';
					
					echo '</div>';
				}
			}
			else
			{	
				if($includeHolidays)
				{
					$events = thc_helper::add_remote_events($events, $countryIso, $instance['unique_id']);
				}
			
				echo '<div class="widget_calendar">';
				echo thc_calendar::draw_calendar($monthToShow,$yearToShow, $firstDayOfWeek == 0, $events, $countryIso);
				echo '</div>';
			}
		
		echo '</div></div>';
		if('1' == $instance['show_powered_by'] ) {
			echo '<div class="thc-widget-footer" style="clear: left;"><span class="thc-powered-by" style="clear: left;">Powered by&nbsp;</span><a href="http://www.theholidaycalendar.com/" title="The Holiday Calendar - All holidays in one overview" target="_blank">The Holiday Calendar</a></div>';
		}
		
		echo $after_widget;
	}
	
	function sortByDate($a, $b) {
		return strcasecmp($a->eventDate, $b->eventDate);
	}
}
?>