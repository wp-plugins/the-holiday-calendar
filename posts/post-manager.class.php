<?php
	class thc_post_manager {
		function save($post_id) {
			$_SESSION['thc_metabox_errors'] = '';
			$_SESSION['thc_metabox_success'] = '';
			
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
			$eventDate = sanitize_text_field( $_POST['EventDate'] );
			$eventDateEnd = sanitize_text_field( $_POST['EventDateEnd'] );
			
			if(thc_helper::validate_us_date($eventDate) && thc_helper::validate_us_date($eventDateEnd))
			{
				$format = 'm/d/Y';
				
				$eventDateTimeStamp = strtotime($eventDate);				
				$eventDateEndTimeStamp = strtotime($eventDateEnd);
				
				if($eventDateEndTimeStamp < $eventDateTimeStamp)
				{
					$_SESSION['thc_metabox_errors'] = 'The end date was before the start date!';
					
					return;
				}

				// Update the meta field.
				update_post_meta( $post_id, 'eventDate', self::convert_date_to_generic_date_format($eventDate));				
				update_post_meta( $post_id, 'eventDateEnd', self::convert_date_to_generic_date_format($eventDateEnd) );
				
				$_SESSION['thc_metabox_success'] = 'Event saved successfully!';
				$_SESSION['thc_metabox_errors'] = '';
			}
			else
			{
				$_SESSION['thc_metabox_errors'] = 'Wrong input! Please try again or your event will not be visible.';
			}
		}
		
		function convert_date_to_generic_date_format($date)
		{
			$splitted = explode( '/' , $date );
				
			$year = $splitted[2];
			$month = $splitted[0];
			$day = $splitted[1];
			
			return $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($day, 2, "0", STR_PAD_LEFT);
		}
	}
?>