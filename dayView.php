<?php 
$day = isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : date('Y-m-d');
$dateFormat = isset($wp_query->query_vars['dateFormat']) ? $wp_query->query_vars['dateFormat'] : 5;
$countryIso = isset($wp_query->query_vars['country']) ? $wp_query->query_vars['country'] : 'US';
$formattedDate = ThcHelper::formatDate($day, $dateFormat);

get_header(); ?>
	
<div id="mva7-thc-main">
	<div id="mva7-thc-events">
<h1><?php echo ThcHelper::formatDate($day, $dateFormat); ?></h1>
<?php
	$args = array(
				'post_type'  => ThcConstants::POSTTYPE,
				'meta_query' => array(
					array(
						'key'     => 'eventDate',
						'value'   => $day,
						'compare' => '=',
					),
				),
				'order' => 'ASC',
				'posts_per_page' => 100
			);
			$query = new WP_Query( $args );	

		$events = array();
		while( $query->have_posts() ) : $query->the_post();		
			$events[] = array($formattedDate, get_the_title(), $day, get_the_content());	
		endwhile;
		
		$events = ThcHelper::AddRemoteEvents($events, $countryIso, $dateFormat, NULL, $day);
		
	if(!empty($events))
	{
		foreach($events as $event)
		{		
?>
			<h2><?php echo $event[1]; ?></h2>
			<p><?php echo isset($event[3]) ? $event[3] : '-'; ?></p>
<?php
		}
	}
	else
	{
?>
		<p>No events on this day!</p>
<?php
	}
?>
	<a href="<?php echo site_url(); ?>">< Homepage</a>
	</div>
</div>

<?php get_footer(); ?>