<?php
class thc_widget_manager {
	function update_widget_instance($new_instance, $old_instance)
	{
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
			$instance['unique_id'] = thc_helper::gen_uuid();
		}

		$instance['displayMode'] = $new_instance['displayMode'];
		$instance['firstDayOfWeek'] = $new_instance['firstDayOfWeek'];
		$instance['numberOfHolidays'] = $new_instance['numberOfHolidays'];
		$instance['disableReadMore'] = $new_instance['disableReadMore'];
		
		return $instance;
	}
}
?>