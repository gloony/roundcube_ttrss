<?php
class ttrss extends rcube_plugin
{
  public $task = '.*';
  public $rc;
  public $rcmail;
  public $ui;
  private $pagesize = 50;

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
    {
      $this->add_hook('message_compose', array($this, 'message_compose'));
    }
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
      $this->register_action('getUnread', array($this, 'getUnread'));
      $this->register_action('getTree', array($this, 'getTree'));
      // $this->register_action('getFeeds', array($this, 'getFeeds'));
      $this->register_action('getLabels', array($this, 'getLabels'));
      $this->register_action('getHeadlines', array($this, 'getHeadlines'));
      $this->register_action('getArticle', array($this, 'getArticle'));
      $this->register_action('getArticleAttachments', array($this, 'getArticleAttachments'));
      $this->register_action('openLink', array($this, 'openLink'));
      $this->register_action('getCounters', array($this, 'getCounters'));
      $this->register_action('updateArticle', array($this, 'updateArticle'));
      $this->register_action('setArticleLabel', array($this, 'setArticleLabel'));
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
        $this->include_script($skin_path.'/js/badge.js');
        $this->include_stylesheet($skin_path.'/css/icon.css');
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
      $skin_path = $this->local_skin_path();
      $this->include_script($skin_path.'/js/locStore.js');
      $this->include_script($skin_path.'/js/keyboard.js');
      $this->include_script($skin_path.'/js/ttrss.js');
      $this->include_script($skin_path.'/js/init.js');
      $this->include_stylesheet($skin_path."/css/app.css");
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
    require_once __DIR__ . '/ttrssAPI.php';
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

