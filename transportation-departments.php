<?php

$args = [
  'post_type'       => 'trans_dept',
  'post_status'     => 'any',
  'posts_per_page'  => -1,
  'order'           => 'ASC',
  'orderby'         => 'title',
];

$trans_depts = get_posts( $args );

$counter = 0;
// Setup column headings
$column_headings = [
  0   => 'post_title',
  1   => 'post_name',
  2   => 'post_content',
  3   => 'post_status',
  4   => 'organization',
  5   => 'contact_title',
  6   => 'contact_name',
  7   => 'contact_email',
  8   => 'cc_emails',
  9   => 'phone',
  10  => 'ad_1_graphic',
  11  => 'ad_1_link',
  12  => 'ad_2_graphic',
  13  => 'ad_2_link',
  14  => 'ad_3_graphic',
  15  => 'ad_3_link',
  16  => 'pickup_codes',
];
$data[ $counter ] = $column_headings;
$counter++;

foreach( $trans_depts as $trans_dept ){
  $pod = pods( 'trans_dept' );
  $pod->fetch( $trans_dept->ID );

  $org = get_post_meta( $trans_dept->ID, 'organization', true );
  $org_post_title = ( is_array( $org ) && array_key_exists( 'post_title', $org ) )? $org['post_title'] : '' ;

  $pickup_codes = [];
  $terms = wp_get_post_terms( $trans_dept->ID, 'pickup_code' );
  if( $terms ){
    foreach( $terms as $term ){
      $pickup_codes[] = $term->slug;
    }
  }

  $ads = [];
  for ($i=1; $i < 4; $i++) {
    $attachment = get_post_meta( $trans_dept->ID, 'ad_' . $i . '_graphic', true );
    $ads[$i]['graphic'] = ( is_array( $attachment ) && array_key_exists( 'guid', $attachment ) ) ? $attachment['guid'] : '' ;
    $ads[$i]['link'] = get_post_meta( $trans_dept->ID, 'ad_' . $i . '_link', true );
  }

  if( 'College Hunks Hauling Junk Dallas-Garland, TX' == $trans_dept->post_title ){
    WP_CLI::line( '🔔 This is `College Hunks Hauling Junk Dallas-Garland, TX`... $ads = ' . print_r( $ads, true ) );
  }

  $data[ $counter ] = [
    'post_title'    => $trans_dept->post_title,
    'post_name'     => $trans_dept->post_name,
    'post_content'  => str_replace( [ "\r", "\n" ], '<br>', $trans_dept->post_content ),
    'post_status'   => $trans_dept->post_status,
    'organization'  => $org_post_title,
    'contact_title' => get_post_meta( $trans_dept->ID, 'contact_title', true ),
    'contact_name'  => get_post_meta( $trans_dept->ID, 'contact_name', true ),
    'contact_email' => get_post_meta( $trans_dept->ID, 'contact_email', true ),
    'contact_name'  => get_post_meta( $trans_dept->ID, 'contact_name', true ),
    'cc_emails'     => get_post_meta( $trans_dept->ID, 'cc_emails', true ),
    'phone'         => get_post_meta( $trans_dept->ID, 'phone', true ),
    'ad_1_graphic'  => $ads[1]['graphic'],
    'ad_1_link'     => $ads[1]['link'],
    'ad_2_graphic'  => $ads[2]['graphic'],
    'ad_2_link'     => $ads[2]['link'],
    'ad_3_graphic'  => $ads[3]['graphic'],
    'ad_3_link'     => $ads[3]['link'],
    'pickup_codes'  => implode( ',', $pickup_codes ),
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

$fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/transportation-departments.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );