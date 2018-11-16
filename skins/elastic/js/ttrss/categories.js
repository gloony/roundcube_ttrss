ttrss.categories = {
  load: function(){
    var rmid = rcmsg.render(rcmail.gettext('loadcategory', 'ttrss'), 'loading');
    $('#subscribe-cat').load('./?_task=ttrss&_action=getCategories&mode=true', function(){
      rcmsg.remove(rmid);
      rcmail.enable_command('feed_subscribe', true);
    });
  }
};