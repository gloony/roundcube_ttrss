<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$res = $ttrss->getCounters($_GET['feed_id']);
echo json_encode($res);

exit;