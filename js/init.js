rcmail.addEventListener('init', function(evt) {
  ttrss.refresh();
  ttrss.refreshLabels();

  rcmail.register_command('checkmail', ttrss.refresh, true);
  rcmail.register_command('firstpage', ttrss.headlines.page.first, false);
  rcmail.register_command('nextpage', ttrss.headlines.page.next, false);
  rcmail.register_command('previouspage', ttrss.headlines.page.previous, false);
  rcmail.register_command('nextarticle', ttrss.article.next, false);
  rcmail.register_command('previousarticle', ttrss.article.previous, false);
  rcmail.register_command('open', ttrss.article.open, false);
  rcmail.register_command('forward', ttrss.article.forward, false);
  rcmail.register_command('feed_subscribe', null, true);
  rcmail.register_command('feed_unsubscribe', null, false);

  // create custom button
  // var button = $('<A>').attr('id', 'rcmSampleButton').html(rcmail.gettext('buttontitle', 'sampleplugin'));
  // button.bind('click', function(e){ return rcmail.command('plugin.samplecmd', this); });
  // add and register
  // rcmail.add_element(button, 'toolbar');
  // rcmail.register_button('plugin.samplecmd', 'rcmSampleButton', 'link');
});