<?php

$args = [
  'post_type'       => 'store',
  'post_status'     => 'any',
  'posts_per_page'  => -1,
  'order'           => 'ASC',
  'orderby'         => 'title',
];

$stores = get_posts( $args );

$counter = 0;
// Setup column headings
$column_headings = [
  0   => 'post_title',
  1   => 'post_name',
  2   => 'post_status',
  3   => 'trans_dept',
  4   => 'address_street',
  5   => 'address_city',
  6   => 'address_state',
  7   => 'address_zip_code',
  8   => 'address_phone',
];
$data[ $counter ] = $column_headings;
$counter++;

foreach( $stores as $store ){
  //$pod = pods( 'store' );
  //$pod->fetch( $store->ID );

  $trans_dept = get_post_meta( $store->ID, 'trans_dept', true );
  $trans_dept_post_title = '';
  if( $trans_dept )
    $trans_dept_post_title = $trans_dept['post_title'];

  $address_street = get_post_meta( $store->ID, 'address', true );
  $address_city = get_post_meta( $store->ID, 'city', true );
  $address_state = get_post_meta( $store->ID, 'state', true );
  $address_zip_code = get_post_meta( $store->ID, 'zip_code', true );
  $address_phone = get_post_meta( $store->ID, 'phone', true );

  $data[ $counter ] = [
    'post_title'        => $store->post_title,
    'post_name'         => $store->post_name,
    'post_status'       => $store->post_status,
    'trans_dept'        => $trans_dept_post_title,
    'address_street'    => $address_street,
    'address_city'      => $address_city,
    'address_state'     => $address_state,
    'address_zip_code'  => $address_zip_code,
    'address_phone'     => $address_phone,
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

$fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/stores.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );