<?php

$args = [
  'post_type'       => 'trans_dept',
  'post_status'     => 'publish',
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
  2   => 'organization',
  3   => 'by-pass',
  4   => 'old_permalink',
];
$data[ $counter ] = $column_headings;
$counter++;

foreach( $trans_depts as $trans_dept ){
  $pod = pods( 'trans_dept' );
  $pod->fetch( $trans_dept->ID );

  $org = get_post_meta( $trans_dept->ID, 'organization', true );
  $priority = (bool) get_post_meta( $org['ID'], 'priority_pickup', true );

  if( ! $priority && 'publish' == get_post_status( $org['ID'] ) ){
    $org_post_title = ( is_array( $org ) && array_key_exists( 'post_title', $org ) )? $org['post_title'] : '' ;
    WP_CLI::line( '🔔 ' . $counter . ' $priority = ' . $priority . ' writing `' . $org_post_title . '` => ' . $trans_dept->post_title );

    $data[ $counter ] = [
      'post_title'    => $trans_dept->post_title,
      'post_name'     => $trans_dept->post_name,
      'organization'  => $org_post_title,
      'by-pass'       => 'https://old.pickupmydonation.com/step-one/?oid=' . $org['ID'] . '&tid=' . $trans_dept->ID,
      'old_permalink' => '/step-one/?oid=' . $org['ID'] . '&tid=' . $trans_dept->ID,
    ];

    $counter++;
  }
}

$fp = fopen( trailingslashit( dirname( __FILE__ ) ) . 'exports/by-pass-links.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );