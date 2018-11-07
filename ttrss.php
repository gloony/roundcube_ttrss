<?php
class ttrss extends rcube_plugin
{
  public $task = '.*';
  public $rc;
  public $rcmail;
  public $ui;

  private $pagesize = 50;
  private $autoread = false;
  private $showonlyunread = false;

  function init()
  {
    $this->rc = rcube::get_instance();
    $this->rcmail = rcmail::get_instance();
    $this->load_config();
    $this->add_texts('localization/');
    $this->register_task('ttrss');
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('refresh', array($this, 'refresh'));
    $this->register_action('index', array($this, 'index'));
    if($this->rc->task == 'mail')
    {
      $this->add_hook('message_compose', array($this, 'message_compose'));
    }
    elseif($this->rc->task == 'settings')
    {
      $this->add_hook('preferences_sections_list', array($this, 'ttrss_preferences_sections_list'));
      $this->add_hook('preferences_list', array($this, 'ttrss_preferences_list'));
      $this->add_hook('preferences_save', array($this, 'ttrss_preferences_save'));
    }
    if($this->rc->task != 'ttrss')
    {
      $this->include_script($this->local_skin_path().'/js/taskmenu.js');
    }
  }
  function startup()
  {
    if($this->rc->config->get('ttrss_username') !== null && $this->rc->config->get('ttrss_username') !== '')
    {
      if($this->rc->config->get('ttrss_pagesize') !== null) $this->pagesize = $this->rc->config->get('ttrss_pagesize');
      if($this->rc->config->get('ttrss_autoread') !== null){
        if( $this->rc->config->get('ttrss_autoread') === 'on') $this->autoread = false;
        else $this->autoread = true;
      }else $this->autoread = true;
      if($this->rc->config->get('ttrss_showonlyunread') !== null){
        if( $this->rc->config->get('ttrss_showonlyunread') === 'on') $this->showonlyunread = true;
        else $this->showonlyunread = false;
      }else $this->showonlyunread = false;
      $this->register_action('getUnread', array($this, 'loadAction'));
      $this->register_action('getTree', array($this, 'loadAction'));
      $this->register_action('getLabels', array($this, 'loadAction'));
      $this->register_action('getHeadlines', array($this, 'loadAction'));
      $this->register_action('getArticle', array($this, 'loadAction'));
      $this->register_action('getAttachment', array($this, 'loadAction'));
      $this->register_action('openLink', array($this, 'loadAction'));
      $this->register_action('getCounters', array($this, 'loadAction'));
      $this->register_action('updateArticle', array($this, 'loadAction'));
      $this->register_action('setArticleLabel', array($this, 'loadAction'));
      if(!$this->rcmail->output->framed)
      {
        $this->add_button(array(
          'command'    => 'ttrss',
          'class'      => 'button-ttrss',
          'classsel'   => 'button-ttrss button-selected',
          'innerclass' => 'button-inner',
          'label'      => 'ttrss.ttrss',
          'type'       => 'link',
        ), 'taskbar');
        $this->include_stylesheet($this->local_skin_path().'/css/taskmenu.css');
      }
    }
  }
  function refresh($r){
    $ttrss = $this->createAPI();
    if( $ttrss !== false )
    {
      $ttrssUnread = $ttrss->getUnread();
      $this->rc->output->command('plugin.ttrss_refresh', array('unread' => $ttrssUnread['content']['unread']));
    }
  }

  function index()
  {
    if($this->rcmail->action == 'index')
    {
      $url = $this->rc->config->get('ttrss_url');
      $url = str_replace('http://', '', $url);
      $url = str_replace('https://', '', $url);
      $url = substr($url, 0, strlen($url) - 1);
      $header_title = $this->rc->config->get('ttrss_username').'@'.$url;
      $this->rcmail->output->set_env('ttrss_header_title', $header_title);
      $this->rcmail->output->set_env('ttrss_pagesize', $this->pagesize);
      $this->rcmail->output->set_env('ttrss_autoread', $this->autoread);
      $skin_path = $this->local_skin_path();
      $this->include_script($skin_path.'/js/favico.js');
      $this->include_script($skin_path.'/js/locStore.js');
      $this->include_script($skin_path.'/js/init.js');
      $this->include_script($skin_path.'/js/ttrss/ttrss.js');
      $this->include_script($skin_path.'/js/ttrss/article.js');
      $this->include_script($skin_path.'/js/ttrss/feed.js');
      $this->include_script($skin_path.'/js/ttrss/headlines.js');
      $this->include_script($skin_path.'/js/ttrss/keyboard.js');
      $this->include_script($skin_path.'/js/ttrss/labels.js');
      $this->include_script($skin_path.'/js/ttrss/tree.js');
      $this->include_stylesheet($skin_path."/css/ttrss.css");
      $this->rcmail->output->set_pagetitle($this->gettext('ttrss'));
      $this->rcmail->output->add_handlers(array('ttrsscontent' => array($this, 'content')));
      $this->rcmail->output->send('ttrss.ttrss');
    }
  }
  function content($attrib)
  {
    $this->rcmail->output->set_env('ttrss_url', $url);
    return $this->rcmail->output->frame($attrib);
  }

  function createAPI()
  {
    require_once __DIR__ . '/lib/encryption.php';
    $encryption = new ttrss\encryption($this->rcmail);
    require_once __DIR__ . '/lib/ttrssAPI.php';
    $username = $this->rc->config->get('ttrss_username');
    if($username!==null)
    {
      $url = $this->rc->config->get('ttrss_url').'api/';
      $passwd = $encryption->decrypt($this->rc->config->get('ttrss_passwd'));
      return new ttrssAPI($url, $username, $passwd);
    }
    else
    {
      return false;
    }
  }

  function loadAction()
  {
    require_once __DIR__ . '/lib/action/'.$this->rc->action.'.php';
  }

  function message_compose($m)
  {
    if(!isset($m['param']['ttrss_feed'])) return $m;
    require_once __DIR__ . '/lib/message/compose.php';
    return $m;
  }

  function ttrss_preferences_sections_list($p)
  {
    require_once __DIR__ . '/lib/preferences/sections_list.php';
    return $p;
  }
  function ttrss_preferences_list($p)
  {
    if( $p['section'] != 'ttrss' ) return $p;
    require_once __DIR__ . '/lib/preferences/list.php';
    return $p;
  }
  function ttrss_preferences_save($p)
  {
    if( $p['section'] != 'ttrss' ) return $p;
    require_once __DIR__ . '/lib/preferences/save.php';
    return $p;
  }
}