<?php
namespace ttrss;
class encryption
{
  private $rcmail;
  public function __construct( $rcmail )
  {
    $this->rcmail = $rcmail;
  }
  public function encrypt( $passwd )
  {
    $imap_password = $this->rcmail->decrypt($_SESSION['password']);
    while( strlen($imap_password) < 24 )
    {
      $imap_password .= $imap_password;
    }
    $imap_password = substr($imap_password, 0, 24);
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', $imap_password);
    $enc = $this->rcmail->encrypt($passwd, 'ttrss_des_key');
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', '');
    return $enc;
  }
  public function decrypt( $secret )
  {
    $imap_password = $this->rcmail->decrypt($_SESSION['password']);
    while( strlen($imap_password) < 24 )
    {
      $imap_password .= $imap_password;
    }
    $imap_password = substr($imap_password, 0, 24);
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', $imap_password);
    $clear = $this->rcmail->decrypt($secret, 'ttrss_des_key');
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', '');
    return $clear;
  }
}