rcmail.addEventListener('init', function(evt){
  $('body').on('keydown', function(e){ if(ttrss.keyboard.onKeyDown(e)){ return true; }else{ e.preventDefault(); return false; } });
  ttrss.tree.load();
  ttrss.labels.load();
  ttrss.categories.load();
  ttrss.headlines.reload();
  ttrss.favico = new Favico({
    animation:'fade',
    bgColor:'#ff6f00'
  });
  ttrss.nameurl = document.title;

  rcmail.register_command('checkmail', ttrss.refresh, true);
  rcmail.register_command('firstpage', ttrss.headlines.page.first, false);
  rcmail.register_command('nextpage', ttrss.headlines.page.next, false);
  rcmail.register_command('previouspage', ttrss.headlines.page.previous, false);
  rcmail.register_command('nextarticle', ttrss.article.next, false);
  rcmail.register_command('previousarticle', ttrss.article.previous, false);
  rcmail.register_command('open', ttrss.article.open, false);
  rcmail.register_command('forward', ttrss.article.forward, false);

  rcmail.register_command('feed_subscribe', ttrss.feed.subscription.add.show, false);
  rcmail.register_command('feed_unsubscribe', null, false);

  rcmail.register_command('select-all', ttrss.article.select.all, false);
  rcmail.register_command('select-unread', ttrss.article.select.unread, false);
  rcmail.register_command('select-flagged', ttrss.article.select.flagged, false);
  rcmail.register_command('select-invert', ttrss.article.select.invert, false);
  rcmail.register_command('select-none', ttrss.article.select.none, false);
});
rcmail.addEventListener('plugin.ttrss_refresh', function(evt){
  ttrss.tree.counters(evt.unread, true);
});