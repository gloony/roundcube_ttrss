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
    add: {
      show: function(){
        var content = $('#subscribe-menu'),
            width = content.width() + 25,
            dialog = content.clone(true);
        $('#subscribe-cat', dialog).val(0);
        $('select', dialog).each(function() { this.id = this.id + '-clone'; });
        $('label', dialog).each(function() { $(this).attr('for', $(this).attr('for') + '-clone'); });
        var save_func = function(e){
          if (rcube_event.is_keyboard(e.originalEvent)){
            $('#subscribe-menu').focus();
          }
          var url = encodeURIComponent($('#subscribe-url', dialog).val()),
              cat = $('select[name="subcat"]', dialog).val();
          var rmid = rcmsg.render(rcmail.gettext('loadsubscribe', 'ttrss'), 'loading');
          $.ajax({ url: './?_task=ttrss&_action=subscribeToFeed&feed_url=' + url + '&category_id=' + cat })
            .done(function(html){ rcmsg.remove(rmid); ttrss.tree.load(); });
          return true;
        };
        dialog = rcmail.simple_dialog(dialog, rcmail.gettext('subscribe', 'ttrss'), save_func, {
          closeOnEscape: true,
          minWidth: 400
        });
      }
    },
    remove: function(){
      var id = $('#mailboxlist li.selected').data('id');
      var rmid = rcmsg.render(rcmail.gettext('loadunsubscribe', 'ttrss'), 'loading');
      $.ajax({ url: './?_task=ttrss&_action=unsubscribeFeed&feed_id=' + id }).done(function(html){
        rcmsg.remove(rmid);
        locStore.unset('ttrss.last.headlines');
        ttrss.headlines.load(0);
        ttrss.tree.load();
      });
    }
  }
};