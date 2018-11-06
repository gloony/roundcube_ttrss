<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

if( isset($m['param']['ttrss_feed']) )
{
  $callback = $ttrss->getArticle($m['param']['ttrss_feed']);
  $article = $callback['content'][0];

  $m['param']['html'] = true;
  $m['param']['body'] .= '<a href="'.$article['link'].'">'.$article['title']."</a><hr />\n";
  $m['param']['body'] .= $article['content'];
  $m['param']['subject'] = $article['title'];
}