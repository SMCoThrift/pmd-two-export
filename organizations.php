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
  $pod = pods( 'organization' );
  $pod->fetch( $org->ID );
  $skip_pickup_dates = false;
  $skip_pickup_dates = $pod->field( 'skip_pickup_dates' );
  $step_one_note = '';
  $step_one_note = $pod->field( 'step_one_note' );

  $pickup_days = get_post_meta( $org->ID, 'pickup_days', false );
  if( is_array( $pickup_days ) && array_key_exists( 0, $pickup_days ) && ! is_array( $pickup_days[0] ) )
    $pickup_days = implode( ',', $pickup_days );
  if( is_array( $pickup_days ) )
    $pickup_days = '';

  $pickup_locations = [];
  $terms = wp_get_post_terms( $org->ID, 'pickup_location' );
  if( $terms ){
    foreach( $terms as $term ){
      $pickup_locations[] = $term->slug;
    }
  }
  $donation_options = [];
  $terms = wp_get_post_terms( $org->ID, 'donation_option' );
  if( $terms ){
    foreach( $terms as $term ){
      $donation_options[] = $term->slug;
    }
  }
  $pickup_times = [];
  $terms = wp_get_post_terms( $org->ID, 'pickup_time' );
  if( $terms ){
    foreach( $terms as $term ){
      $pickup_times[] = $term->slug;
    }
  }
  $screening_questions = [];
  $terms = wp_get_post_terms( $org->ID, 'screening_question' );
  if( $terms ){
    foreach( $terms as $term ){
      $screening_questions[] = $term->slug;
    }
  }
  $realtor_ad_standard_banner = '';
  $standard_banner = get_post_meta( $org->ID, 'realtor_ad_standard_banner', true );
  if( is_array( $standard_banner ) && array_key_exists( 'guid', $standard_banner ) )
    $realtor_ad_standard_banner = $standard_banner['guid'];


  $data[ $counter ] = [
    'post_title'                  => $org->post_title,
    'post_name'                   => $org->post_name,
    'post_content'                => str_replace( [ "\r", "\n" ], '', $org->post_content ),
    'contact_emails'              => strip_tags( get_post_meta( $org->ID, 'contact_emails', true ) ),
    'website'                     => get_post_meta( $org->ID, 'website', true ),
    'priority_pickup'             => (bool) get_post_meta( $org->ID, 'priority_pickup', true ), // Will be either `1` or empty
    'donation_routing'            => get_post_meta( $org->ID, 'donation_routing', true ),
    'skip_pickup_dates'           => (bool) $skip_pickup_dates,
    'pickup_days'                 => $pickup_days,
    'minimum_scheduling_interval' => get_post_meta( $org->ID, 'minimum_scheduling_interval', true ),
    'step_one_note'               => str_replace( [ "\r", "\n" ], '', $step_one_note ),
    'provide_additional_details'  => (bool) get_post_meta( $org->ID, 'provide_additional_details', true ),
    'allow_user_photo_uploads'    => (bool) get_post_meta( $org->ID, 'allow_user_photo_uploads', true ),
    'pause_pickups'               => (bool) get_post_meta( $org->ID, 'pickups_paused', true ),
    'realtor_ad_standard_banner'  => $realtor_ad_standard_banner, // Need to handle media files
    'realtor_ad_medium_banner'    => '', // Need to handle media files
    'realtor_ad_link'             => get_post_meta( $org->ID, 'realtor_ad_link', true ),
    'realtor_description'         => '',
    'pickup_locations'            => implode( ',', $pickup_locations ), // Taxonomy options need to be available before importing
    'donation_options'            => implode( ',', $donation_options ), // Taxonomy options need to be available before importing
    'pickup_times'                => implode( ',', $pickup_times ), // Taxonomy options need to be available before importing
    'screening_questions'         => implode( ',', $screening_questions ), // Taxonomy options need to be available before importing
  ];

  // Data Validation
  foreach( $data[ $counter ] as $key => $value ){
    if( ! is_string( $value ) && ! is_bool( $value ) ){
      $print_r_value = '';
      if( is_array( $value ) )
        $print_r_value = print_r( $value, true );
      WP_CLI::error( $key . ' is of type ' . gettype( $value ) . ' (' . $data[ $counter ]['post_title'] . ') ' . $print_r_value );
    }
  }
  $counter++;
}

$fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/organizations.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );