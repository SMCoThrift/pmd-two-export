<?php

$taxonomy = $args[0];
if( empty( $taxonomy ) )
  WP_CLI::error( '🚨 Please provide a taxonomy as the first argument when calling this file.' );

if( ! taxonomy_exists( $taxonomy ) )
  WP_CLI::error( '🚨 No taxonomy found for `' . $taxonomy . '`!');

$terms = get_terms([
  'taxonomy'    => $taxonomy,
  'orderby'     => 'name',
  'number'      => 0,
  'hide_empty'  => false,
]);

$counter = 0;
// Setup column headings
$data[ $counter ] = [
  0 => 'name',
  1 => 'slug',
  2 => 'taxonomy',
  3 => 'description',
];
$counter++;
foreach( $terms as $term ){

  $data[ $counter ] = [
    'name'        => $term->name,
    'slug'        => $term->slug,
    'taxonomy'    => $taxonomy,
    'description' => $term->description,
  ];

  $pod = pods( $taxonomy );
  $pod->fetch( $term->term_id );

  switch( $taxonomy ){
    case 'donation_option':
      $data[ $counter ]['skip_questions'] = $pod->field( 'skip_questions' );
      $data[ $counter ]['pickup'] = $pod->field( 'pickup' );
      // Add extra column headings
      if( 1 === $counter ){
        $data[0][] = 'skip_questions';
        $data[0][] = 'pickup';
      }
      break;

    default:
      // nothing
  }
  $counter++;
}

WP_CLI::line( '🔔 $data = ' . print_r( $data, true ) );
$fp = fopen( $taxonomy . '.csv', 'w' );
foreach( $data as $fields ){
  fputcsv( $fp, $fields );
}
fclose( $fp );