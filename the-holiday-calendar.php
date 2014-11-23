<?php
/*
Plugin Name: The Holiday Calendar
Version: 1.2
Plugin URI: http://www.theholidaycalendar.com
Description: Shows upcoming holidays.
Author: Mva7
Author URI: http://www.mva7.nl
*/

class the_holiday_calendar extends WP_Widget {

	// constructor
	function the_holiday_calendar() {
		parent::WP_Widget(false, $name = __('The Holiday Calendar', 'wp_widget_plugin') );
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
		$selectedCountry = $instance['country2'];
		$selectedCountry = isset($selectedCountry) ? $selectedCountry : 'US';
		
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
		$selectedDateFormat = $instance['dateFormat'];
		$selectedDateFormat = isset($selectedDateFormat) ? $selectedDateFormat : '5'; //is US (default)
		
		ksort($countries);
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
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
			<input class="checkbox" type="checkbox" <?php checked($instance['show_powered_by'], 'off'); ?> id="<?php echo $this->get_field_id('show_powered_by'); ?>" name="<?php echo $this->get_field_name('show_powered_by'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_powered_by'); ?>">Enable powered by link. Thank you!!!</label>
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
		  
		  if(!array_key_exists('unique_id', $instance))
		  {
			$instance['unique_id'] = $this->gen_uuid();
		  }
		  
		 return $instance;
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
	   
	   echo '<div id="thc-widget-content">loading..</div><br />';
	   if('on' == $instance['show_powered_by'] ) {
			echo '<div class="thc-widget-footer" style="clear: left;"><div class="thc-powered-by" style="clear: left; float: left;">Powered by&nbsp;</div><a style="float: left;" href="http://www.theholidaycalendar.com/" title="The Holiday Calendar - All holidays in one overview" target="_blank">The Holiday Calendar</a></div>';
	   }
	   ?>
	   <script>
	    var d = new Date();
		var curr_date = d.getDate();
		var curr_month = d.getMonth() + 1; //Months are zero based
		var curr_year = d.getFullYear();
		
		var unique_id = '<?php echo $instance['unique_id']; ?>';
		var site_url = '<?php echo  site_url(); ?>';
		var countryIso = '<?php echo isset($instance['country2']) ? $instance['country2'] : 'US'; ?>';
		var dateFormat = '<?php echo isset($instance['dateFormat']) ? $instance['dateFormat'] : '5'; ?>';
	
	   var $j = jQuery.noConflict();
	   $j.ajax({
		   url: 'http://www.theholidaycalendar.com/handlers/pluginData.ashx?pluginVersion=1.2&amountOfHolidays=3&fromDate=' + curr_year + '-' + curr_month + '-' + curr_date + '&pluginId=' + unique_id + '&url=' + site_url + '&countryIso=' + countryIso + '&dateFormat=' + dateFormat,
		   success: function(data){
				output = '';
				rows = data.split('\r\n');
				output += '<div class="thc-holidays" style="display:table; border-collapse: collapse;">';				
				rows.forEach(function(entry) {								
					splitted = entry.split('=');
					if(splitted.length > 1)
					{
						output += '<div class="thc-holiday" style="display: table-row;">';
						output += '<div class="date" style="display: table-cell; padding-right: 10px;">' + splitted[0] + '</div><div class="name" style="display: table-cell; padding-bottom: 10px;">' + splitted[1] + '</div>';						
						output += '</div>';
					}
				});
				
				output += '</div>';
				
				document.getElementById('thc-widget-content').innerHTML = output;
			},
		   timeout: 3000 //in milliseconds
		});
		</script>
	   <?php
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
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("the_holiday_calendar");'));