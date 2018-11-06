$(document).ready(function(){
  if(window.rcmail){
    if($(".button-ttrss").length){
      if(rcmail.env.task=='ttrss') window.setInterval(updateBadgeTTRSS, 60000);
      else window.setInterval(updateBadgeTTRSS, 120000);
      updateBadgeTTRSS();
    }
    if(rcmail.env.task=='ttrss'){
      faviconTTRSS = new Favico({animation:'fade',bgColor:'#ff6f00'});
    }
  }
});
var lastTTRSSCount = null;
var firstTTRSSTitle = null;
var faviconTTRSS = null;
function updateBadgeTTRSS(){
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
        if(localStorage.ttrss_unreads===undefined) localStorage.setItem('ttrss_unreads', 0);
        if(localStorage.ttrss_unreads<data){
          localStorage.setItem('ttrss_unreads', data);
          var title = 'gNews';
          var icon = 'plugins/ttrss/skins/elastic/asset/logo.png';
          var body = 'You have ' + data;
          if(data>1) body += ' unread articles';
          else body += ' unread article';
          if(Notification.permission === "granted"){
            var notification = new Notification(title, { icon: icon, body: body });
            notification.onclick = function(){ window.focus(); this.close(); };
          }else Notification.requestPermission();
        }else if(localStorage.ttrss_unreads>data){
          localStorage.setItem('ttrss_unreads', data);
        }
        if(rcmail.env.task=='ttrss'){
          if(firstTTRSSTitle===null) firstTTRSSTitle = document.title;
          if(data!==null) document.title = "(" + data + ") " + firstTTRSSTitle;
          else document.title = firstTTRSSTitle;
          if(data===null) faviconTTRSS.badge(0);
          else faviconTTRSS.badge(data);
          ttrss.loadLastFeeds();
        }
      }
    },
  });
}