ttrss.article = {
  currentID: null, currentFeedID: null,
  focusPending: null, selectPending: null,
  focus: {
    first: function(){
      var id = $('#messagelist-content tbody tr:first').attr('id');
      if(id!==undefined){
        $('#messagelist-content tr.focused').removeClass('focused');
        $('#messagelist-content tr#' + id).addClass('focused');
        ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
      }
    },
    last: function(){
      var id = $('#messagelist-content tbody tr:last').attr('id');
      if(id!==undefined){
        $('#messagelist-content tr.focused').removeClass('focused');
        $('#messagelist-content tr#' + id).addClass('focused');
        ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
      }
    },
    next: function(){
      var id = $('#messagelist-content tr.focused').attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
      else id = $('#messagelist-content tr.focused').next('tr').attr('id');
      if(id!==undefined){
        $('#messagelist-content tr.focused').removeClass('focused');
        $('#messagelist-content tr#' + id).addClass('focused');
        ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
      }else{
        // ttrss.article.focusPending = 'next';
        // ttrss.headlines.page.next();
      }
    },
    open: function(){
      var id = $('#messagelist-content tr.focused').attr('id');
      id = id.substring(5);
      ttrss.article.load(id);
    },
    previous: function(){
      var id = $('#messagelist-content tr.focused').attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
      else id = $('#messagelist-content tr.focused').prev('tr').attr('id');
      if(id!==undefined){
        $('#messagelist-content tr.focused').removeClass('focused');
        $('#messagelist-content tr#' + id).addClass('focused');
        ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
      }else{
        // ttrss.article.focusPending = 'previous';
        // ttrss.headlines.page.previous();
      }
    },
    select: function(){
      var id = $('#messagelist-content tr.focused').attr('id');
      ttrss.article.select.id(id);
    }
  },
  toggle: {
    fullscreen: function(force){
      if(force===undefined) force = !($('body>#layout>div.content .iframe-wrapper').css('position')=='fixed');
      if(force) $('body>#layout>div.content .iframe-wrapper').css('position', 'fixed');
      else $('body>#layout>div.content .iframe-wrapper').css('position', 'initial');
    },
    label: function(id_label, mode, selected){
      if(selected===undefined) selected = true;
      if(mode===undefined) mode = '';
      else{
        if(mode===null) mode = '';
        else if(mode) mode = '&mode=' + 1;
        else if(!mode) mode = '&mode=' + 0;
        else mode = '&mode=' + mode;
      }
      if(selected){
        var tid, id_article = '';
        $('#messagelist-content tr.selected').each(function(){
          if(id_article!=='') id_article += ',';
          tid = $(this).attr('id');
          tid = tid.substring(5);
          id_article += tid;
          // switch(mode){
          //   case 0: $(this).removeClass('unread'); break;
          //   case 1: $(this).addClass('unread'); break;
          //   case 2: case '': $(this).toggleClass('unread'); break;
          // }
        });
      }else{
        var id_article = $('#messagelist-content tr.focused').attr('id');
        id_article = id_article.substring(5);
      }
      if(id_article!==undefined&&id_article!==''){
        var rmid = rcmsg.render(rcmail.gettext('loadupdatelabel', 'ttrss'), 'loading');
        $.ajax({ url: './?_task=ttrss&_action=setArticleLabel&id_article=' + id_article + '&id_label=' + id_label + mode })
          .done(function(html){ rcmsg.remove(rmid); ttrss.tree.counters(); ttrss.headlines.reload(); });
      }
    },
    read: function(id, mode, selected){
      if(selected===undefined) selected = true;
      if(id===undefined||id===null||id==='') selected = true;
      else if(selected&&!$('#trsHL' + id).hasClass('selected')) selected = false;
      if(selected===undefined) selected = true;
      if(mode===undefined) mode = 2;
      if(mode===true||mode===1||mode==='1') mode = 1;
      else if(mode===false||mode===0||mode==='0') mode = 0;
      else if(selected&&mode==2){
        id = $('#messagelist-content tr.selected').attr('id');
        id = id.substring(5);
        if($('#trsHL' + id).hasClass('unread')) mode = 0;
        else mode = 1;
      }
      if(selected){
        var tid; id = '';
        $('#messagelist-content tr.selected').each(function(){
          if(id!=='') id += ',';
          tid = $(this).attr('id');
          tid = tid.substring(5);
          id += tid;
          switch(mode){
            case 0: $(this).removeClass('unread'); break;
            case 1: $(this).addClass('unread'); break;
            case 2: case '': $(this).toggleClass('unread'); break;
          }
        });
      }else{
        if(id===undefined||id===null){
          id = $('#messagelist-content tr.focused').attr('id');
          id = id.substring(5);
        }
        switch(mode){
          case 0: $('#trsHL' + id).removeClass('unread'); break;
          case 1: $('#trsHL' + id).addClass('unread'); break;
          case 2: case '': $('#trsHL' + id).toggleClass('unread'); break;
        }
      }
      var rmid = rcmsg.render(rcmail.gettext('loadmarkarticle', 'ttrss'), 'loading');
      $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=2&mode=' + mode })
        .done(function(html){ rcmsg.remove(rmid); ttrss.tree.counters(); });
    },
    star: function(id, mode, selected){
      if(selected===undefined) selected = true;
      if(id===undefined||id===null||id==='') selected = true;
      else if(selected&&!$('#trsHL' + id).hasClass('selected')) selected = false;
      if(selected===undefined) selected = true;
      if(mode===undefined||mode===null||mode===''||mode===2){
        if(selected){
          id = $('#messagelist-content tr.selected').attr('id');
          id = id.substring(5);
          if($('#trsHL' + id).hasClass('flagged')) mode = false;
          else mode = true;
        }else{
          mode = null;
        }
      }
      if(mode===null) mode = '';
      else if(mode) mode = '&mode=' + 1;
      else if(!mode) mode = '&mode=' + 0;
      else mode = '&mode=' + mode;
      if(selected){
        var tid; id = '';
        $('#messagelist-content tr.selected').each(function(){
          if(id!=='') id += ',';
          tid = $(this).attr('id');
          tid = tid.substring(5);
          id += tid;
          switch(mode){
            case '&mode=1':
              $('#trsHL' + tid).addClass('flagged');
              $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').removeClass('unflagged');
              $('#trsHL' + tid + ' .flag #flagicnrcmrowOTE').addClass('flagged');
              break;
            case '&mode=0':
              $('#trsHL' + tid).removeClass('flagged');
              $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').addClass('unflagged');
              $('#trsHL' + tid + ' .flag #flagicnrcmrowOTE').removeClass('flagged');
              break;
          }
        });
      }else{
        $('#trsHL' + id).toggleClass('flagged');
        $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').toggleClass('unflagged');
        $('#trsHL' + id + ' .flag #flagicnrcmrowOTE').toggleClass('flagged');
      }
      var rmid = rcmsg.render(rcmail.gettext('loadmarkarticle', 'ttrss'), 'loading');
      $.ajax({ url: './?_task=ttrss&_action=updateArticle&id=' + id + '&field=0' + mode })
        .done(function(html){ rcmsg.remove(rmid); ttrss.tree.counters(); });
    }
  },
  select: {
    start: null,
    id: function(id){
      ttrss.watermark();
      $('#messagelist-content tr#' + id).toggleClass('selected');
      ttrss.article.select.start = id;
    },
    upper: {
      click: function(id){
        ttrss.watermark();
        if(ttrss.article.select.start===null){
          ttrss.article.select.id(id);
        }else{
          if(id===ttrss.article.select.start){
            $('#messagelist-content tr').removeClass('selected');
            $('#messagelist-content tr#trsHL' + id).addClass('selected');
          }else{
            var tID, bID = false, cID = false;
            $('#messagelist-content tr').each(function(){
              tID = $(this).attr('id'); tID = tID.substring(5);
              if(id===tID&&!bID&&!cID){
                $(this).addClass('selected'); cID = true;
              }else if(tID===ttrss.article.select.start&&!bID&&!cID){
                $(this).addClass('selected'); bID = true;
              }else if(tID===id&&bID&&!cID){
                $(this).addClass('selected'); cID = true;
              }else if(tID===ttrss.article.select.start&&!bID&&cID){
                $(this).addClass('selected'); bID = true;
              }else if(bID&&!cID) $(this).addClass('selected');
              else if(!bID&&cID) $(this).addClass('selected');
              else $(this).removeClass('selected');
            });
          }
        }
        $('#messagelist-content tr.focused').removeClass('focused');
        $('#messagelist-content tr#trsHL' + id).addClass('focused');
      },
      first: function(){
        var id = $('#messagelist-content tbody tr:first').attr('id');
        if(id!==undefined){
          id = id.substring(5);
          ttrss.article.select.upper.click(id);
        }
      },
      last: function(){
        var id = $('#messagelist-content tbody tr:last').attr('id');
        if(id!==undefined){
          id = id.substring(5);
          ttrss.article.select.upper.click(id);
        }
      },
      next: function(){
        var id = $('#messagelist-content tr.focused').attr('id');
        if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
        else id = $('#messagelist-content tr.focused').next('tr').attr('id');
        if(id!==undefined){
          ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
          id = id.substring(5);
          ttrss.article.select.upper.click(id);
        }
      },
      previous: function(){
        var id = $('#messagelist-content tr.focused').attr('id');
        if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
        else id = $('#messagelist-content tr.focused').prev('tr').attr('id');
        if(id!==undefined){
          ttrss.scrollToElement(document.getElementById(id), document.getElementById('messagelist-content'));
          id = id.substring(5);
          ttrss.article.select.upper.click(id);
        }
      }
    },
    all: function(){
      $('#messagelist-content tbody tr').each(function(){
        $(this).addClass('selected');
      });
    },
    unread: function(){
      $('#messagelist-content tbody tr').each(function(){
        if($(this).hasClass('unread')) $(this).addClass('selected');
      });
    },
    invert: function(){
      $('#messagelist-content tbody tr').each(function(){
        $(this).toggleClass('selected');
      });
    },
    none: function(){
      $('#messagelist-content tbody tr').each(function(){
        $(this).removeClass('selected');
      });
    },
    checkEvent: function(){
      $('#messagelist tr td input:checkbox').on('click', function(){
        if($(this).prop('checked')) $(this).parent().parent().addClass('selected');
        else $(this).parent().parent().removeClass('selected');
      });
    },
    toggleMenu: function(mode){
      rcmail.enable_command('select-all', mode);
      rcmail.enable_command('select-unread', mode);
      rcmail.enable_command('select-invert', mode);
      rcmail.enable_command('select-none', mode);
    }
  },
  click: function(id, e){
    if(e.ctrlKey){
      ttrss.article.select.start = id;
      ttrss.article.select.id('trsHL' + id);
      $('#messagelist-content tr.focused').removeClass('focused');
      $('#messagelist-content tr#trsHL' + id).addClass('focused');
    }else if(e.shiftKey){
      ttrss.article.select.upper.click(id);
    }else{
      ttrss.article.select.start = id;
      ttrss.article.load(id);
    }
  },
  first: function(){
    var id = $('#messagelist-content tbody tr:first').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }
  },
  forward: function(){
    rcmail.goto_url('mail/compose', { _ttrss_feed: ttrss.article.currentID }, true);
  },
  last: function(){
    var id = $('#messagelist-content tbody tr:last').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }
  },
  load: function(id, feed_ids){
    rcmail.enable_command('nextarticle', true);
    rcmail.enable_command('previousarticle', true);
    rcmail.enable_command('open', true);
    rcmail.enable_command('forward', true);
    ttrss.article.currentID = id;
    ttrss.article.select.start = id;
    if(feed_ids===undefined) feed_ids = locStore.get('trs.last.article.feed_ids');
    ttrss.article.currentFeedID = feed_ids;
    locStore.set('trs.last.article.feed_ids', ttrss.currentPage);
    $('#messagelist tbody tr.selected').removeClass('selected');
    $('#messagelist tbody tr.expended').removeClass('expended');
    $('#messagelist tbody tr.focused').removeClass('focused');
    $('#trsHL' + id).addClass('selected expended focused');
    ttrss.scrollToElement(document.getElementById('trsHL' + id), document.getElementById('messagelist-content'));
    ttrss.iswater = false;
    $('#messagecontframe').attr('src', './?_task=ttrss&_action=getArticle&id=' + id);
    var rmid = rcmsg.render(rcmail.gettext('loadarticle', 'ttrss'), 'loading');
    $('#messagecontframe').on('load', function(){
      rcmsg.remove(rmid);
      ttrss.article.loadfunc();
    });
  },
  loadfunc(){
    if(rcmail.env.ttrss_autoread){
      if($('#trsHL' + ttrss.article.currentID).hasClass('unread')) ttrss.article.toggle.read(ttrss.article.currentID, 0, false);
    }
  },
  next: function(){
    var id = $('#messagelist-content tr.focused').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    else id = $('#messagelist-content tr.focused').next('tr').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }else{
      ttrss.article.selectPending = 'next';
      ttrss.headlines.page.next();
    }
  },
  open: function(){
    if(ttrss.article.currentID!==null) window.open('./?_task=ttrss&_action=openLink&id=' + ttrss.article.currentID);
  },
  pageDown: function(){
    var id = $('#messagelist-content tr.focused').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    else{
      var counter = 0;
      id = $('#messagelist-content tr.focused');
      while(id!==undefined&&counter<10){
        id = $(id).next('tr');
        counter++;
      }
      id = $(id).attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    }
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }else{
      ttrss.headlines.page.next();
    }
  },
  pageUp: function(){
    var id = $('#messagelist-content tr.focused').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    else{
      var counter = 0;
      id = $('#messagelist-content tr.focused');
      while(id!==undefined&&counter<10){
        id = $(id).prev('tr');
        counter++;
      }
      id = $(id).attr('id');
      if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
    }
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }
  },
  previous: function(){
    var id = $('#messagelist-content tr.focused').attr('id');
    if(id===undefined) id = $('#messagelist-content tbody tr:last').attr('id');
    else id = $('#messagelist-content tr.focused').prev('tr').attr('id');
    if(id!==undefined){
      id = id.substring(5);
      ttrss.article.load(id);
    }else{
      ttrss.article.selectPending = 'previous';
      ttrss.headlines.page.previous();
    }
  }
};
