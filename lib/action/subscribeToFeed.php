<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$res = $ttrss->subscribeToFeed(urldecode($_GET['feed_url']), $_GET['category_id']);
echo json_encode($res);

exit;