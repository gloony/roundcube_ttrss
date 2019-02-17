<?php
require_once __DIR__ . '/../encryption.php';
$encryption = new ttrss\encryption($this->rcmail);

$urlV = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
$usernameV = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
$passwdV = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
$pagesizeV = rcube_utils::get_input_value('ttrss_pagesize', rcube_utils::INPUT_POST);
$autoreadV = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
$url = new html_inputfield(array(
  'name' => 'ttrss_url',
  'type' => 'text',
  'autocomplete' => 'off',
  'value' => $urlV != '' ? $urlV : $this->rc->config->get('ttrss_url'), 'size' => 255
));
$username = new html_inputfield(array(
  'name' => 'ttrss_username',
  'type' => 'text', 'autocomplete' => 'off',
  'value' => $usernameV != '' ? $usernameV : $this->rc->config->get('ttrss_username'), 'size' => 255
));
$passwd = new html_inputfield(array(
  'name' => 'ttrss_passwd',
  'type' => 'password', 'autocomplete' => 'off',
  'value' => '' // $passwdV != '' ? $passwdV : $encryption->decrypt($this->rc->config->get('ttrss_passwd')), 'size' => 255
));
$pagesize = new html_inputfield(array(
  'name' => 'ttrss_pagesize',
  'type' => 'text', 'autocomplete' => 'off', 'value' => $pagesizeV != '' ? $pagesizeV : $this->rc->config->get('ttrss_pagesize'), 'size' => 3
));
$autoread = new html_inputfield(array(
  'name' => 'ttrss_autoread',
  'type' => 'checkbox', 'checked' => $autoreadV != '' ? $autoreadV : $this->rc->config->get('ttrss_autoread')
));

if( $urlV !== '' && $usernameV !== '' )
{
  require_once __DIR__ . '/../ttrssAPI.php';
  $urlV = $urlV != '' ? $urlV : $this->rc->config->get('ttrss_url'); $urlV .= 'api/';
  $usernameV = $usernameV != '' ? $usernameV : $this->rc->config->get('ttrss_username');
  $passwdV = $passwdV != '' ? $passwdV : $encryption->decrypt($this->rc->config->get('ttrss_passwd'));
  $ttrss = new ttrssAPI($urlV, $usernameV, $passwdV, true);
  if( $ttrss->isLoggedIn() )
  {
    $tester = array('title'=> rcube::Q($this->gettext('serverconnection')), 'content' => '<b style="color: #28a745">'.rcube::Q($this->gettext('ok')).'</b>');
  }
  else
  {
    $tester = array('title'=> rcube::Q($this->gettext('serverconnection')), 'content' => '<b style="color: #dc3545">'.rcube::Q($this->gettext('nok')).'</b>');
  }
}
else
{
  $tester = array('title'=> rcube::Q($this->gettext('serverconnection')), 'content' => rcube::Q($this->gettext('notconfigured')));
}

$p['blocks']['ttrss_preferences_section'] = array(
  'options' => array(
    array('title'=> rcube::Q($this->gettext('website')), 'content' => $url->show()),
    array('title'=> rcube::Q($this->gettext('username')), 'content' => $username->show()),
    array('title'=> rcube::Q($this->gettext('password')), 'content' => $passwd->show()),
    array('title'=> rcube::Q($this->gettext('pagesize')), 'content' => $pagesize->show()),
    array('title'=> rcube::Q($this->gettext('keepunread')), 'content' => $autoread->show()),
    $tester
  ),
  'name' => rcube::Q($this->gettext('ttrss_settings'))
);