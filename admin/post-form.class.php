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
	}
?>