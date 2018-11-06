ttrss.folder = {
  load: function(){
    $('#mailboxlist').load('./?_task=ttrss&_action=getTree', ttrss.folder.loadfunc);
    locStore.unset('ttrss.last.feeds');
  },
  loadfunc: function(){
    ttrss.loadExpendedFeed();
    $('#mailboxlist #' + locStore.get('ttrss.last.headlines.el')).addClass('selected');
  }
};