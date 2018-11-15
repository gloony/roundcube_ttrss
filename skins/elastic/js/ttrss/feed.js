ttrss.feed = {
  collapse: function(id, force){
    if(!isNaN(id)) return;
    if($('#' + id + ' div.treetoggle')===undefined) return;
    if(force===undefined) force = $('#' + id + '>div.treetoggle').hasClass('collapsed');
    var cur = locStore.get('ttrss.feed.expended');
    var find = id + ', ';
    if(force){
      $('#' + id + '>div.treetoggle').removeClass('collapsed');
      $('#' + id + '>div.treetoggle').addClass('expanded');
      $('#' + id + '>ul#sub' + id).removeClass('hidden');
      $('#' + id).attr("aria-expanded", "true");
      if(cur===null) cur = find;
      else if(cur.search(find)==-1) cur += find;
      if(locStore.get('ttrss.feed.expended')!=cur&&cur!==null) locStore.set('ttrss.feed.expended', cur);
    }else{
      $('#' + id + '>div.treetoggle').addClass('collapsed');
      $('#' + id + '>div.treetoggle').removeClass('expanded');
      $('#' + id + '>ul#sub' + id).addClass('hidden');
      $('#' + id).attr("aria-expanded", "false");
      var reg = new RegExp(find, 'g');
      cur = cur.replace(reg, '');
      if(cur==='') locStore.unset('ttrss.feed.expended');
      else locStore.set('ttrss.feed.expended', cur);
    }
  },
  subscription: {
    add: function(){
      UI.listoptions();
    },
    remove: function(){
      
      alert($('#mailboxlist li.selected').data('id'));
    }
  }
};