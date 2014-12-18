<?php get_header(); ?>
	
<div class="mva7-thc-main">

<?php

	$args = array(
				'post_type'  => the_holiday_calendar::POSTTYPE,
				'meta_query' => array(
					array(
						'key'     => 'eventDate',
						'value'   => '2014-12-23',
						'compare' => '=',
					),
				),
				'posts_per_page' => 100
			);
			$query = new WP_Query( $args );	

	while( $query->have_posts() ) : $query->the_post();
?>
<h2>test: <?php echo get_the_title(); ?></h2>
<?php	
	endwhile;

?>
</div>

<?php get_footer(); ?>