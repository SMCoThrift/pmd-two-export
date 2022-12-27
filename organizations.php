<?php

$args = [
  'post_type'       => 'organization',
  'post_status'     => 'any',
  'posts_per_page'  => -1,
  'order'           => 'ASC',
  'orderby'         => 'title',
];

$organizations = get_posts( $args );

$counter = 0;
// Setup column headings
$column_headings = [
  0 => 'post_title',
  1 => 'post_name',
  2 => 'post_content',
  3 => 'contact_emails',
  4 => 'website',
  5 => 'priority_pickup',
  6 => 'donation_routing',
  7 => 'skip_pickup_dates',
  8 => 'pickup_days',
  9 => 'minimum_scheduling_interval',
  10  => 'step_one_note',
  11  => 'provide_additional_details',
  12  => 'allow_user_photo_uploads',
  13  => 'pause_pickups',
  14  => 'realtor_ad_standard_banner', // Need to handle media files
  15  => 'realtor_ad_medium_banner', // Need to handle media files
  16  => 'realtor_ad_link',
  17  => 'realtor_description',
  18  => 'pickup_locations', // Taxonomy options need to be available before importing
  19  => 'donation_options', // Taxonomy options need to be available before importing
  20  => 'pickup_times', // Taxonomy options need to be available before importing
  21  => 'screening_questions', // Taxonomy options need to be available before importing
];
$data[ $counter ] = $column_headings;
$counter++;

foreach( $organizations as $org ){
  $data[ $counter ] = [
    'post_title'      => $org->post_title,
    'post_name'       => $org->post_name,
    'post_content'    => $org->post_content,
    'contact_emails'  => strip_tags( get_post_meta( $org->ID, 'contact_emails', true ) ),
    'website'         => get_post_meta( $org->ID, 'website', true ),
  ];
  $counter++;
}

$fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/organizations.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );