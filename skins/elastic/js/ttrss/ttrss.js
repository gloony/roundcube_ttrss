var ttrss = {
  favico: null, notification: null,
  nameurl: '',
  iswater: true,
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
  },
  watermark: function(){
    if(!ttrss.iswater){
      ttrss.article.currentID = null;
      rcmail.enable_command('nextarticle', false);
      rcmail.enable_command('previousarticle', false);
      rcmail.enable_command('open', false);
      rcmail.enable_command('forward', false);
      ttrss.iswater = true;
      $('#messagecontframe').attr('src', './plugins/ttrss/skins/elastic/templates/watermark.html');
    }
  }
};