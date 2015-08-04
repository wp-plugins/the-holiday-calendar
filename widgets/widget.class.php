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

		echo '<div class="thc-widget-content">loading..</div>';
		if('1' == $instance['show_powered_by'] ) {
			echo '<div class="thc-widget-footer" style="clear: left;"><span class="thc-powered-by" style="clear: left;">Powered by&nbsp;</span><a href="http://www.theholidaycalendar.com/" title="The Holiday Calendar - All holidays in one overview" target="_blank">The Holiday Calendar</a></div>';
		}

		$includeHolidays = !isset($instance['includeThcEvents2']) || $instance['includeThcEvents2'] == '1';
		$countryIso = $includeHolidays ? (isset($instance['country2']) ? $instance['country2'] : 'US') : null;
		$numberOfHolidays = isset($instance['numberOfHolidays']) ? $instance['numberOfHolidays'] : '3';

		global $wp_query;
		$yearToShow = $displayMode == 1 && isset($wp_query->query_vars['thc-month']) ? substr($wp_query->query_vars['thc-month'], 0, 4) : date('Y');	
		$monthToShow = $displayMode == 1 && isset($wp_query->query_vars['thc-month']) ? intval(substr($wp_query->query_vars['thc-month'], 4, 2)) : date('n');				
		?>
		<script>

		var events = [<?php			
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
					$title = get_the_title();
					request_helper::set_surpress_title_filter(false);
					
					$url = '';
					
					if(get_the_ID() > 0)
					{
						$url = get_permalink();
					}
					
					$eventDateEnd = get_post_meta( $query->post->ID, 'eventDateEnd', true);
					
					if(empty($eventDateEnd))
					{
						$eventDateEnd = $eventDate; //use $eventDate as default for backwards compatibility
					}

					$days_difference = thc_helper::get_difference_in_days($eventDate, $eventDateEnd);

					for($i = 0; $i <= $days_difference; $i++)
					{
						$currentEventDate = date('y-m-d', (strtotime($eventDate) + ($i*60*60*24)));
						$formattedDate = thc_helper::formatDate($currentEventDate);
						$events[] = array($formattedDate, $title, $currentEventDate, '');
					
						echo $separator . '[\'' . $formattedDate . '\',\'' . $title . '\',\'' . $currentEventDate . '\',\'' . $url . '\',\'1\']';
						$separator = ',';
					}
				}
			} else {
				echo '/* no posts found */';
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		?>];

		function compare(a,b) {
			  if (a[2] < b[2])
				 return -1;
			  if (a[2] > b[2])
				return 1;
			  return 1;
			}
			
		function renderContent(viewMode){
			var output = '<div class="thc-holidays" style="display:table; border-collapse: collapse;">';
			
			<?php if($displayMode == 0)
			{
			?>
				events = events.slice(0, <?php echo $numberOfHolidays; ?>);
				
				events.forEach(function(event) {
					output += '<div class="thc-holiday" style="display: table-row;">';					
					var eventTitle = event[3] != '' ? '<a href="' + event[3] + '" title="' + event[1] + '"' + (event[4] == '1' ? 'class="customEvent"' : '') + '>' + event[1] + '</a>' : event[1];
					output += '<div class="date" style="display: table-cell; padding-right: 10px;">' + event[0] + '</div><div class="name" style="display: table-cell; padding-bottom: 10px;">' + eventTitle + '</div>';						
					output += '</div>';
				});
			<?php
			}
			else
			{
				//http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=1.3&amountOfHolidays=3&fromDate=2014-12-3&pluginId=3b6bfa54-8bd2-4a5c-a328-9f29d6fb5e00&url=http://wpsandbox.mva7.nl&countryIso=DE&dateFormat=2
				if($includeHolidays)
				{
					$events = thc_helper::add_remote_events($events, $countryIso, $instance['unique_id']);
				}

				?>
				output += '<div class="widget_calendar"><?php echo thc_calendar::draw_calendar($monthToShow,$yearToShow, $firstDayOfWeek == 0, $events, $countryIso); ?></div>';
			<?php
			}
			?>
			output += '</div>';
			
			var j = jQuery.noConflict();
			j('.thc-widget-content').html(output);
		}
			
		<?php
			if(!isset($instance['includeThcEvents2']) || $instance['includeThcEvents2'] == '1')
			{
		?>
			var d = new Date();
			var curr_date = d.getDate();
			var curr_month = d.getMonth() + 1; //Months are zero based
			var curr_year = d.getFullYear();
			
			var unique_id = '<?php echo $instance['unique_id']; ?>';
			var site_url = '<?php echo  site_url(); ?>';
			var countryIso = '<?php echo $countryIso; ?>';
			var dateFormat = '<?php echo thc_settings_helper::get_date_format(); ?>';
			var firstDayOfWeek = '<?php echo $firstDayOfWeek; ?>';
			

			<?php
			if($displayMode == 0)
			{
			?>
			jQuery.noConflict().ajax({
			   url: 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=<?php echo thc_constants::PLUGIN_VERSION; ?>&amountOfHolidays=<?php echo $numberOfHolidays; ?> &fromDate=' + curr_year + '-' + curr_month + '-' + curr_date + '&pluginId=' + unique_id + '&url=' + site_url + '&countryIso=' + countryIso + '&dateFormat=' + dateFormat,
			   success: function(data){	
					rows = data.split('\r\n');
					
					rows.forEach(function(entry) {								
						splitted = entry.split('=');
						if(splitted.length > 1)
						{
							<?php
								$url = get_post_type_archive_link(thc_constants::POSTTYPE);
								$url = add_query_arg(array('date' => 'replaceDate'), $url);
								$url = add_query_arg(array('country' => $countryIso), $url);

							?>
							var valueToPush = [splitted[0], splitted[1], splitted[2], '<?php echo $url; ?>'.replace('replaceDate', splitted[2]), '0']; // http://wpsandbox.mva7.nl/events/?date=2015-04-01&dateFormat=4&country=AU or "var valueToPush = new Object();" which is the same
							
							this.events.push(valueToPush);
						}
					});
					events.sort(compare);
					renderContent();
				},
			   timeout: 3000 //in milliseconds
			});
			<?php
			}
			else
			{
			?>
			renderContent();
		<?php
			}
		}
		else
		{
		?>
		renderContent();
		<?php
		}
		echo '</script>';
		echo '</div>';
		echo $after_widget;
	}
}
?>