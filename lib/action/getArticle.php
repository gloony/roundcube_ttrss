<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getArticle($_GET['id']);
$target = '';
$article = $callback['content'][0];
if( !allowFrame($article['link'])
    || substr($article['link'], 0, strlen('http://')) == 'http://' )
  $target = ' target="_BLANK"';
?><!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $article['title']; ?></title>
    <link rel="stylesheet" href="skins/elastic/styles/styles.css?s=1535618602">
    <link rel="stylesheet" href="plugins/ttrss/css/article.css?s=1535544692">
  </head>
  <body>
    <h2>
      <a id="rssHeadArticleLink" href="<?php echo $article['link']; ?>" <?php echo $target; ?>>
         <?php echo $article['title']; ?>
      </a>
    </h2>
    <hr /><br />
<?php echo $article['content']."\n";
    if( isset($article['attachments']) && !empty($article['attachments']) )
    {
      echo '    <hr /><br />'."\n";
      foreach( $article['attachments'] as $attachments )
      {
        $url = './?_task=ttrss&_action=getArticleAttachments&id='.$_GET['id'].'&attachments='.$attachments['id'];
        switch( $attachments['content_type'] )
        {
          case 'image/jpeg':
            echo '    <img src="'.$url.'" class=att_preview />'."\n";
            break;
          case 'application/x-shockwave-flash':
            echo '    <object width="'.$attachments['width'].'" height="'.$attachments['height'].'">
  <param name="movie" value="'.$attachments['content_url'].'">
    <embed src="'.$attachments['content_url'].'" width="'.$attachments['width'].'" height="'.$attachments['height'].'">
  </embed>
</object>'."\n";
            break;
          default:
            echo '    <a href="'.$attachments['content_url'].'">'.$attachments['content_url'].'</a>'."\n";
            break;
        }
      }
    } ?>
  </body>
</html>
<?php
if( $this->autoread && $article['unread'] )
  $ttrss->updateArticle($_GET['id'], 0, 2);

function allowFrame($url){
  $header = @get_headers($url, 1);
  if( !$header || stripos($header[0], '200 ok') === false )
  {
    return false;
  }
  elseif
    ( isset($header['X-Frame-Options'])
     && (
       stripos($header['X-Frame-Options'], 'SAMEORIGIN') !== false
       || stripos($header['X-Frame-Options'], 'deny')!==false) )
  {
    return false;
  }
  else
  {
    return true;
  }
}

exit;