  function getUnread()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $ttrssUnread = $ttrss->getUnread();
      echo $ttrssUnread['content']['unread'];
    }
    else
      echo 0;
    exit;
  }

  function getTree()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $callback = $ttrss->getCategories(false, true);
      $items = $callback['content'];
      $keys = array_column($items, 'id');
      array_multisort($keys, SORT_DESC, $items);
      foreach($items as $item){
        if($item['id']==-1||$item['id']==-2){
          $class = 'mailbox'; $unread = '';
          if($item['unread']>0){
            $class .= ' unread';
            $unread = '<span class="unreadcount">'.$item['unread'].'</span>';
          }
          echo '      <li id="trsPrCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" role="treeitem" aria-level="1"><a onclick="ttrss.feed.collapse(\'trsPrCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
        <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\'trsPrCAT'.$item['id'].'\'); return false;">&nbsp;</div>
        <ul id="subtrsPrCAT'.$item['id'].'" class="hidden" role="group">
';
          $callback = $ttrss->getFeeds($item['id']);
          $sitems = $callback['content'];
          $keys = array_column($sitems, 'title');
          array_multisort($keys, SORT_ASC, $sitems);
          $view_mode = 'all_articles';
          foreach($sitems as $sitem){
            $class = 'mailbox'; $unread = '';
            if($sitem['unread']>0){
              $class .= ' unread';
              $unread = '<span class="unreadcount">'.$sitem['unread'].'</span>';
              // $view_mode = 'unread';
            }
            if(substr($sitem['id'], 0, 3)==='-10') $class .= ' label';
            echo '          <li id="trsCAT'.$sitem['id'].'" class="'.$class.'" role="treeitem" aria-level="2">
            <a onclick="ttrss.load.headlines('.$sitem['id'].', \''.$view_mode.'\', 1, \'true\', \'trsCAT'.$sitem['id'].'\'); return false;">'.$sitem['title'].$unread.'</a>
          </li>';
            }
          echo '        </ul>
      </li>
';
        }
      }
      $keys = array_column($items, 'title');
      array_multisort($keys, SORT_ASC, $items);
      foreach($items as $item){
        if($item['id']!=-1&&$item['id']!=-2){
          $class = 'mailbox'; $unread = '';
          if($item['unread']>0){
            $class .= ' unread';
            $unread = '<span class="unreadcount">'.$item['unread'].'</span>';
          }
          echo '      <li id="trsCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" role="treeitem" aria-level="1"><a onclick="ttrss.load.headlines('.$item['id'].', \''.$view_mode.'\', 1, \'true\', \'trsCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
        <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\'trsCAT'.$item['id'].'\'); return false;">&nbsp;</div>
        <ul id="subtrsCAT'.$item['id'].'" class="hidden" role="group">';
          $this->getFeeds($item['id']);
          echo '        </ul>
      </li>
';
        }
      }
    }
    exit;
  }

  private function getFeeds($id = null, $level = 2)
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      if($id===null) $id = $_GET['id'];
      $callback = $ttrss->getFeeds($id);
      $items = $callback['content'];
      $keys = array_column($items, 'title');
      array_multisort($keys, SORT_ASC, $items);
      usort($items, function ($a, $b){
        return (int)!array_key_exists('is_cat', $a);
      });
      foreach($items as $item){
        if($item['id']==-2||$item['id']==0) continue;
        $class = 'mailbox'; $unread = '';
        $view_mode = 'all_articles';
        if($item['unread']>0){
          $class .= ' unread';
          $unread = '<span class="unreadcount">'.$item['unread'].'</span>';
          // $view_mode = 'unread';
        }
        $indent = '';
        $subtxt = 'sub';
        if($level>2){
          for($i = 2; $i <= $level; $i++){
            $indent .= '  ';
            $subtxt .= 'sub';
          }
        }
        if(isset($item['is_cat'])&&$item['is_cat']){
          echo $indent.'        <li id="'.$subtxt.'trsCAT'.$item['id'].'" class="'.$class.'" aria-expanded="false" role="treeitem" aria-level="1"><a onclick="ttrss.load.headlines('.$item['id'].', \'\', 1, \'true\', \''.$subtxt.'trsCAT'.$item['id'].'\'); return false;">'.$item['title'].$unread.'</a>
'.$indent.'          <div class="treetoggle collapsed" onclick="ttrss.feed.collapse(\''.$subtxt.'trsCAT'.$item['id'].'\'); return false;">&nbsp;</div>
'.$indent.'          <ul id="'.$subtxt.'subtrsCAT'.$item['id'].'" class="hidden" role="group">';
          $this->getFeeds($item['id'], $level + 1);
          echo $indent.'          </ul>
'.$indent.'      </li>
';
        }else{
          if(substr($item['id'], 0, 3)==='-10') $class .= ' label';
          echo $indent.'          <li id="'.$subtxt.'trsFD'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="'.$level.'">
'.$indent.'            <a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.headlines('.$item['id'].', \''.$view_mode.'\', 1, \'false\', \''.$subtxt.'trsFD'.$item['id'].'\');return false;">'.$item['title'].$unread.'</a>
'.$indent.'          </li>
';
        }
      }
    }
    if(isset($_GET['id'])) exit;
  }

  function getLabels()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      // $callback = $ttrss->getFeeds(-2, false, 200, 0, true);
      $callback = $ttrss->getLabels();
      $items = $callback['content'];
      $keys = array_column($items, 'title');
      array_multisort($keys, SORT_ASC, $items);
      foreach($items as $item){
        if($item['id']==-2||$item['id']==0) continue;
        echo '<li role="menuitem"><a class="expand all active" id="trsLBL'.$item['id'].'" role="button" tabindex="-1" aria-disabled="true" onclick="ttrss.article.toggle.label('.$item['id'].', '.$_GET['mode'].'); return false;" style="color:'.$item['bg_color'].'">'.$item['caption'].'</a></li>';
      }
    }
    exit;
  }

  function getHeadlines()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $empty = true;
      $limit = $this->pagesize;
      if(isset($_GET['offset'])){
        $offset = ($_GET['offset'] * $limit) + 1;
        $offset = $offset - $this->pagesize;
      }else $offset = 1;
      if(isset($_GET['is_cat'])) $is_cat = $_GET['is_cat'];
      else $is_cat = true;
      if(isset($_GET['view_mode'])&&!empty($_GET['view_mode'])) $view_mode = $_GET['view_mode'];
      else $view_mode = 'all_articles';
      echo '<table id="messagelist" class="listing messagelist sortheader fixedheader focus" aria-labelledby="aria-label-messagelist" data-list="message_list" data-label-msg="The list is empty.">';
      $callback = $ttrss->getHeadlines($_GET['id'], $limit, $offset, $is_cat, 'true', 'false', $view_mode, false, 0, true, 'date_reverse');
      foreach($callback['content'] as $item){
        if(!empty($item['labels'])){
          $title = ''; $count = 0;
          foreach($item['labels'] as $label){
            if($title!==''){
              $title .= ' - ';
              $title .= $label[1];
              $index = 0.2;
              $dec1 = hexdec($color);
              $dec2 = hexdec($label[3]);
              $dec1 = ($dec1 < $dec2) ? $dec1^=$dec2^=$dec1^=$dec2 : $dec1;
              $color = '#'.dechex($dec1 - ($dec1 - $dec2)*0.2);
            }else{
              $title = $label[1];
              $color = $label[3];
            }
            $count++;
          }
          if($count>1) $attachment = 'tags';
          else $attachment = 'tag';
          $attachment = '<span class="'.$attachment.'" title="'.$title.'" style="color:'.$color.'"></span>';
        }else $attachment = '&nbsp;';
        $class = ''; $unread = '';
        if($item['unread']>0){
          $class .= ' unread';
        }
        if($item['marked']>0){
          $class .= ' flagged';
          $flag = 'flagged';
        }else{
          $flag = 'unflagged';
        }
        if($empty) $empty = false;
        echo '    <tr id="trsHL'.$item['id'].'" class="message'.$class.'">
      <td class="selection">
        <input type="checkbox" tabindex="-1">
      </td>
      <td class="subject" tabindex="0">
        <span class="fromto skip-on-drag">
          <span class="adr">
            <span class="rcmContactAddress">'.$item['feed_title'].'</span>
          </span>
        </span>
        <span class="date skip-on-drag">'.date('H:i:s d/m/Y', $item['updated']).'</span>
        <span class="subject">
          <span id="wdNS.tree" class="msgicon status" title="" onclick="ttrss.article.toggle.read(\''.$item['id'].'\'); return false;"></span>
          <a href="'.$item['link'].'" tabindex="-1" onclick="ttrss.load.article(\''.$item['id'].'\'); return false;">
            <span>'.$item['title'].'</span>
          </a>
        </span>
      </td>
      <td class="flags">
        <span class="flag"><span id="flagicnrcmrowOTE" class="'.$flag.'" onclick="ttrss.article.toggle.star(\''.$item['id'].'\'); return false;"></span></span>
        <span class="attachment">'.$attachment.'</span>
      </td>
    </tr>';
      }
      if($empty) echo '<div class="listing-info">The list is empty.</div>';
      echo '</table>';
    }
    exit;
  }

  function getArticle()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $callback = $ttrss->getArticle($_GET['id']);
      $target = '';
      if(!$this->allowFrame($callback['content'][0]['link'])) $target = ' target="_BLANK"';
      if(substr($callback['content'][0]['link'], 0, strlen('http://'))=='http://') $target = ' target="_BLANK"';
      // var_dump($callback);
