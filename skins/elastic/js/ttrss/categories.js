ttrss.categories = {
  load: function(){
    var rmid = rcmsg.render('Load categorie(s) ...', 'loading');
    $('#subscribe-cat').load('./?_task=ttrss&_action=getCategories&mode=true', function(){
      rcmsg.remove(rmid);
      rcmail.enable_command('feed_subscribe', true);
    });
  }
};