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
  'post_type'   => 'donation',
  'numberposts' => -1,
  'post_status' => 'publish',
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
  'post_date',
  'post_status',
  'donor_name',
  'donor_company',
  'donor_address',
  'donor_city',
  'donor_state',
  'donor_zip',
  'donor_phone',
  'donor_email',
  'referrer',
];
if( $donations ):
  foreach( $donations as $donation ){
    WP_CLI::line("\n");
    $org_post_title = '';
    $org_post_name = '';

    $org = get_post_meta( $donation->ID, 'organization', true );
    if( is_string( $org ) ){
      WP_CLI::line( '🔔🔔🔔 $org is a string = `' . $org . '`. Skipping to next row.' );
    } else {
      $org_post_title = ( array_key_exists( 'post_title', $org ) )? $org['post_title'] : '' ;
      $org_post_name = ( array_key_exists( 'post_name', $org ) )? $org['post_name'] : '' ;

      $pickup_codes = wp_get_post_terms( $donation->ID, 'pickup_code', [ 'fields' => 'names' ] );
      $pickup_code = implode( ', ', $pickup_codes );

      $pickup_description = get_post_meta( $donation->ID, 'pickup_description', true );

      $custom_fields = get_post_custom( $donation->ID );
      $donor_company = ( ! isset( $custom_fields['donor_company'][0] ) )? '' : $custom_fields['donor_company'][0];
      $DonationAddress = ( empty( $custom_fields['pickup_address'][0] ) )? $custom_fields['donor_address'][0] : $custom_fields['pickup_address'][0];
      $DonationCity = ( empty( $custom_fields['pickup_city'][0] ) )? $custom_fields['donor_city'][0] : $custom_fields['pickup_city'][0];
      $DonationState = ( empty( $custom_fields['pickup_state'][0] ) )? $custom_fields['donor_state'][0] : $custom_fields['pickup_state'][0];
      $DonationZip = ( empty( $custom_fields['pickup_zip'][0] ) )? $custom_fields['donor_zip'][0] : $custom_fields['pickup_zip'][0];
      $organization = $custom_fields['organization'][0];
      $PickupDate1 = ( ! empty( $custom_fields['pickupdate1'][0] ) )? $custom_fields['pickupdate1'][0] : '';
      $PickupDate2 = ( ! empty( $custom_fields['pickupdate2'][0] ) )? $custom_fields['pickupdate2'][0] : '';
      $PickupDate3 = ( ! empty( $custom_fields['pickupdate3'][0] ) )? $custom_fields['pickupdate3'][0] : '';
      $org_name = ( is_numeric( $organization ) )? get_the_title( $organization ) : '--';
      $Referer = ( ! empty( $custom_fields['referer'][0] ) )? esc_url( $custom_fields['referer'][0] ) : '';

      $row = [
        'post_title'          => $donation->post_title,
        'post_name'           => $donation->post_name,
        'org_post_title'      => $org_post_title,
        'org_post_name'       => $org_post_name,
        'pickup_code'         => $pickup_code,
        'pickup_description'  => str_replace( ["\n","\r"], "", strip_tags( $pickup_description ) ),
        'post_date'           => $donation->post_date,
        'post_status'         => $donation->post_status,
        'donor_name'          => $custom_fields['donor_name'][0],
        'donor_company'       => $donor_company,
        'donor_address'       => $DonationAddress,
        'donor_city'          => $DonationCity,
        'donor_state'         => $DonationState,
        'donor_zip'           => $DonationZip,
        'donor_phone'         => $custom_fields['donor_phone'][0],
        'donor_email'         => $custom_fields['donor_email'][0],
        'referrer'            => $Referer,
      ];
      $data[] = $row;

      WP_CLI::line('🔔 $org_post_title = ' . $org_post_title );
      WP_CLI::line('🔔 $org_post_name = ' . $org_post_name );
      if( empty( $org_post_title ) || empty( $org_post_name ) ){
        WP_CLI::line('$row = ' . print_r( $row, true ) );
      }
    }


  }

  $fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/donations_' . $start_date . '-thru-' . $end_date . '.csv', 'w' );
  foreach( $data as $fields ){
    fputcsv( $fp, $fields );
  }
  fclose( $fp );
endif;
