ttrss.article = {
  currentFeedID: null,
  currentID: null,
  selectPending: null,
  toggle: {
    fullscreen: function(force){
      if(force===undefined) force = !($('body>#layout>div.content .iframe-wrapper').css('position')=='fixed');
      if(force) $('body>#layout>div.content .iframe-wrapper').css('position', 'fixed');
      else $('body>#layout>div.content .iframe-wrapper').css('position', 'initial');
    },
    label: function(id_label, mode){
      if(mode===undefined) mode = '';
      else{
        if(mode===null) mode = '';
        else if(mode) mode = '&mode=' + 1;
        else if(!mode) mode = '&mode=' + 0;
        else mode = '&mode=' + mode;
      }
      var id_article = $('#messagelist-content tr.selected').attr('id');
      if(id_article!==undefined){
        id_article = id_article.substring(5);
        var rmid = rcmsg.render('Update label on article(s) ...', 'loading');
        $.ajax({ url: './?_task=ttrss&_action=setArticleLabel&id_article=' + id_article + '&id_label=' + id_label + mode })
          .done(function(html){ rcmsg.remove(rmid); ttrss.headlines.reload(); });
      }
    },
    read: function(id, mode){
      if(mode===undefined){
        mode = '';
      }else{
        if(mode===null) mode = '';
        else if(mode) mode = '&mode=' + 1;
        else if(!mode) mode = '&mode=' + 0;
        else mode = '&mode=' + mode;
      }
      switch(mode){
        case '&mode=0': $('#trsHL' + id).removeClass('unread'); break;
        case '&mode=1': $('#trsHL' + id).addClass('unread'); break;
        case '&mode=2': case '': $('#trsHL' + id).toggleClass('unread'); break;
      }
      var rmid = rcmsg.render('Mark article(s) ...', 'loading');
      $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=2' + mode })
        .done(function(html){ rcmsg.remove(rmid); ttrss.tree.counters(); });
    },
    star: function(id, mode){
      if(mode===undefined) mode = '';
      else{
        if(mode===null) mode = '';
        else if(mode) mode = '&mode=' + 1;
        else if(!mode) mode = '&mode=' + 0;
        else mode = '&mode=' + mode;
      }
      $('#trsHL' + id).toggleClass('flagged');
      $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').toggleClass('unflagged');
      $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').toggleClass('flagged');
      var rmid = rcmsg.render('Mark article(s) ...', 'loading');
      $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=0' + mode })
        .done(function(html){ rcmsg.remove(rmid); ttrss.tree.counters(); });
    }
  },
  first: function(){
    var id = $('#messagelist-content tbody tr:first').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }
  },
  forward: function(){
    rcmail.goto_url('mail/compose', { _ttrss_feed: ttrss.article.currentID }, true);
  },
  last: function(){
    var id = $('#messagelist-content tbody tr:last').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }
  },
  load: function(id, feed_ids){
    rcmail.enable_command('nextarticle', true);
    rcmail.enable_command('previousarticle', true);
    rcmail.enable_command('open', true);
    rcmail.enable_command('forward', true);
    ttrss.article.currentID = id;
    ttrss.article.currentFeedID = feed_ids;
    $('#messagelist tbody tr.selected.expended.focused').removeClass('selected expended focused');
    $('#trsHL' + id).addClass('selected expended focused');
    ttrss.scrollToElement(document.getElementById('trsHL' + id), document.getElementById('messagelist-content'));
    $('#messagecontframe').attr('src', './?_task=ttrss&_action=getArticle&id=' + id);
    if(rcmail.env.ttrss_autoread)  $('#trsHL' + id).removeClass('unrea');
    var rmid = rcmsg.render('Load article ...', 'loading');
    $('#messagecontframe').on('load', function(){
      rcmsg.remove(rmid);
      ttrss.article.loadfunc();
    });
    locStore.set('trs.last.article.feed_ids', ttrss.currentPage);
  },
  loadfunc(){
    if(rcmail.env.ttrss_autoread){
      if($('#trsHL' + ttrss.article.currentID).hasClass('unread')) ttrss.article.toggle.read(ttrss.article.currentID, 0);
    }
  },
  next: function(){
    var id = $('#messagelist-content tr.selected').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    else id = $('#messagelist-content tr.selected').next('tr').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }else{
      ttrss.article.selectPending = 'next';
      ttrss.headlines.page.next();
    }
  },
  open: function(){
    if(ttrss.article.currentID!==null) window.open('./?_task=ttrss&_action=openLink&id=' + ttrss.article.currentID);
  },
  pageDown: function(){
    var id = $('#messagelist-content tr.selected').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    else{
      var counter = 0;
      id = $('#messagelist-content tr.selected');
      while(id!==undefined&&counter<10){
        id = $(id).next('tr');
        counter++;
      }
      id = $(id).attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    }
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }else{
      ttrss.headlines.page.next();
    }
  },
  pageUp: function(){
    var id = $('#messagelist-content tr.selected').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    else{
      var counter = 0;
      id = $('#messagelist-content tr.selected');
      while(id!==undefined&&counter<10){
        id = $(id).prev('tr');
        counter++;
      }
      id = $(id).attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    }
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }
  },
  previous: function(){
    var id = $('#messagelist-content tr.selected').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    else id = $('#messagelist-content tr.selected').prev('tr').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id, locStore.get('ttrss.last.article.feed_ids'));
    }else{
      ttrss.article.selectPending = 'previous';
      ttrss.headlines.page.previous();
    }
  },
  select: {
    all: function(){
		rcmail.enable_command('select-all', false);
    },
    unread: function(){

    },
    flagged: function(){

    },
    invert: function(){

    },
    none: function(){

    },
    toggle: function(mode){
      rcmail.enable_command('select-all', ttrss.article.select.all(), mode);
      rcmail.enable_command('select-unread', ttrss.article.select.unread(), mode);
      rcmail.enable_command('select-flagged', ttrss.article.select.flagged(), mode);
      rcmail.enable_command('select-invert', ttrss.article.select.invert(), mode);
    }
  }
};