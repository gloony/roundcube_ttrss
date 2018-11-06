<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

if( isset($_GET['mode']) )
{
  $mode = $_GET['mode'];
}
else
{
  $mode = 2;
}

$ttrss->updateArticle($_GET['id'], $mode, $_GET['field']);

exit;