<?php
require_once __DIR__ . '/../encryption.php';
$encryption = new ttrss\encryption($this->rcmail);
$url = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
$username = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
$passwd = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
$ttrss_pagesize = rcube_utils::get_input_value('ttrss_pagesize', rcube_utils::INPUT_POST);
$ttrss_autoread = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);

if(
  $url !== $this->rc->config->get('ttrss_url')
  || $username !== $this->rc->config->get('ttrss_username')
  || $passwd !== $this->rc->config->get('ttrss_passwd')
)
{
  $_SESSION['rc_ttrss_sid'] = null;
  unset($_SESSION['rc_ttrss_sid']);
}

if( $url !== '' && substr($url, strlen($url) - 1) != '/' )
{
  $url .= '/';
}

if( $passwd == '' )
{
  $passwd = $encryption->decrypt($this->rc->config->get('ttrss_passwd'));
}

if( !is_numeric($ttrss_pagesize) )
{
  $ttrss_pagesize = 60;
}
else if( $ttrss_pagesize < 10 )
{
  $ttrss_pagesize = 10;
}
else if( $ttrss_pagesize > 200 )
{
  $ttrss_pagesize = 200;
}

$p['prefs'] = array(
  'ttrss_url'  => $url,
  'ttrss_username'  => $username,
  'ttrss_passwd'    => $encryption->encrypt($passwd),
  'ttrss_pagesize'    => $ttrss_pagesize,
  'ttrss_autoread'    => $ttrss_autoread
);