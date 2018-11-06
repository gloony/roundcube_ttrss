ttrss.headlines = {
  load: function(id, view_mode, offset, is_cat, el){
    if(is_cat===undefined) is_cat = 'true';
    if(offset===undefined||offset===null||isNaN(offset)) offset = 1;
    ttrss.currentPage = offset;
    if(view_mode===undefined||view_mode===null) view_mode = '';
    $('.pagenav.toolbar .pagenav-text').html('Loading');
    $('#messagelist-content').html('');
    $('#messagelist-content').load('./?_task=ttrss&_action=getHeadlines&id=' + id + '&view_mode=' + view_mode + '&offset=' + offset + '&is_cat=' + is_cat, function(){ ttrss.headlines.loadfunc(offset); });
    locStore.set('ttrss.last.headlines', id);
    locStore.set('ttrss.last.headlines.view_mode', view_mode);
    locStore.set('ttrss.last.headlines.offset', offset);
    $('#mailboxlist .selected').removeClass('selected');
    if(el!==undefined&&el!==null){
      locStore.set('ttrss.last.headlines.el', el);
      $('#mailboxlist #' + el).addClass('selected');
    }
  },
  loadfunc: function(page){
    if(ttrss.article.selectPending!==null){
      switch(ttrss.article.selectPending){
        case 'next': ttrss.article.first(); break;
        case 'previous': ttrss.article.last(); break;
      }
      ttrss.article.selectPending = null;
    }
    $('#trsHL' + ttrss.article.currentID).addClass('selected expended focused');
    rcmail.enable_command('select-all', false);
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
    if(counter===0) rcmail.enable_command('select-all', false);
    else rcmail.enable_command('select-all', true);
    if(counter===0&&offset==1){
      $('.pagenav.toolbar .pagenav-text').html('Feeds is empty');
      rcmail.enable_command('firstpage', false);
    }else if(counter!==0){
      $('.pagenav.toolbar .pagenav-text').html(page + ' - ' + offset + ' of ' + (offset + counter - 1));
    }else if(counter===0){
      $('.pagenav.toolbar .pagenav-text').html('');
      $('messagelist-header .toolbar.listing.iconized .button.select').removeClass('active');
    }else{
      $('.pagenav.toolbar .pagenav-text').html('');
    }
    if(counter<userlimit) rcmail.enable_command('nextpage', false);
    else rcmail.enable_command('nextpage', true);
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