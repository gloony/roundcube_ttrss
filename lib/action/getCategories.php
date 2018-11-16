<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getCategories();
$items = $callback['content'];
$keys = array_column($items, 'title');
array_multisort($keys, SORT_ASC, $items);
foreach( $items as $item )
{
  if($item['id']<=0)
  {
    continue;
  }
  echo '<option value="'.$item['id'].'">'.$item['title'].'</option>'."\n";
  renderSub($ttrss, $item['id']);
}
echo '<option value="0" selected>None</option>';

function renderSub( $ttrss, $id, $level = 1 )
{
  $callback = $ttrss->getFeeds($id);
  $items = $callback['content'];
  $keys = array_column($items, 'title');
  array_multisort($items, SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE, $keys);
  foreach( $items as $item )
  {
    if( isset($item['is_cat']) && $item['is_cat'] )
    {
      $class = 'mailbox';
      $view_mode = 'all_articles';
      if( $item['unread'] > 0 )
      {
        // $class .= ' unread';
        // $view_mode = 'unread';
      }
      $indent = '';
      for( $i = 1; $i <= $level; $i++ )
      {
        $indent .= '- ';
      }
      echo '<option value="'.$item['id'].'">'.$indent.$item['title'].'</option>'."\n";
      renderSub($ttrss, $item['id'], $level + 1);
    }
  }
}

exit;