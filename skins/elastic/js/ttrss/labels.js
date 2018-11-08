ttrss.labels = {
  load: function(){
    var rmid = rcmsg.render('Load label(s) ...', 'loading');
    $('#threadselect-add ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=true', function(){
      rcmsg.remove(rmid);
      ttrss.labels.loadfunc();
    });
    $('#threadselect-remove ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=false');
  },
  loadfunc: function(){
    $('#threadselect-add ul.toolbarmenu li a').each(function(){
      var id = $(this).attr('id');
      id = id.substr(6);
      document.styleSheets[0].addRule('#trsSpCAT' + id + ' a:before','color: ' + $('#trsLBL' + id).css('color') + ';');
    });
  }
};