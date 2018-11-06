ttrss.headlines = {
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
};