<?php
$urlV = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
$usernameV = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
$passwdV = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
$pagesizeV = rcube_utils::get_input_value('ttrss_pagesize', rcube_utils::INPUT_POST);
$autoreadV = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
$autoreadV = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
$showonlyunreadV = rcube_utils::get_input_value('ttrss_showonlyunread', rcube_utils::INPUT_POST);
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
  'type' => 'password', 'autocomplete' => 'off', 'value' => '', 'size' => 255
));
$pagesize = new html_inputfield(array(
  'name' => 'ttrss_pagesize',
  'type' => 'text', 'autocomplete' => 'off', 'value' => $pagesizeV != '' ? $pagesizeV : $this->rc->config->get('ttrss_pagesize'), 'size' => 255
));
$autoread = new html_inputfield(array(
  'name' => 'ttrss_autoread',
  'type' => 'checkbox', 'checked' => $autoreadV != '' ? $autoreadV : $this->rc->config->get('ttrss_autoread')
));
$showonlyunread = new html_inputfield(array(
  'name' => 'ttrss_showonlyunread',
  'type' => 'checkbox', 'checked' => $showonlyunreadV != '' ? $showonlyunreadV : $this->rc->config->get('ttrss_showonlyunread')
));
$p['blocks']['ttrss_preferences_section'] = array(
  'options' => array(
    array('title'=> rcube::Q($this->gettext('url')), 'content' => $url->show()),
    array('title'=> rcube::Q($this->gettext('username')), 'content' => $username->show()),
    array('title'=> rcube::Q($this->gettext('password')), 'content' => $passwd->show()),
    array('title'=> rcube::Q($this->gettext('pagesize')), 'content' => $pagesize->show()),
    array('title'=> rcube::Q($this->gettext('autoread')), 'content' => $autoread->show()),
    array('title'=> rcube::Q($this->gettext('showonlyunread')), 'content' => $showonlyunread->show())
  ),
  'name' => rcube::Q($this->gettext('ttrss_settings'))
);