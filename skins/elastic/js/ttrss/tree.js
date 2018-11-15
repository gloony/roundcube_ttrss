ttrss.tree = {
  icount: 0,
  counters: function(count){
    var refresh = false;
    if(count===undefined) refresh = true;
    else if(ttrss.tree.icount!=count){
      ttrss.tree.icount = count;
      refresh = true;
    }
    if(refresh){
      var rmid = rcmsg.render('Count unread item(s) ...', 'loading');
      $.ajax({ url: './?_task=ttrss&_action=getCounters' })
        .done(function(json){ rcmsg.remove(rmid); ttrss.tree.countersfunc(json); });
    }
  },
  countersfunc: function(counters){
    json = JSON.parse(counters);
    for(var i = 0; i < json.length; i++){
      if(json[i].id=='global-unread'){
        if(locStore.get('ttrss.unread.counter')===null) locStore.set('ttrss.unread.counter', 0);
        ttrss.tree.icount = json[i].counter;
        if(json[i].counter!==locStore.get('ttrss.unread.counter')){
          ttrss.favico.badge(json[i].counter);
          if(json[i].counter!==0){
            // $(".button-ttrss").attr('data-badge', json[i].counter);
            document.title = "(" + json[i].counter + ") " + ttrss.nameurl;
          }else{
            // $(".button-ttrss").attr('data-badge', null);
            document.title = ttrss.nameurl;
          }
          if(json[i].counter>locStore.get('ttrss.unread.counter')){
            var title = 'TTRSS';
            var icon = 'plugins/ttrss/skins/elastic/asset/logo.png';
            var body = 'You have ' + json[i].counter;
            if(json[i].counter>1) body += ' unread articles';
            else body += ' unread article';
            if(Notification.permission === "granted"){
              if(ttrss.notification!==null) ttrss.notification.close();
              ttrss.notification = new Notification(title, { icon: icon, body: body });
              ttrss.notification.onclick = function(){ window.focus(); parent.focus(); this.close(); };
            }else Notification.requestPermission();
          }
          locStore.set('ttrss.unread.counter', json[i].counter);
        }
      }
      if(json[i].counter!==0&&json[i].counter!=='0'){
        $('li[data-id="' + json[i].id + '"]').addClass('unread');
        $('li[data-id="' + json[i].id + '"] span.unreadcount').html(json[i].counter);
      }else{
        $('li[data-id="' + json[i].id + '"]').removeClass('unread');
        $('li[data-id="' + json[i].id + '"] span.unreadcount').html('');
      }
    }
  },
  load: function(){
    var rmid = rcmsg.render('Load tree ...', 'loading');
    $('#mailboxlist').load('./?_task=ttrss&_action=getTree', function(){
      rcmsg.remove(rmid);
      ttrss.tree.loadfunc();
    });
    locStore.unset('ttrss.last.feeds');
  },
  loadfunc: function(){
    if(locStore.get('ttrss.feed.expended')!==null){
      var s = locStore.get('ttrss.feed.expended');
      if(s.search(', ')!=-1){
        var match = s.split(', ');
        for(var a in match){
          var id = match[a];
          if(id!==null&&id!=='null'&&id!=='ttrss.feed.expended') ttrss.feed.collapse(id, true);
        }
      }else locStore.unset('ttrss.feed.expended');
    }
    $('#mailboxlist #' + locStore.get('ttrss.last.headlines.el')).addClass('selected');
    if($('#mailboxlist #' + locStore.get('ttrss.last.headlines.el')).hasClass('feed')) rcmail.enable_command('feed_unsubscribe', true);
    else rcmail.enable_command('feed_unsubscribe', false);
    ttrss.tree.counters();
  }
};