ttrss.headlines = {
  load: function(id, view_mode, offset, is_cat, el){
    ttrss.article.select.start = null;
    if(is_cat===undefined) is_cat = 'true';
    if(offset===undefined||offset===null||isNaN(offset)) offset = 1;
    ttrss.currentPage = offset;
    if($('#mailboxlist #' + el).hasClass('unread')) view_mode = 'unread';
    if(view_mode===undefined||view_mode===null) view_mode = '';
    $('.pagenav.toolbar .pagenav-text').html(rcmail.gettext('loading', 'ttrss'));
    $('#messagelist-content').html('');
    var rmid = rcmsg.render(rcmail.gettext('loadheadlines', 'ttrss'), 'loading');
    $('#messagelist-content').load(
      './?_task=ttrss&_action=getHeadlines&id=' + id + '&view_mode=' + view_mode + '&offset=' + offset + '&is_cat=' + is_cat,
      function(){
        rcmsg.remove(rmid);
        ttrss.headlines.loadfunc(offset);
      }
    );
    locStore.set('ttrss.last.headlines', id);
    locStore.set('ttrss.last.headlines.view_mode', view_mode);
    locStore.set('ttrss.last.headlines.offset', offset);
    $('#mailboxlist .selected').removeClass('selected');
    rcmail.enable_command('feed_unsubscribe', false);
    if(el!==undefined&&el!==null){
      locStore.set('ttrss.last.headlines.el', el);
      $('#mailboxlist #' + el).addClass('selected');
      if($('#mailboxlist #' + locStore.get('ttrss.last.headlines.el')).hasClass('feed')) rcmail.enable_command('feed_unsubscribe', true);
    }else{
      $('#mailboxlist #' + locStore.get('ttrss.last.headlines.el')).addClass('selected');
    }
  },
  loadfunc: function(page){
    if(ttrss.article.selectPending!==null){
      switch(ttrss.article.selectPending){
        case 'next': ttrss.article.first(); break;
        case 'previous': ttrss.article.last(); break;
      }
      ttrss.article.selectPending = null;
    }else if(ttrss.article.focusPending){
      ttrss.article.selectPending = null;
    }else if($('#trsHL' + ttrss.article.currentID).length){
      $('#trsHL' + ttrss.article.currentID).addClass('selected expended focused');
      ttrss.scrollToElement(document.getElementById('trsHL' + ttrss.article.currentID), document.getElementById('messagelist-content'));
    }else{
      ttrss.article.focus.first();
    }
    ttrss.article.select.toggleMenu(false);
    if(page==1){
      rcmail.enable_command('firstpage', false);
      rcmail.enable_command('previouspage', false);
    }else{
      rcmail.enable_command('firstpage', true);
      rcmail.enable_command('previouspage', true);
    }
    var userlimit = rcmail.env.ttrss_pagesize;
    var limit = userlimit;
    var offset = (limit * page) + 1;
    offset = offset - userlimit;
    var counter = $('#messagelist tbody tr').length;
    if(counter===0) ttrss.article.select.toggleMenu(false);
    else ttrss.article.select.toggleMenu(true);
    if(counter===0&&offset==1){
      $('.pagenav.toolbar .pagenav-text').html('Feeds is empty');
      rcmail.enable_command('firstpage', false);
    }else if(counter!==0){
      $('.pagenav.toolbar .pagenav-text').html(page + ' - ' + offset + ' ' + rcmail.gettext('of', 'ttrss') + ' ' + (offset + counter - 1));
    }else if(counter===0){
      $('.pagenav.toolbar .pagenav-text').html('');
      $('messagelist-header .toolbar.listing.iconized .button.select').removeClass('active');
    }else{
      $('.pagenav.toolbar .pagenav-text').html('');
    }
    if(counter<userlimit) rcmail.enable_command('nextpage', false);
    else rcmail.enable_command('nextpage', true);
    ttrss.article.select.checkEvent();
  },
  page:{
    first: function(){
      if(locStore.get('ttrss.last.headlines.offset')<1){
        locStore.set('ttrss.last.headlines.offset', 1);
        ttrss.headlines.reload();
      }
    },
    next: function(){
      if(!$('.toolbar.pagenav a.button.nextpage').hasClass('disabled')){
        var offset = parseInt(locStore.get('ttrss.last.headlines.offset'), 10); offset++;
        locStore.set('ttrss.last.headlines.offset', offset);
        ttrss.headlines.reload();
      }
    },
    previous: function(){
      if(locStore.get('ttrss.last.headlines.offset')>1){
        var offset = parseInt(locStore.get('ttrss.last.headlines.offset'), 10); offset--;
        locStore.set('ttrss.last.headlines.offset', offset);
        ttrss.headlines.reload();
      }
    }
  },
  reload: function(){
    if(locStore.get('ttrss.last.headlines')!==null){
      ttrss.headlines.load(locStore.get('ttrss.last.headlines'), locStore.get('ttrss.last.headlines.view_mode'), locStore.get('ttrss.last.headlines.offset'), locStore.get('ttrss.last.headlines.el'));
    }
  }
};