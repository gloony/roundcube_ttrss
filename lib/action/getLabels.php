<?php
$ttrss = $this->createAPI();
if( $ttrss === false ) exit;

$callback = $ttrss->getLabels();
$items = $callback['content'];
$keys = array_column($items, 'caption');
array_multisort($keys, SORT_ASC, $items);
foreach( $items as $item )
{
  if( $item['id']==-2 || $item['id']==0 )
  {
    continue;
  }
  echo '<li role="menuitem"><a class="expand all active" id="trsLBL'.$item['id'].'" role="button" tabindex="-1" aria-disabled="true" onclick="ttrss.article.toggle.label('.$item['id'].', '.$_GET['mode'].'); return false;" style="color:'.$item['bg_color'].'">'.$item['caption'].'</a></li>';
}

exit;