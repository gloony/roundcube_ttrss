<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getCategories($this->showonlyunread, true);
$items = $callback['content'];
$keys = array_column($items, 'id');
array_multisort($keys, SORT_DESC, $items);
foreach( $items as $item )
{
  if( $item['id']==-1 || $item['id']==-2 )
  {
    $class = 'mailbox';
    if( $item['unread'] > 0 )
    {
      $class .= ' unread';
    }
    switch( $item['id'] )
    {
      case -1:
        $nameID = 'global-unread';
        break;
      case -2:
        $nameID = '-2';
        break;
    }
    $unread = '<span class="unreadcount"></span>';
    echo '      <li id="trsPrCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" data-id="'.$nameID.'" role="treeitem" aria-level="1"><a onclick="ttrss.feed.collapse(\'trsPrCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
        <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\'trsPrCAT'.$item['id'].'\'); return false;">&nbsp;</div>
        <ul id="subtrsPrCAT'.$item['id'].'" class="hidden" role="group">
';
    $callback = $ttrss->getFeeds($item['id']);
    $sitems = $callback['content'];
    if( $sitems !== null )
    {
      $keys = array_column($sitems, 'title');
      array_multisort($keys, SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE, $sitems);
      $view_mode = 'all_articles';
      foreach( $sitems as $sitem )
      {
        $class = 'mailbox';
        if( $sitem['unread'] > 0)
        {
          $class .= ' unread';
          // $view_mode = 'unread';
        }
        if( substr($sitem['id'], 0, 3) === '-10' )
        {
          $class .= ' label';
        }
        $unread = '<span class="unreadcount"></span>';
        echo '          <li id="trsSpCAT'.$sitem['id'].'" class="'.$class.'" data-id="'.$sitem['id'].'" role="treeitem" aria-level="2">
              <a onclick="ttrss.load.headlines('.$sitem['id'].', \''.$view_mode.'\', 1, \'true\', \'trsSpCAT'.$sitem['id'].'\'); return false;">'.$sitem['title'].$unread.'</a>
            </li>';
      }
    }
    echo '        </ul>
      </li>
';
  }
}
$keys = array_column($items, 'title');
array_multisort($keys, SORT_ASC|SORT_NATURAL|SORT_FLAG_CASE, $items);
usort(
  $items,
  function( $a, $b )
  {
    return (int)($a['id']==0);
  }
);
foreach( $items as $item )
{
  if( $item['id'] != -1 && $item['id'] != -2 )
  {
    $class = 'mailbox';
    if( $item['unread'] > 0 )
    {
      $class .= ' unread';
    }
    $unread = '<span class="unreadcount"></span>';
    echo '      <li id="trsCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" data-id="'.$item['id'].'" role="treeitem" aria-level="1"><a onclick="ttrss.load.headlines('.$item['id'].', \''.$view_mode.'\', 1, \'true\', \'trsCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
        <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\'trsCAT'.$item['id'].'\'); return false;">&nbsp;</div>
        <ul id="subtrsCAT'.$item['id'].'" class="hidden" role="group">';
    getFeeds($ttrss, $item['id']);
    echo '        </ul>
      </li>
';
  }
}

function getFeeds( $ttrss, $id = null, $level = 2 )
{
  if( $id === null )
  {
    $id = $_GET['id'];
  }
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
        $class .= ' unread';
        // $view_mode = 'unread';
      }
      $indent = '';
      $subtxt = 'sub';
      if( $level > 2 )
      {
        for( $i = 2; $i <= $level; $i++ )
        {
          $indent .= '  ';
          $subtxt .= 'sub';
        }
      }
      $unread = '<span class="unreadcount"></span>';
      echo $indent.'        <li id="'.$subtxt.'trsCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" data-id="'.$item['id'].'" role="treeitem" aria-level="1"><a onclick="ttrss.load.headlines('.$item['id'].', \'\', 1, \'true\', \''.$subtxt.'trsCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
'.$indent.'          <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\''.$subtxt.'trsCAT'.$item['id'].'\'); return false;">&nbsp;</div>
'.$indent.'          <ul id="'.$subtxt.'subtrsCAT'.$item['id'].'" class="hidden" role="group">';
      getFeeds($ttrss, $item['id'], $level + 1);
      echo $indent.'          </ul>
'.$indent.'      </li>
';
    }
  }
  foreach( $items as $item )
  {
    if( $item['id'] == -2 || $item['id'] == 0 )
    {
      continue;
    }
    elseif( !isset($item['is_cat']) || !$item['is_cat'] )
    {
      $class = 'mailbox';
      $view_mode = 'all_articles';
      if( $item['unread'] > 0 )
      {
        // $scounter = $item['unread'];
        $class .= ' unread';
        // $view_mode = 'unread';
      }
      $indent = '';
      $subtxt = 'sub';
      if( $level > 2 )
      {
        for( $i = 2; $i <= $level; $i++ )
        {
          $indent .= '  ';
          $subtxt .= 'sub';
        }
      }
      $class .= ' feed';
      $unread = '<span class="unreadcount"></span>';
      echo $indent.'          <li id="'.$subtxt.'trsFD'.$item['id'].'" class="'.$class.'" data-id="'.$item['id'].'" role="treeitem" aria-level="'.$level.'">
'.$indent.'            <a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.headlines('.$item['id'].', \''.$view_mode.'\', 1, \'false\', \''.$subtxt.'trsFD'.$item['id'].'\');return false;">'.$item['title'].$unread.'</a>
'.$indent.'          </li>
';
    }
  }
}

exit;