?><!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $callback['content'][0]['title']; ?></title>
    <link rel="stylesheet" href="skins/elastic/styles/styles.css?s=1535618602">
    <link rel="stylesheet" href="plugins/ttrss/css/article.css?s=1535544692">
  </head>
  <body>
    <h2><a id="rssHeadArticleLink" href="<?php echo $callback['content'][0]['link']; ?>" <?php echo $target; ?>><?php echo $callback['content'][0]['title']; ?></a></h2>
    <hr /><br />
    <?php echo $callback['content'][0]['content']; ?>
    <?php
      if(isset($callback['content'][0]['attachments'])&&!empty($callback['content'][0]['attachments'])){
        echo '<hr /><br />';
        foreach($callback['content'][0]['attachments'] as $attachments){
          $url = './?_task=ttrss&_action=getArticleAttachments&id='.$_GET['id'].'&attachments='.$attachments['id'];
          switch($attachments['content_type']){
            case 'image/jpeg': echo '<img src="'.$url.'" class=att_preview />'; break;
            case 'application/x-shockwave-flash': echo '<object width="'.$attachments['width'].'" height="'.$attachments['height'].'"><param name="movie" value="'.$attachments['content_url'].'"><embed src="'.$attachments['content_url'].'" width="'.$attachments['width'].'" height="'.$attachments['height'].'"></embed></object>'; break;
            default:
              echo '<a href="'.$attachments['content_url'].'">'.$attachments['content_url'].'</a>';
              break;
          }
        }
      } ?>

  </body>
