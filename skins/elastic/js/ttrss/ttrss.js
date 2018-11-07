var ttrss = {
  favico: null,
  nameurl: '',
  refresh: function(){
    ttrss.tree.counters();
    ttrss.headlines.reload();
  },
  load: {
    headlines: function(id, view_mode, offset, is_cat, el){
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