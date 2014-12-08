<?php
/*
Plugin Name: The Holiday Calendar
Version: 1.4.4.1
Plugin URI: http://www.theholidaycalendar.com
Description: Shows the upcoming holidays.
Author: Mva7
Author URI: http://www.mva7.nl
*/

class the_holiday_calendar extends WP_Widget {
	const PLUGIN_VERSION            = '1.4.3';
	const POSTTYPE            = 'thc-events';
	var $dateError;

	// constructor
	function the_holiday_calendar() {
		parent::WP_Widget(false, $name = __('The Holiday Calendar', 'wp_widget_plugin') );
		
		add_action( 'init', array( $this, 'create_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_css' ) );
		
		if (!is_admin()) {
			wp_enqueue_script('jquery');
		}		
		
		if (!session_id())
			session_start();
	}
	
	function load_css() {
		wp_register_style( 'thc-style', plugins_url('the-holiday-calendar.css', __FILE__) );
		wp_enqueue_style( 'thc-style' );
	}
	
	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            
		add_meta_box(
			'some_meta_box_name'
			,__( 'The Holiday Calendar', 'myplugin_textdomain' )
			,array( $this, 'render_meta_box_content' )
			,self::POSTTYPE
			,'normal'
			,'high'
		);   
	}
	
	public function render_meta_box_content( $post ) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'thc_event_detail_box', 'thc_event_detail_box_nonce' );

		// Enqueue Datepicker + jQuery UI CSS
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);		
		
		// Retrieve current date for cookie
		$eventDate = get_post_meta( $post->ID, 'eventDate', true  );
		
		if($eventDate != '')
		{
			$splitted = explode( '-' , $eventDate );
			
			$year = $splitted[0];
			$month = $splitted[1];
			$day = $splitted[2];
			
			$eventDate = $month . '/' . $day . '/' . $year;
		}
		
		?>
			<table>
				<tr>
				<td>Event date (input: mm/dd/yyyy):</td>
				<td>
					<input type="text" name="EventDate" id="EventDate" value="<?php echo $eventDate; ?>" /><?php if(!empty($_SESSION['thc_metabox_errors'])) { echo ' <span style="color: red;">' . $_SESSION['thc_metabox_errors'] . '</span>'; } ?></td>
				</tr>
			</table>
			<p>Remark: the post description will be used in the next version of this plugin. (coming soon!)</p>
			
			<script>
					jQuery(document).ready(function(){
						if(jQuery.fn.datepicker)
						{
							jQuery('#EventDate').datepicker({
								dateFormat : 'mm/dd/yy'							
							});
						}
					});
			</script>
		<?php
	}
	
	public function validateDate($date, $format)
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {		
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['thc_event_detail_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['thc_event_detail_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'thc_event_detail_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;		

		/* OK, its safe for us to save the data now. */
		
		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['EventDate'] );
		
		if($this->validateDate($mydata, 'm/d/Y'))
		{
			$splitted = explode( '/' , $mydata );
			
			$year = $splitted[2];
			$month = $splitted[0];
			$day = $splitted[1];

			// Update the meta field.
			update_post_meta( $post_id, 'eventDate', $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($day, 2, "0", STR_PAD_LEFT) );		
			
			$_SESSION['thc_metabox_errors'] = '';
		}
		else
		{
			$_SESSION['thc_metabox_errors'] = 'Wrong input! Please correct or your event will not be visible.';
		}
	}
	
	function create_post_type() {
	  register_post_type( self::POSTTYPE,
		array(
		  'labels' => array(
			'name' => __( 'Events' ),
			'singular_name' => __( 'Event' )
		  ),
		  'public' => true,
		  'has_archive' => true,
		  'menu_position' => 5,
		  'menu_icon' => 'dashicons-calendar-alt'
		)
	  );
	}
	// widget form creation
	function form($instance) {
		// Check values
		if( $instance) {
			 $title = esc_attr($instance['title']);
		} else {
			 $title = '';
		}
		
		$countries = array('United States' => 'US', 'India' => 'IN', 'Japan' => 'JP', 'Brazil' => 'BR', 'Russia' => 'RU', 'Germany' => 'DE', 'United Kingdom' => 'GB', 'France' => 'FR', 'Mexico' => 'MX', 'South Korea' => 'KR');
		$selectedCountry = isset($instance['country2']) ? $instance['country2'] : 'US';
		
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
		$dateFormats = array('dd-mm-yy' => '0', 'dd.mm.yy' => '1', 'dd.mm.yyyy' => '2', 'dd/mm/yy' => '3', 'dd/mm/yyyy' => '4', 'mm/dd/yyyy' => '5', 'yy/mm/dd' => '6', 'yyyy년 m월 d일' => '7');
		$selectedDateFormat = isset($instance['dateFormat']) ? $instance['dateFormat'] : '5'; //is US (default)
		
		ksort($countries);
		
		$showPoweredBy = isset($instance['show_powered_by']) ? $instance['show_powered_by'] : '0';
		$includeThcEvents = isset($instance['includeThcEvents2']) ? $instance['includeThcEvents2'] : '1';
		$displayMode = isset($instance['displayMode']) ? $instance['displayMode'] : '0';
		$firstDayOfWeek = isset($instance['firstDayOfWeek']) ? $instance['firstDayOfWeek'] : '0';
		
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($includeThcEvents, '1'); ?> id="<?php echo $this->get_field_id('includeThcEvents2'); ?>" name="<?php echo $this->get_field_name('includeThcEvents2'); ?>" value="1" /> 
			<label for="<?php echo $this->get_field_id('includeThcEvents2'); ?>">Include holidays (with settings below)</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('country2'); ?>">Country</label>
			<select class="widefat" id="<?php echo $this->get_field_id('country2'); ?>" name="<?php echo $this->get_field_name('country2'); ?>" >
			<?php foreach($countries as $country => $iso) { ?>
			  <option <?php selected( $selectedCountry, $iso ); ?> value="<?php echo $iso; ?>"><?php echo $country; ?></option>
			<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('dateFormat'); ?>">Date format</label>
			<select class="widefat" id="<?php echo $this->get_field_id('dateFormat'); ?>" name="<?php echo $this->get_field_name('dateFormat'); ?>" >
			<?php foreach($dateFormats as $dateFormat => $code) { ?>
			  <option <?php selected( $selectedDateFormat, $code ); ?> value="<?php echo $code; ?>"><?php echo $dateFormat; ?></option>
			<?php } ?>
			</select>
		</p>
		<p>
			Display mode:&nbsp;
			<label><input class="radio" type="radio" <?php checked($displayMode, '0'); ?> id="<?php echo $this->get_field_id('displayMode'); ?>" name="<?php echo $this->get_field_name('displayMode'); ?>" value="0" />			
			List</label>&nbsp;
			<label><input class="radio" type="radio" <?php checked($displayMode, '1'); ?> id="<?php echo $this->get_field_id('displayMode'); ?>" name="<?php echo $this->get_field_name('displayMode'); ?>" value="1" />			
			Calendar</label>
		</p>
		<p>
			First day of the week:&nbsp;
			<label><input class="radio" type="radio" <?php checked($firstDayOfWeek, '0'); ?> id="<?php echo $this->get_field_id('firstDayOfWeek'); ?>" name="<?php echo $this->get_field_name('firstDayOfWeek'); ?>" value="0" />			
			Sunday</label>&nbsp;
			<label><input class="radio" type="radio" <?php checked($firstDayOfWeek, '1'); ?> id="<?php echo $this->get_field_id('firstDayOfWeek'); ?>" name="<?php echo $this->get_field_name('firstDayOfWeek'); ?>" value="1" />			
			Monday</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($showPoweredBy, '1'); ?> id="<?php echo $this->get_field_id('show_powered_by'); ?>" name="<?php echo $this->get_field_name('show_powered_by'); ?>" value="1" /> 
			<label for="<?php echo $this->get_field_id('show_powered_by'); ?>">Enable "Powered by The Holiday Calendar". Thank you!!!</label>
		</p>
		<?php
	}

	// update widget
	function update($new_instance, $old_instance) {
		  $instance = $old_instance;
		  // Fields
		  $instance['title'] = strip_tags($new_instance['title']);
		  $instance['show_powered_by'] = $new_instance['show_powered_by'];
		  $instance['country2'] = $new_instance['country2'];
		  $instance['dateFormat'] = $new_instance['dateFormat'];
		  if(isset($new_instance['includeThcEvents2']))
		  {
			$instance['includeThcEvents2'] = $new_instance['includeThcEvents2'];
		  }
		  else
		  {
			$instance['includeThcEvents2'] = '0';
		  }
		  
		  if(!array_key_exists('unique_id', $instance))
		  {
			$instance['unique_id'] = $this->gen_uuid();
		  }
		  
		  $instance['displayMode'] = $new_instance['displayMode'];
		  $instance['firstDayOfWeek'] = $new_instance['firstDayOfWeek'];
		  
		 return $instance;
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
			7: yyyy년 m월 d일
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
				return date_format($dateToFormat,"Y년 m월 d일");//return dateToFormat.ToString("yyyy년 M월 d일", CultureInfo.InvariantCulture);
		}

		throw new InvalidOperationException("Date format not supported");
	}

	// display widget
	function widget($args, $instance) {
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
	   
	   $dateFormat = isset($instance['dateFormat']) ? $instance['dateFormat'] : '5';
	   $countryIso = isset($instance['country2']) ? $instance['country2'] : 'US';
	   
	   ?>
	   <script>
	    var events = [<?php			
			$args = array(
				'post_type'  => self::POSTTYPE,
				'meta_query' => array(
					array(
						'key'     => 'eventDate',
						'value'   => date('Y') . '-' . date('m') . '-' . ($displayMode == 0 ? date('d') : '01'),
						'compare' => '>=',
					),
				),
				'orderby' => 'eventDate',
				'order' => 'ASC',
				'posts_per_page' => $displayMode == 0 ? 3 : 100
			);
			$query = new WP_Query( $args );	
			
			// The Loop
			if ( $query->have_posts() ) {
				$separator = '';
				while ( $query->have_posts() ) {
					$query->the_post();
					$eventDate = get_post_meta( $query->post->ID, 'eventDate', true );					
					$formattedDate = $this->formatDate($eventDate, $dateFormat);
					$title = get_the_title();
					
					$events[] = array($formattedDate, $title, $eventDate);
					
					echo $separator . '[\'' . $formattedDate . '\',\'' . $title . '\',\'' . $eventDate . '\']';
					$separator = ',';
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
				events = events.slice(0, 3);
				
				events.forEach(function(event) {
					output += '<div class="thc-holiday" style="display: table-row;">';
					output += '<div class="date" style="display: table-cell; padding-right: 10px;">' + event[0] + '</div><div class="name" style="display: table-cell; padding-bottom: 10px;">' + event[1] + '</div>';						
					output += '</div>';
				});
			<?php
			}
			else
			{
				//http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=1.3&amountOfHolidays=3&fromDate=2014-12-3&pluginId=3b6bfa54-8bd2-4a5c-a328-9f29d6fb5e00&url=http://wpsandbox.mva7.nl&countryIso=DE&dateFormat=2
				$url = 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=' . self::PLUGIN_VERSION . '&amountOfHolidays=15&fromDate=' . date('Y-m') . '-01&pluginId=' . $instance['unique_id'] . '&url=' . site_url() . '&countryIso=' . $countryIso . '&dateFormat=' . $dateFormat;
			
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
							
							$events[] = array($splitted[0], $splitted[1], $splitted[2]);
						}
					}
				}
				?>
				output += '<div class="widget_calendar"><?php echo $this->draw_calendar(date('n'),date('Y'), $firstDayOfWeek == 0, $events); ?></div>';
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
			var dateFormat = '<?php echo $dateFormat; ?>';
			var firstDayOfWeek = '<?php echo $firstDayOfWeek; ?>';
			

			<?php
			if($displayMode == 0)
			{
			?>
			jQuery.noConflict().ajax({
			   url: 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=1.4&amountOfHolidays=3&fromDate=' + curr_year + '-' + curr_month + '-' + curr_date + '&pluginId=' + unique_id + '&url=' + site_url + '&countryIso=' + countryIso + '&dateFormat=' + dateFormat,
			   success: function(data){	
					rows = data.split('\r\n');
					
					rows.forEach(function(entry) {								
						splitted = entry.split('=');
						if(splitted.length > 1)
						{
							var valueToPush = [splitted[0], splitted[1], splitted[2]]; // or "var valueToPush = new Object();" which is the same
							
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

	/* draws a calendar */
	function draw_calendar($month,$year,$sundayFirst, $events){	
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
				$foundEvents = $this->searchForEvents($year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($list_day, 2, "0", STR_PAD_LEFT), $events);
				
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
					
					$columnContent = '<span class="thc-highlight" title="' . $caption . '">' . $list_day . '</span>';
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

// register widget
add_action('widgets_init', create_function('', 'return register_widget("the_holiday_calendar");'));
