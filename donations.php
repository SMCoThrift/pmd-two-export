<?php

$start_date = $args[0];
if( empty( $start_date ) )
  WP_CLI::error( '🚨 Please provide a start_date in YYYY-MM-DD format as the first argument when calling this file.' );
if( ! stristr( $start_date, '-' ) )
  WP_CLI::error( '🚨  start_date must be in YYYY-MM-DD format.' );
$start_date_array = explode( '-', $start_date );
if( 3 != count( $start_date_array ) )
  WP_CLI::error( '🚨  start_date must be in YYYY-MM-DD format.' );

$end_date = $args[1];
if( empty( $end_date ) )
  WP_CLI::error( '🚨 Please provide an end_date in YYYY-MM-DD format as the second argument when calling this file.' );
if( ! stristr( $end_date, '-' ) )
  WP_CLI::error( '🚨  end_date must be in YYYY-MM-DD format.' );
$end_date_array = explode( '-', $end_date );
if( 3 != count( $end_date_array ) )
  WP_CLI::error( '🚨  end_date must be in YYYY-MM-DD format.' );

$query_args = [
  'post_type'     => 'donation',
  'numberposts' => -1,
  'date_query'    => [
    'after'     => $start_date,
    'before'    => $end_date,
    'inclusive' => true,
  ]
];

WP_CLI::line( '$query_args = ' . print_r( $query_args, true ) );

$donations = get_posts( $query_args );
$data = [];
$data[] = [
  'post_title',
  'post_name',
  'organization',
  'org_post_name',
  'pickup_code',
  'pickup_description',
  'date',
  'post_status',
];
if( $donations ):
  foreach( $donations as $donation ){
    $org = get_post_meta( $donation->ID, 'organization', true );
    WP_CLI::line('🔔 Organization= ' . $org['post_title'] );

    $pickup_codes = wp_get_post_terms( $donation->ID, 'pickup_code', [ 'fields' => 'names' ] );
    $pickup_code = implode( ', ', $pickup_codes );

    $pickup_description = get_post_meta( $donation->ID, 'pickup_description', true );

    $data[] = [
      'post_title'          => $donation->post_title,
      'post_name'           => $donation->post_name,
      'org_post_title'      => $org['post_title'],
      'org_post_name'       => $org['post_name'],
      'pickup_code'         => $pickup_code,
      'pickup_description'  => str_replace( ["\n","\r"], "", $pickup_description ),
      'post_date'           => $donation->post_date,
      'post_status'         => $donation->post_status,
    ];
  }

  $fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/donations_' . $start_date . '-thru-' . $end_date . '.csv', 'w' );
  foreach( $data as $fields ){
    fputcsv( $fp, $fields );
  }
  fclose( $fp );
endif;
