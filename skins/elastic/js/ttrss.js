var ttrss = {
  refresh: function(){
    ttrss.loadLastFeeds();
    ttrss.loadLastHeadlines();
  },
  loadLastFeeds: function(){
    if(locStore.get('ttrss.last.feeds')!==null) ttrss.load.feeds(locStore.get('ttrss.last.feeds'));
    else ttrss.load.folder();
  },
  loadLastHeadlines: function(){
    if(locStore.get('ttrss.last.headlines')!==null){
      ttrss.load.headlines(locStore.get('ttrss.last.headlines'), locStore.get('ttrss.last.headlines.view_mode'), locStore.get('ttrss.last.headlines.offset'));
    }else{
      ttrss.load.headlines(-4);
    }
  },
  loadExpendedFeed: function(){
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
  },
  refreshLabels: function(){
    $('#threadselect-add ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=true', function(){ ttrss.after.labels(); });
    $('#threadselect-remove ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=false');
  },
  after: {
    headlines: function(page){
      if(ttrss.article.selectPending!==null){
        switch(ttrss.article.selectPending){
          case 'next': ttrss.article.first(); break;
          case 'previous': ttrss.article.last(); break;
        }
        ttrss.article.selectPending = null;
      }
      $('#trsHL' + ttrss.article.currentID).addClass('selected expended focused');
      if(page==1){
        rcmail.enable_command('firstpage', false);
        rcmail.enable_command('previouspage', false);
      }else{
        rcmail.enable_command('firstpage', true);
        rcmail.enable_command('previouspage', true);
      }
      var limit = 50;
      var offset = (limit * page) + 1;
      offset = offset - 50;
      var counter = $('#messagelist tbody tr').length;
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
      if(counter<50) rcmail.enable_command('nextpage', false);
      else rcmail.enable_command('nextpage', true);
    },
    labels: function(){
      $('#threadselect-add ul.toolbarmenu li a').each(function(){
        var id = $(this).attr('id');
        id = id.substr(6);
        document.styleSheets[0].addRule('#trsCAT' + id + ' a:before','color: ' + $('#trsLBL' + id).css('color') + ';');
      });
    }
  },
  load: {
    folder: function(){
      $('#mailboxlist').load('./?_task=ttrss&_action=getTree', function(){ ttrss.loadExpendedFeed(); $('#trsCAT' + locStore.get('ttrss.last.headlines')).addClass('selected'); });
      locStore.unset('ttrss.last.feeds');
    },
    feeds: function(id){
      $('#mailboxlist').load('./?_task=ttrss&_action=getFeeds&id=' + id, function(){ $('#trsCAT' + locStore.get('ttrss.last.headlines')).addClass('selected'); });
      locStore.set('ttrss.last.feeds', id);
    },
    headlines: function(id, view_mode, offset){
      if(offset===undefined||offset===null||isNaN(offset)) offset = 1;
      ttrss.currentPage = offset;
      if(view_mode===undefined||view_mode===null) view_mode = '';
      $('.pagenav.toolbar .pagenav-text').html('Loading');
      $('#messagelist-content').html('');
      $('#messagelist-content').load('./?_task=ttrss&_action=getHeadlines&id=' + id + '&view_mode=' + view_mode + '&offset=' + offset, function(){ ttrss.after.headlines(offset); });
      locStore.set('ttrss.last.headlines', id);
      locStore.set('ttrss.last.headlines.view_mode', view_mode);
      locStore.set('ttrss.last.headlines.offset', offset);
      $('#mailboxlist .selected').removeClass('selected');
      $('#trsCAT' + id).addClass('selected');
    },
    article: function(id, feed_ids){
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
      $('#trsHL' + id).removeClass('unread');
      $('#messagecontframe').on('load', function(){ ttrss.loadLastFeeds(); });
      locStore.set('trs.last.article.feed_ids', ttrss.currentPage);
    }
  },
  headlines: {
    page:{
      first: function(){
        if(locStore.get('ttrss.last.headlines.offset')<1){
          locStore.set('ttrss.last.headlines.offset', 1);
          ttrss.loadLastHeadlines();
        }
      },
      next: function(){
        if(!$('.toolbar.pagenav a.button.nextpage').hasClass('disabled')){
          var offset = parseInt(locStore.get('ttrss.last.headlines.offset'), 10); offset++;
          locStore.set('ttrss.last.headlines.offset', offset);
          ttrss.loadLastHeadlines();
        }
      },
      previous: function(){
        if(locStore.get('ttrss.last.headlines.offset')>1){
          var offset = parseInt(locStore.get('ttrss.last.headlines.offset'), 10); offset--;
          locStore.set('ttrss.last.headlines.offset', offset);
          ttrss.loadLastHeadlines();
        }
      }
    }
  },
  article: {
    currentID: null,
    currentFeedID: null,
    selectPending: null,
    toggle: {
      fullscreen: function(force){
        if(force===undefined) force = !($('body>#layout>div.content .iframe-wrapper').css('position')=='fixed');
        if(force) $('body>#layout>div.content .iframe-wrapper').css('position', 'fixed');
        else $('body>#layout>div.content .iframe-wrapper').css('position', 'initial');
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
        $('#trsHL' + id).toggleClass('unread');
        $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=2' + mode })
          .done(function(html){ ttrss.loadLastFeeds(); });
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
        $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=0' + mode })
          .done(function(html){ ttrss.loadLastFeeds(); });
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
          $.ajax({ url: './?_task=ttrss&_action=setArticleLabel&id_article=' + id_article + '&id_label=' + id_label + mode })
            .done(function(html){ ttrss.loadLastHeadlines(); });
        }
      }
    },
    open: function(){
      if(ttrss.article.currentID!==null) window.open('./?_task=ttrss&_action=openLink&id=' + ttrss.article.currentID);
    },
    forward: function(){
      rcmail.goto_url('mail/compose', { _ttrss_feed: ttrss.article.currentID }, true);
    },
    first: function(){
      var id = $('#messagelist-content tbody tr:first').attr('id');
      if(id!==undefined){
        id = id.substring(5);
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }
    },
    last: function(){
      var id = $('#messagelist-content tbody tr:last').attr('id');
      if(id!==undefined){
        id = id.substring(5);
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }
    },
    next: function(){
      var id = $('#messagelist-content tr.selected').attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
      else id = $('#messagelist-content tr.selected').next('tr').attr('id');
      if(id!==undefined){
        id = id.substring(5);
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }else{
        ttrss.article.selectPending = 'next';
        ttrss.headlines.page.next();
      }
    },
    previous: function(){
      var id = $('#messagelist-content tr.selected').attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
      else id = $('#messagelist-content tr.selected').prev('tr').attr('id');
      if(id!==undefined){
        id = id.substring(5);
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }else{
        ttrss.article.selectPending = 'previous';
        ttrss.headlines.page.previous();
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
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }
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
        ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
      }else{
        ttrss.headlines.page.next();
      }
    }
  },
  feed: {
    collapse: function(id, force){
      if(!isNaN(id)) return;
      if($('#' + id + ' div.treetoggle')===undefined) return;
      if(force===undefined) force = $('#' + id + ' div.treetoggle').hasClass('collapsed');
      var cur = locStore.get('ttrss.feed.expended');
      var find = id + ', ';
      if(force){
        $('#' + id + ' div.treetoggle').removeClass('collapsed');
        $('#' + id + ' div.treetoggle').addClass('expanded');
        $('#' + id + ' ul#sub' + id).removeClass('hidden');
        $('#' + id).attr("aria-expanded", "true");
        if(cur===null) cur = find;
        else if(cur.search(find)==-1) cur += find;
        if(locStore.get('ttrss.feed.expended')!=cur&&cur!==null) locStore.set('ttrss.feed.expended', cur);
      }else{
        $('#' + id + ' div.treetoggle').addClass('collapsed');
        $('#' + id + ' div.treetoggle').removeClass('expanded');
        $('#' + id + ' ul#sub' + id).addClass('hidden');
        $('#' + id).attr("aria-expanded", "false");
        var reg = new RegExp(find, 'g');
        cur = cur.replace(reg, '');
        if(cur==='') locStore.unset('ttrss.feed.expended');
        else locStore.set('ttrss.feed.expended', cur);
      }
    }
  },
  scrollToElement: function(element, container){
    if(element===undefined || element===null) return;
    if(element.offsetTop < container.scrollTop){
      container.scrollTop = element.offsetTop;
    }else{
      var offsetBottom = element.offsetTop + element.offsetHeight;
      var scrollBottom = container.scrollTop + container.offsetHeight;
      if(offsetBottom > scrollBottom){
        container.scrollTop = offsetBottom - container.offsetHeight;
      }
    }
  }
};