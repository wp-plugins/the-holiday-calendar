<?php
	class thc_post_form {
		public function render_meta_box_content( $post ) {
			// Add an nonce field so we can check for it later.
			wp_nonce_field( 'thc_event_detail_box', 'thc_event_detail_box_nonce' );

			// Enqueue Datepicker + jQuery UI CSS
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);		
			
			// Retrieve current date for cookie
			$eventDate = get_post_meta( $post->ID, 'eventDate', true  );
			$eventDateEnd = get_post_meta( $post->ID, 'eventDateEnd', true  );
			
			if($eventDate != '')
			{
				$eventDate = self::convert_to_us_date($eventDate);
			}
			
			if($eventDateEnd != '')
			{
				$eventDateEnd = self::convert_to_us_date($eventDateEnd);
			}
			else
			{
				$eventDateEnd = $eventDate;
			}
			
			?>
				<table>
					<tr>
						<td>Start date (input: mm/dd/yyyy):</td>
						<td>
							<input type="text" name="EventDate" id="EventDate" value="<?php echo $eventDate; ?>" /></td>
					</tr>
					<tr>
						<td>End date (input: mm/dd/yyyy):</td>
						<td>
							<input type="text" name="EventDateEnd" id="EventDateEnd" value="<?php echo $eventDateEnd; ?>" /></td>
					</tr>
					<tr>
						<td colspan="2"><?php
							if(!empty($_SESSION['thc_metabox_errors']))
							{
								echo '<span style="color: red;">' . $_SESSION['thc_metabox_errors'] . '</span>';
								
								$_SESSION['thc_metabox_errors'] = '';
							}
							else if(!empty($_SESSION['thc_metabox_success']))
							{
								echo '<span style="color: green;">' . $_SESSION['thc_metabox_success'] . '</span>';
								$_SESSION['thc_metabox_success'] = '';
							}
							?></td>
					</tr>
				</table>
				
				<script>
						jQuery(document).ready(function(){
							if(jQuery.fn.datepicker)
							{
								jQuery('#EventDate').datepicker({
									dateFormat : 'mm/dd/yy'							
								});
								
								jQuery('#EventDateEnd').datepicker({
									dateFormat : 'mm/dd/yy'							
								});
							}
						});
				</script>
			<?php
		}
		
		function convert_to_us_date($date)
		{
			$splitted = explode( '-' , $date );
				
			$year = $splitted[0];
			$month = $splitted[1];
			$day = $splitted[2];
			
			return $month . '/' . $day . '/' . $year;
		}		
	}
?>