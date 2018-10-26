<?php
class ttrss extends rcube_plugin
{
	public $task = '.*';
	public $rc;
	public $ui;
	private $env_loaded = false;
	/**
* Plugin initialization.
*/
	function init()
	{
		$this->rc = rcube::get_instance();
		$this->load_ui();
		$this->register_task('ttrss');
		$this->add_hook('startup', array($this, 'startup'));
		$this->register_action('index', array($this, 'action'));
		if($this->rc->task == 'settings')
		{
			$this->add_hook('preferences_sections_list', array($this, 'ttrss_preferences_sections_list'));
			$this->add_hook('preferences_list', array($this, 'ttrss_preferences_list'));
			$this->add_hook('preferences_save', array($this, 'ttrss_preferences_save'));
		}
	}
	/**
* Startup the application, adding the Task-button
*/
	function startup()
	{
		$rcmail = rcmail::get_instance();
		if($this->rc->config->get('ttrss_username') !== null && $this->rc->config->get('ttrss_username') !== '')
		{
			$this->register_action('getunreaditems', array($this, 'getunreaditems'));
			$this->register_action('getTree', array($this, 'getTree'));
			$this->register_action('getFeeds', array($this, 'getFeeds'));
			$this->register_action('getLabels', array($this, 'getLabels'));
			$this->register_action('getHeadlines', array($this, 'getHeadlines'));
			$this->register_action('getArticle', array($this, 'getArticle'));
			$this->register_action('getArticleAttachments', array($this, 'getArticleAttachments'));
			$this->register_action('getCounters', array($this, 'getCounters'));
			$this->register_action('updateArticle', array($this, 'updateArticle'));
			$this->register_action('setArticleLabel', array($this, 'setArticleLabel'));
			if(!$rcmail->output->framed)
			{
				// add taskbar button
				$this->add_button(array(
					'command'    => 'ttrss',
					'class'      => 'button-ttrss',
					'classsel'   => 'button-ttrss button-selected',
					'innerclass' => 'button-inner',
					'label'      => 'ttrss.ttrss',
					'type'       => 'link',
				), 'taskbar');
				$this->include_script('ttrss.js');
				$this->include_stylesheet('ttrss.css');
			}
		}
	}
	/**
* Create the connection to ttrss with API
*
* @return class of ttrssAPI
*/
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
	/**
* Echo the unreads elements on TTRSS and exit
*/
	function getunreaditems()
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
	/**
* Echo the tree of folders and exit
*/
	function getTree()
	{
		$ttrss = $this->createAPI();
		// $counters = $ttrss->getFeedTree();
		if($ttrss!==false)
		{
			$callback = $ttrss->getCategories(false, true);
			$items = $callback['content'];
			$keys = array_column($items, 'title');
			array_multisort($keys, SORT_ASC, $items);
			// var_dump($items);
			foreach($items as $item){
				if($item['id']==-1){
					$class = 'mailbox'; $unread = '';
					if($item['unread']>0){
						$class .= ' unread';
						$unread = '<span class="unreadcount">'.$item['unread'].'</span>';
					}
					echo '			<li id="trsCAT'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="1">
				<a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.feeds('.$item['id'].'); return false;">'.$item['title'].$unread.'</a>
			</li>';
					break;
				}
			}
			foreach($items as $item){
				if($item['id']==-2){
					$class = 'mailbox'; $unread = '';
					if($item['unread']>0){
						$class .= ' unread';
						$unread = '<span class="unreadcount">'.$item['unread'].'</span>';
					}
					echo '			<li id="trsCAT'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="1">
				<a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.feeds('.$item['id'].'); return false;">'.$item['title'].$unread.'</a>
			</li>';
					break;
				}
			}
			foreach($items as $item){
				if($item['id']!=-1&&$item['id']!=-2){
					$class = 'mailbox'; $unread = '';
					if($item['unread']>0){
						$class .= ' unread';
						$unread = '<span class="unreadcount">'.$item['unread'].'</span>';
					}
					echo '			<li id="trsCAT'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="1">
				<a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.headlines('.$item['id'].'); return false;">'.$item['title'].$unread.'</a>
			</li>';
				}
			}
		}
		exit;
	}
	/**
* Echo the tree of folders and exit
*/
	function getFeeds()
	{
		$ttrss = $this->createAPI();
		if($ttrss!==false)
		{
			$callback = $ttrss->getFeeds($_GET['id'], false, 200, 0, true);
			echo '			<li id="trsCAT'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="1">
				<a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.folder(); return false;">..</a>
			</li>';
			$items = $callback['content'];
			$keys = array_column($items, 'title');
			array_multisort($keys, SORT_ASC, $items);
			foreach($items as $item){
				if($item['id']==-2||$item['id']==0) continue;
				$class = 'mailbox'; $unread = '';
				if($item['unread']>0){
					$class .= ' unread';
					$unread = '<span class="unreadcount">'.$item['unread'].'</span>';
					$view_mode = 'unread';
				}else{
					$view_mode = 'all_articles';
				}
				echo '			<li id="trsCAT'.$item['id'].'" class="'.$class.'" role="treeitem" aria-level="1">
				<a data-type="folder" data-path="'.$path.$item['name'].'" onclick="ttrss.load.headlines('.$item['id'].', \''.$view_mode.'\');return false;">'.$item['title'].$unread.'</a>
			</li>';
			}
		}
		exit;
	}
	/**
* Echo the tree of folders and exit
*/
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
				echo '<li role="menuitem"><a class="expand all active" id="rcmbtn137" role="button" tabindex="-1" aria-disabled="true" href="#" onclick="ttrss.article.toggle.label('.$item['id'].', '.$_GET['mode'].'); return false;" style="color:'.$item['bg_color'].'">'.$item['caption'].'</a></li>';
			}
		}
		exit;
	}
	/**
* Echo the tree of folders and exit
*/
	function getHeadlines()
	{
		$ttrss = $this->createAPI();
		if($ttrss!==false)
		{
			$empty = true;
			$limit = 50;
			if(isset($_GET['offset'])){
				$offset = ($_GET['offset'] * $limit) + 1;
				$offset = $offset - 50;
			}else $offset = 1;
			if(isset($_GET['view_mode'])&&!empty($_GET['view_mode'])) $view_mode = $_GET['view_mode'];
			else $view_mode = 'all_articles';
			echo '<table id="messagelist" class="listing messagelist sortheader fixedheader focus" aria-labelledby="aria-label-messagelist" data-list="message_list" data-label-msg="The list is empty.">';
			$callback = $ttrss->getHeadlines($_GET['id'], $limit, $offset, 'true', 'true', 'false', $view_mode, false, 0, true, 'date_reverse');
			foreach($callback['content'] as $item){
				if(!empty($item['labels'])){
					$title = ''; $count = 0;
					foreach($item['labels'] as $label){
						if($title!=='') $title .= ' - ';
						$title .= $label[1];
						$color = $label[3];
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
				echo '		<tr id="trsHL'.$item['id'].'" class="message'.$class.'">
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
				<span class="attachment" onClick="rcmail.command(\'menu-open\', \'messagelistmenu\', this, event); ttrss.after.label.show(); return false;">'.$attachment.'</span>
			</td>
		</tr>';
			}
			if($empty) echo '<div class="listing-info">The list is empty.</div>';
			echo '</table>';
		}
		exit;
	}
	/**
* Echo the tree of folders and exit
*/
	function getArticle()
	{
		$ttrss = $this->createAPI();
		if($ttrss!==false)
		{
			$callback = $ttrss->getArticle($_GET['id']);
			// var_dump($callback);
?><!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $callback['content'][0]['title']; ?></title>
		<link rel="stylesheet" href="skins/elastic/styles/styles.css?s=1535618602">
		<link rel="stylesheet" href="plugins/ttrss/css/article.css?s=1535544665">
	</head>
	<body>
		<h2><?php echo $callback['content'][0]['title']; ?></h2>
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
	/**
* Echo the tree of folders and exit
*/
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
	/**
* Echo the tree of folders and exit
*/
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
	/**
* Update Article and exit
*/
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
	/**
* Update Article Label and exit
*/
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
	/**
* Manage the action (called from Roundcube)
*/
	function action()
	{
		$rcmail = rcmail::get_instance();
		if($rcmail->action == 'index')
		{
			$url = $this->rc->config->get('ttrss_url');
			$url = str_replace('http://', '', $url);
			$url = str_replace('https://', '', $url);
			$url = substr($url, 0, strlen($url) - 1);
			$header_title = $this->rc->config->get('ttrss_username').'@'.$url;
			$rcmail->output->set_env('ttrss_header_title', $header_title);
			$this->include_script('js/locStore.js');
			$this->include_script('js/ttrss.js');
			$this->include_script('js/keyboard.js');
			$skin_path = $this->local_skin_path();
			$this->include_stylesheet($skin_path."/ttrss.css");
			$rcmail->output->set_pagetitle($this->gettext('ttrss'));
			$rcmail->output->add_handlers(array('ttrsscontent' => array($this, 'content')));
			$rcmail->output->send('ttrss.ttrss');
		}
	}
	/**
* Display the content of the calender (calling TTRSS)
*/
	function content($attrib)
	{
		$rcmail = rcmail::get_instance();
		$url = $this->rc->config->get('ttrss_url').'api/';
		$username = $this->rc->config->get('ttrss_username');
		$passwd = $this->decrypt($this->rc->config->get('ttrss_passwd'));
		$rcmail->output->set_env('ttrss_url', $url);
		return $rcmail->output->frame($attrib);
	}
	/**
* Handler for preferences_sections_list hook.
* Adds Encryption settings section into preferences sections list.
*
* @param array Original parameters
*
* @return array Modified parameters
*/
	function ttrss_preferences_sections_list($p)
	{
		$this->add_texts('localization/');
		$p['list']['ttrss'] = array(
			'id' => 'ttrss',
			'section' => $this->gettext('ttrss'),
		);
		return $p;
	}
	/**
* Handler for preferences_list hook.
* Adds options blocks into TTRSS settings sections in Preferences.
*
* @param array Original parameters
*
* @return array Modified parameters
*/
	function ttrss_preferences_list($p)
	{
		$this->add_texts('localization/');
		if($p['section'] != 'ttrss') return $p;
		$urlV = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
		$usernameV = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
		$passwdV = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
		$url = new html_inputfield(array('name' => 'ttrss_url', 'type' => 'text', 'autocomplete' => 'off', 'value' => $urlV != '' ? $urlV : $this->rc->config->get('ttrss_url'), 'size' => 255));
		$username = new html_inputfield(array('name' => 'ttrss_username', 'type' => 'text', 'autocomplete' => 'off', 'value' => $usernameV != '' ? $usernameV : $this->rc->config->get('ttrss_username'), 'size' => 255));
		$passwd = new html_inputfield(array('name' => 'ttrss_passwd', 'type' => 'password', 'autocomplete' => 'off', 'value' => '', 'size' => 255));
		$p['blocks']['ttrss_preferences_section'] = array(
			'options' => array(
				array('title'=> rcube::Q($this->gettext('url')), 'content' => $url->show()),
				array('title'=> rcube::Q($this->gettext('username')), 'content' => $username->show()),
				array('title'=> rcube::Q($this->gettext('password')), 'content' => $passwd->show()),
			),
			'name' => rcube::Q($this->gettext('ttrss_settings'))
		);
		return $p;
	}
	/**
* Handler for preferences_save hook.
* Executed on TTRSS settings form submit.
*
* @param array Original parameters
*
* @return array Modified parameters
*/
	function ttrss_preferences_save($p)
	{
		$this->add_texts('localization/');
		if ($p['section'] == 'ttrss')
		{
			$rcmail = rcmail::get_instance();
			$url = rcube_utils::get_input_value('ttrss_url', rcube_utils::INPUT_POST);
			if(substr($url, strlen($url) - 1)!='/') $url .= '/';
			$username = rcube_utils::get_input_value('ttrss_username', rcube_utils::INPUT_POST);
			$passwd = rcube_utils::get_input_value('ttrss_passwd', rcube_utils::INPUT_POST);
			if($passwd == '') $passwd = $this->decrypt($this->rc->config->get('ttrss_passwd'));
			$p['prefs'] = array(
				'ttrss_url'  => $url,
				'ttrss_username'  => $username,
				'ttrss_passwd'    => $this->encrypt($passwd),
			);
		}
		return $p;
	}
	/**
* Plugin environment initialization.
*/
	function load_env()
	{
		if($this->env_loaded)
			return;
		$this->env_loaded = true;
		// load the TTRSS plugin configuration
		$this->load_config();
		// include localization (if wasn't included before)
		$this->add_texts('localization/');
	}
	/**
* Plugin UI initialization.
*/
	function load_ui()
	{
		$this->load_env();
	}
	/**
* Encrypt a passwort (key: IMAP-password)
*
* @param string $passwd             Password as plain text
* @return string                    Encrypted password
*/
	private function encrypt($passwd)
	{
		$rcmail = rcmail::get_instance();
		$imap_password = $rcmail->decrypt($_SESSION['password']);
		while(strlen($imap_password)<24)
			$imap_password .= $imap_password;
		$imap_password = substr($imap_password, 0, 24);
		$deskey_backup = $rcmail->config->set('ttrss_des_key', $imap_password);
		$enc = $rcmail->encrypt($passwd, 'ttrss_des_key');
		$deskey_backup = $rcmail->config->set('ttrss_des_key', '');
		return $enc;
	}
	/**
* Decrypt a passwort (key: IMAP-password)
*
* @param string $passwd             Encrypted password
* @return string                    Passwort as plain text
*/
	private function decrypt($passwd)
	{
		$rcmail = rcmail::get_instance();
		$imap_password = $rcmail->decrypt($_SESSION['password']);
		while(strlen($imap_password)<24)
			$imap_password .= $imap_password;
		$imap_password = substr($imap_password, 0, 24);
		$deskey_backup = $rcmail->config->set('ttrss_des_key', $imap_password);
		$clear = $rcmail->decrypt($passwd, 'ttrss_des_key');
		$deskey_backup = $rcmail->config->set('ttrss_des_key', '');
		return $clear;
	}
}