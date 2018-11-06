<?php
$ttrss = $this->createAPI();
if( $ttrss === false )
{
  echo 0;
}
else
{
  $ttrssUnread = $ttrss->getUnread();
  echo $ttrssUnread['content']['unread'];
}

exit;