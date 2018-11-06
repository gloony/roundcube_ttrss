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
    $this->register_action('index', array($this, 'index'));
    if($this->rc->task == 'mail')
      $this->add_hook('message_compose', array($this, 'message_compose'));
    elseif($this->rc->task == 'settings')
    {
      $this->add_hook('preferences_sections_list', array($this, 'ttrss_preferences_sections_list'));
      $this->add_hook('preferences_list', array($this, 'ttrss_preferences_list'));
      $this->add_hook('preferences_save', array($this, 'ttrss_preferences_save'));
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
      $this->register_action('getArticleAttachments', array($this, 'loadAction'));
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
        $skin_path = $this->local_skin_path();
        $this->include_script($skin_path.'/js/taskmenu.js');
        $this->include_stylesheet($skin_path.'/css/taskmenu.css');
      }
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
      $this->include_script($skin_path.'/js/ttrss/folder.js');
      $this->include_script($skin_path.'/js/ttrss/headlines.js');
      $this->include_script($skin_path.'/js/ttrss/keyboard.js');
      $this->include_script($skin_path.'/js/ttrss/label.js');
      $this->include_stylesheet($skin_path."/css/ttrss.css");
      $this->rcmail->output->set_pagetitle($this->gettext('ttrss'));
      $this->rcmail->output->add_handlers(array('ttrsscontent' => array($this, 'content')));
      $this->rcmail->output->send('ttrss.ttrss');
    }
  }
  function content($attrib)
  {
    $url = $this->rc->config->get('ttrss_url').'api/';
    $username = $this->rc->config->get('ttrss_username');
    $passwd = $this->decrypt($this->rc->config->get('ttrss_passwd'));
    $this->rcmail->output->set_env('ttrss_url', $url);
    return $this->rcmail->output->frame($attrib);
  }

  function createAPI()
  {
    require_once __DIR__ . '/lib/ttrssAPI.php';
    $username = $this->rc->config->get('ttrss_username');
    if($username!==null)
    {
      $url = $this->rc->config->get('ttrss_url').'api/';
      $passwd = $this->decrypt($this->rc->config->get('ttrss_passwd'));
      return new ttrssAPI($url, $username, $passwd);
    }
    else
      return false;
    exit;
  }

  function loadAction()
  {
    require_once __DIR__ . '/lib/action/'.$this->rc->action.'.php';
  }

  function message_compose($args)
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      if(isset($args['param']['ttrss_feed'])){
        $callback = $ttrss->getArticle($args['param']['ttrss_feed']);
        // $args['attachments'][] = array(
        // 	'name'     => $name.".".$type,
        // 	'mimetype' => $mimetype,
        // 	'data'     => $note_content,
        // 	'size'     => filesize($note_file),
        // );
    	$args['param']['html'] = true;
        $args['param']['body'] .= '<a href="'.$callback['content'][0]['link'].'">'.$callback['content'][0]['title']."</a><hr />\n";
        $args['param']['body'] .= $callback['content'][0]['content'];
        $args['param']['subject'] = $callback['content'][0]['title'];
      }
    }
    return $args;
  }

  function ttrss_preferences_sections_list($p)
  {
    $this->add_texts('localization/');
    $p['list']['ttrss'] = array(
      'id' => 'ttrss',
      'section' => $this->gettext('ttrss'),
    );
    return $p;
  }
  function ttrss_preferences_list($p)
  {
    $this->add_texts('localization/');
    if($p['section'] != 'ttrss') return $p;
    $urlV = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
    $usernameV = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
    $passwdV = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
    $pagesizeV = rcube_utils::get_input_value('ttrss_pagesize', rcube_utils::INPUT_POST);
    $autoreadV = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
    $autoreadV = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
    $showonlyunreadV = rcube_utils::get_input_value('ttrss_showonlyunread', rcube_utils::INPUT_POST);
    $url = new html_inputfield(array('name' => 'ttrss_url', 'type' => 'text', 'autocomplete' => 'off', 'value' => $urlV != '' ? $urlV : $this->rc->config->get('ttrss_url'), 'size' => 255));
    $username = new html_inputfield(array('name' => 'ttrss_username', 'type' => 'text', 'autocomplete' => 'off', 'value' => $usernameV != '' ? $usernameV : $this->rc->config->get('ttrss_username'), 'size' => 255));
    $passwd = new html_inputfield(array('name' => 'ttrss_passwd', 'type' => 'password', 'autocomplete' => 'off', 'value' => '', 'size' => 255));
    $pagesize = new html_inputfield(array('name' => 'ttrss_pagesize', 'type' => 'text', 'autocomplete' => 'off', 'value' => $pagesizeV != '' ? $pagesizeV : $this->rc->config->get('ttrss_pagesize'), 'size' => 255));
    $autoread = new html_inputfield(array('name' => 'ttrss_autoread', 'type' => 'checkbox', 'checked' => $autoreadV != '' ? $autoreadV : $this->rc->config->get('ttrss_autoread')));
    $showonlyunread = new html_inputfield(array('name' => 'ttrss_showonlyunread', 'type' => 'checkbox', 'checked' => $showonlyunreadV != '' ? $showonlyunreadV : $this->rc->config->get('ttrss_showonlyunread')));
    $p['blocks']['ttrss_preferences_section'] = array(
      'options' => array(
        array('title'=> rcube::Q($this->gettext('url')), 'content' => $url->show()),
        array('title'=> rcube::Q($this->gettext('username')), 'content' => $username->show()),
        array('title'=> rcube::Q($this->gettext('password')), 'content' => $passwd->show()),
        array('title'=> rcube::Q($this->gettext('pagesize')), 'content' => $pagesize->show()),
        array('title'=> rcube::Q($this->gettext('autoread')), 'content' => $autoread->show()),
        array('title'=> rcube::Q($this->gettext('showonlyunread')), 'content' => $showonlyunread->show()),
      ),
      'name' => rcube::Q($this->gettext('ttrss_settings'))
    );
    return $p;
  }
  function ttrss_preferences_save($p)
  {
    $this->add_texts('localization/');
    if ($p['section'] == 'ttrss')
    {
      $url = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
      if(substr($url, strlen($url) - 1)!='/') $url .= '/';
      $username = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
      $passwd = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
      if($passwd == '') $passwd = $this->decrypt($this->rc->config->get('ttrss_passwd'));
      $ttrss_pagesize = rcube_utils::get_input_value('ttrss_pagesize', rcube_utils::INPUT_POST);
      $ttrss_autoread = rcube_utils::get_input_value('ttrss_autoread', rcube_utils::INPUT_POST);
      $ttrss_showonlyunread = rcube_utils::get_input_value('ttrss_showonlyunread', rcube_utils::INPUT_POST);
      $p['prefs'] = array(
        'ttrss_url'  => $url,
        'ttrss_username'  => $username,
        'ttrss_passwd'    => $this->encrypt($passwd),
        'ttrss_pagesize'    => $ttrss_pagesize,
        'ttrss_autoread'    => $ttrss_autoread,
        'ttrss_showonlyunread'    => $ttrss_showonlyunread,
      );
    }
    return $p;
  }

  private function encrypt($passwd)
  {
    $imap_password = $this->rcmail->decrypt($_SESSION['password']);
    while(strlen($imap_password)<24)
      $imap_password .= $imap_password;
    $imap_password = substr($imap_password, 0, 24);
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', $imap_password);
    $enc = $this->rcmail->encrypt($passwd, 'ttrss_des_key');
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', '');
    return $enc;
  }
  private function decrypt($passwd)
  {
    $imap_password = $this->rcmail->decrypt($_SESSION['password']);
    while(strlen($imap_password)<24)
      $imap_password .= $imap_password;
    $imap_password = substr($imap_password, 0, 24);
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', $imap_password);
    $clear = $this->rcmail->decrypt($passwd, 'ttrss_des_key');
    $deskey_backup = $this->rcmail->config->set('ttrss_des_key', '');
    return $clear;
  }
}