</html>
<?php
      $ttrss->updateArticle($_GET['id'], 0, 2);
    }
    exit;
  }

  function getArticleAttachments()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $callback = $ttrss->getArticle($_GET['id']);
      if(isset($callback['content'][0]['attachments'])){
        foreach($callback['content'][0]['attachments'] as $attachments){
          if($attachments['id']==$_GET['attachments']){
            header('Content-Type: '.$attachments['content_type']);
            echo file_get_contents($attachments['content_url']);
          }
        }
      }
    }
    exit;
  }

  function openLink()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $callback = $ttrss->getArticle($_GET['id']);
      header('Location: '.$callback['content'][0]['link']);
    }
    exit;
  }

  function getCounters()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      $output_mode = '';
      if(isset($_GET['output_mode'])) $output_mode = $_GET['output_mode'];
      $counters = $ttrss->getCounters($output_mode);
      $counters = $counters['content'];
      var_dump($counters); exit();
    }
    exit;
  }

  function updateArticle()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      if(isset($_GET['mode'])) $mode = $_GET['mode'];
      else $mode = 2;
      $ttrss->updateArticle($_GET['id'], $mode, $_GET['field']);
    }
    exit;
  }

  function setArticleLabel()
  {
    $ttrss = $this->createAPI();
    if($ttrss!==false)
    {
      switch($_GET['mode']){
        case 0: case '0': case false: $mode = false; break;
        case 1: case '1': case true: default: $mode = true;
      }
      var_dump($ttrss->setArticleLabel($_GET['id_article'], $_GET['id_label'], $mode));
    }
    exit;
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
    $url = new html_inputfield(array('name' => 'ttrss_url', 'type' => 'text', 'autocomplete' => 'off', 'value' => $urlV != '' ? $urlV : $this->rc->config->get('ttrss_url'), 'size' => 255));
    $username = new html_inputfield(array('name' => 'ttrss_username', 'type' => 'text', 'autocomplete' => 'off', 'value' => $usernameV != '' ? $usernameV : $this->rc->config->get('ttrss_username'), 'size' => 255));
    $passwd = new html_inputfield(array('name' => 'ttrss_passwd', 'type' => 'password', 'autocomplete' => 'off', 'value' => '', 'size' => 255));
    $pagesize = new html_inputfield(array('name' => 'ttrss_pagesize', 'type' => 'text', 'autocomplete' => 'off', 'value' => $pagesizeV != '' ? $pagesizeV : $this->rc->config->get('ttrss_pagesize'), 'size' => 255));
    $p['blocks']['ttrss_preferences_section'] = array(
      'options' => array(
        array('title'=> rcube::Q($this->gettext('url')), 'content' => $url->show()),
        array('title'=> rcube::Q($this->gettext('username')), 'content' => $username->show()),
        array('title'=> rcube::Q($this->gettext('password')), 'content' => $passwd->show()),
        array('title'=> rcube::Q($this->gettext('pagesize')), 'content' => $pagesize->show()),
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
      $p['prefs'] = array(
        'ttrss_url'  => $url,
        'ttrss_username'  => $username,
        'ttrss_passwd'    => $this->encrypt($passwd),
        'ttrss_pagesize'    => $ttrss_pagesize,
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

  public function allowFrame($url){
    $header = @get_headers($url, 1);
    if(!$header||stripos($header[0], '200 ok')===false) return false;
    elseif (isset($header['X-Frame-Options'])&&(stripos($header['X-Frame-Options'], 'SAMEORIGIN')!==false||stripos($header['X-Frame-Options'], 'deny')!==false)) return false;
    else return true;
  }
}