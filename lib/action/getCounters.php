<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$output_mode = '';
if( isset($_GET['output_mode']) )
  $output_mode = $_GET['output_mode'];

$counters = $ttrss->getCounters($output_mode);
$counters = $counters['content'];

echo json_encode($counters);

exit;