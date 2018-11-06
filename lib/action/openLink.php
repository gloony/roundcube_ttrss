<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getArticle($_GET['id']);
header('Location: '.$callback['content'][0]['link']);

exit;