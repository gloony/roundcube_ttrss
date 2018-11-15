<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$empty = true;
$limit = $this->pagesize;
if( isset($_GET['offset']) )
{
  $offset = ($_GET['offset'] * $limit) + 1;
  $offset = $offset - $this->pagesize;
}
else
{
  $offset = 1;
}
if( isset($_GET['is_cat']) )
{
  $is_cat = $_GET['is_cat'];
}
else
{
  $is_cat = true;
}
if( isset($_GET['view_mode']) && !empty($_GET['view_mode']) )
{
  $view_mode = $_GET['view_mode'];
}
else
{
  $view_mode = 'all_articles';
}

if( $this->showonlyunread )
  $view_mode = 'unread';

echo '<table id="messagelist" class="listing messagelist sortheader fixedheader focus" aria-labelledby="aria-label-messagelist" data-list="message_list" data-label-msg="The list is empty.">';
$callback = $ttrss->getHeadlines(
  $_GET['id'],
  $limit,
  $offset,
  $is_cat,
  'true',
  'false',
  $view_mode,
  false,
  0,
  true,
  'date_reverse'
);
if( isset($callback['content']) && is_array($callback['content']))
{
  foreach( $callback['content'] as $item )
  {
    if( !empty($item['labels']) )
    {
      $title = '';
      $count = 0;
      foreach( $item['labels'] as $label )
      {
        if( $title !== '' )
        {
          $title .= ' - ';
          $title .= $label[1];
          $index = 0.2;
          $dec1 = hexdec($color);
          $dec2 = hexdec($label[3]);
          $dec1 = ($dec1 < $dec2) ? $dec1^=$dec2^=$dec1^=$dec2 : $dec1;
          $color = '#'.dechex($dec1 - ($dec1 - $dec2)*0.2);
        }
        else
        {
          $title = $label[1];
          $color = $label[3];
        }
        $count++;
      }
      if( $count>1 )
      {
        $attachment = 'tags';
      }
      else
      {
        $attachment = 'tag';
      }
      $attachment = '<span class="'.$attachment.'" title="'.$title.'" style="color:'.$color.'"></span>';
    }
    else
    {
      $attachment = '&nbsp;';
    }
    $class = ''; $unread = '';
    if( $item['unread']>0 )
    {
      $class .= ' unread';
    }
    if( $item['marked']>0 )
    {
      $class .= ' flagged';
      $flag = 'flagged';
    }
    else
    {
      $flag = 'unflagged';
    }
    if( $empty )
    {
      $empty = false;
    }
    echo '    <tr id="trsHL'.$item['id'].'" class="message'.$class.'">
      <td class="selection">
        <input type="checkbox" tabindex="-1">
      </td>
      <td class="subject" tabindex="0">
        <span class="fromto skip-on-drag">
          <span class="adr">
            <span class="rcmContactAddress" title="'.$item['feed_title'].'">'.$item['feed_title'].'</span>
          </span>
        </span>
        <span class="date skip-on-drag">'.date('H:i:s d/m/Y', $item['updated']).'</span>
        <span class="subject">
          <span id="wdNS.tree" class="msgicon status" onclick="ttrss.article.toggle.read(\''.$item['id'].'\', 2, true); return false;"></span>
          <a href="'.$item['link'].'" tabindex="-1" title="'.$item['title'].'" onclick="ttrss.article.click(\''.$item['id'].'\', event); return false;">
            <span>'.$item['title'].'</span>
          </a>
        </span>
      </td>
      <td class="flags">
        <span class="flag"><span id="flagicnrcmrowOTE" class="'.$flag.'" onclick="ttrss.article.toggle.star(\''.$item['id'].'\'); return false;"></span></span>
        <span class="attachment">'.$attachment.'</span>
      </td>
    </tr>';
  }
}
if( $empty )
  echo '<div class="listing-info">The list is empty.</div>';
echo '</table>';

exit;