<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$res = $ttrss->unsubscribeFeed($_GET['feed_id']);
echo json_encode($res);

exit;