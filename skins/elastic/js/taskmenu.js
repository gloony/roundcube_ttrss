rcmail.addEventListener('init', function(evt){
  if(window.rcmail){
    if($(".button-ttrss").length){
      if(rcmail.env.task=='ttrss') window.setInterval(ttrss_badge, 60000);
      else window.setInterval(ttrss_badge, 120000);
      ttrss_badge();
    }
  }
});
rcmail.addEventListener('plugin.refresh_ttrss', function(evt){ ttrss_badge(); });
var lastTTRSSCount = null;
function ttrss_badge(){
  $.ajax({
    type: "POST",
    url: "/?_task=ttrss&_action=getUnread",
    data: "",
    xhrFields: {
      withCredentials: true
    },
    success: function (data) {
      if(isNaN(data)) document.location.href = document.location.href;
      if(data==0) data = null;
      if(lastTTRSSCount!=data){
        lastTTRSSCount = data;
        $(".button-ttrss").attr('data-badge', data);
        if(localStorage.getItem('ttrss.unread.counter')===undefined) localStorage.setItem('ttrss.unread.counter', 0);
        if(localStorage.getItem('ttrss.unread.counter')<data){
          localStorage.setItem('ttrss.unread.counter', data);
          var title = 'gNews';
          var icon = 'plugins/ttrss/skins/elastic/asset/logo.png';
          var body = 'You have ' + data;
          if(data>1) body += ' unread articles';
          else body += ' unread article';
          if(Notification.permission === "granted"){
            var notification = new Notification(title, { icon: icon, body: body });
            notification.onclick = function(){ window.focus(); this.close(); };
          }else Notification.requestPermission();
        }else if(localStorage.getItem('ttrss.unread.counter')>data){
          localStorage.setItem('ttrss.unread.counter', data);
        }
      }
    },
  });
}