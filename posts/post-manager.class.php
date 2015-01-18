<?php
	class thc_post_manager {
		function save($post_id) {
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
			
			if(thc_helper::validateDate($mydata, 'm/d/Y'))
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
	}
?>