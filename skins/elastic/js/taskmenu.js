var ttrss_lastunread = null;
function ttrss_badge(data){
  if(data==0) data = null;
  if(ttrss_lastunread!=data){
    ttrss_lastunread = data;
    if($(".button-ttrss").length) $(".button-ttrss").attr('data-badge', data);
    if(localStorage.getItem('ttrss.unread.counter')===undefined) localStorage.setItem('ttrss.unread.counter', 0);
    if(localStorage.getItem('ttrss.unread.counter')<data){
      localStorage.setItem('ttrss.unread.counter', data);
      var title = 'TTRSS';
      var icon = 'plugins/ttrss/skins/elastic/asset/logo.png';
      var body = rcmail.gettext('youhave', 'ttrss') + ' ' + data + ' ';
      if(data>1) body += rcmail.gettext('unreadarticles', 'ttrss');
      else body += rcmail.gettext('unreadarticle', 'ttrss');
      if(Notification.permission === "granted"){
        var notification = new Notification(title, { icon: icon, body: body });
        notification.onclick = function(){ window.document.location.href = './?_task=ttrss'; window.focus(); this.close(); };
      }else Notification.requestPermission();
    }else if(localStorage.getItem('ttrss.unread.counter')>data){
      localStorage.setItem('ttrss.unread.counter', data);
    }
  }
}

rcmail.addEventListener('init', function(evt){ rcmail.refresh(); });
rcmail.addEventListener('plugin.ttrss_refresh', function(evt){ ttrss_badge(evt.unread); });