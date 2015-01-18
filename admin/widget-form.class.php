<?php
class thc_widget_form {
	function render_form($instance)
	{
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
			7: yyyy? m? d?
		*/
		$dateFormats = array('dd-mm-yy' => '0', 'dd.mm.yy' => '1', 'dd.mm.yyyy' => '2', 'dd/mm/yy' => '3', 'dd/mm/yyyy' => '4', 'mm/dd/yyyy' => '5', 'yy/mm/dd' => '6', 'yyyy? m? d?' => '7');
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
}
?>