var ttrss = {
  favico: null,
  nameurl: '',
  refresh: function(){
    ttrss.tree.counters();
    ttrss.headlines.reload();
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