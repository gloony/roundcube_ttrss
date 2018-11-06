<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getArticle($_GET['id']);
$article = $callback['content'][0];

if( isset($article['attachments']) )
{
  foreach( $article['attachments'] as $attachment )
  {
    if( $attachment['id'] == $_GET['attachments'] )
    {
      header('Content-Type: '.$attachment['content_type']);
      echo file_get_contents($attachment['content_url']);
    }
  }
}

exit;