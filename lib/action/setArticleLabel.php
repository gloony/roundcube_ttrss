<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

switch( $_GET['mode'] ){
  case 0: case '0': case false:
    $mode = false;
    break;
  case 1: case '1': case true: default:
    $mode = true;
}

$ttrss->setArticleLabel($_GET['id_article'], $_GET['id_label'], $mode);

exit;