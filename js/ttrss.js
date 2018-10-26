var ttrss = {
	refresh: function(){
		ttrss.loadLastFeeds();
		ttrss.loadLastHeadlines();
	},
	loadLastFeeds: function(){
		// if(locStore.get('ttrss.last.feeds')!==null) ttrss.load.feeds(locStore.get('ttrss.last.feeds'));
		// else ttrss.load.folder();
		ttrss.load.folder();
	},
	loadLastHeadlines: function(){
		if(locStore.get('ttrss.last.headlines')!==null)
			ttrss.load.headlines(
				locStore.get('ttrss.last.headlines'),
				locStore.get('ttrss.last.headlines.view_mode'),
				locStore.get('ttrss.last.headlines.offset')
			);
	},
	refreshLabels: function(){
		$('#threadselect-add ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=true');
		$('#threadselect-remove ul.toolbarmenu.listing').load('./?_task=ttrss&_action=getLabels&mode=false');
	},
	after: {
		headlines: function(offset){
			$('messagelist-header .toolbar.listing.iconized .threads.select').removeClass('active');
			$('#ttrssPageCounter').val(offset);
			var limit = 50;
			offset = (limit * offset) + 1;
			offset = offset - 50;
			var counter = $('#messagelist tbody tr').length;
			if(counter==0&&offset==1){
				$('#rcmcountdisplay').html('Feeds is empty');
				$('#ttrssPageCounter').val(1);
				$('messagelist-header .toolbar.listing.iconized .button.select').addClass('active');
				$('messagelist-header .toolbar.listing.iconized .threads.select').addClass('active');
			}else{
				$('#rcmcountdisplay').html(offset + ' to ' + (offset + counter - 1));
				$('messagelist-header .toolbar.listing.iconized .button.select').removeClass('active');
			}
		},
		label:{
			show: function(){
				$('.ui-dialog-buttonpane.ui-widget-content.ui-helper-clearfix button.mainaction.save.btn.btn-primary').on('click', function(e){
					alert(1);
					e.preventDefault();
					e.stopPropagation();
					return false;
				});
			}
		}
	},
	load: {
		folder: function(){
			$('#mailboxlist').load('./?_task=ttrss&_action=getTree');
			locStore.set('ttrss.last.feeds', null);
		},
		feeds: function(id){
			$('#mailboxlist').load('./?_task=ttrss&_action=getFeeds&id=' + id);
			locStore.set('ttrss.last.feeds', id);
		},
		headlines: function(id, view_mode, offset){
			if(offset===undefined||offset===null||isNaN(offset)) offset = 1;
			ttrss.currentPage = offset;
			if(view_mode===undefined||view_mode===null) view_mode = '';
			$('#rcmcountdisplay').html('');
			$('#ttrssPageCounter').val('');
			$('#messagelist-content').load('./?_task=ttrss&_action=getHeadlines&id=' + id + '&view_mode=' + view_mode + '&offset=' + offset, function(){ ttrss.after.headlines(offset); });
			locStore.set('ttrss.last.headlines', id);
			locStore.set('ttrss.last.headlines.view_mode', view_mode);
			locStore.set('ttrss.last.headlines.offset', offset);
			$('#mailboxlist .selected').removeClass('selected');
			$('#trsCAT' + id).addClass('selected');
		},
		article: function(id, feed_ids){
			$('#messagelist tbody tr.selected.expended.focused').removeClass('selected expended focused');
			$('#trsHL' + id).addClass('selected expended focused');
			$('#messagecontframe').attr('src', './?_task=ttrss&_action=getArticle&id=' + id);
			$('#trsHL' + id).removeClass('unread');
			$('#messagecontframe').on('load', function(){ ttrss.loadLastFeeds(); });
			locStore.set('trs.last.article.feed_ids', ttrss.currentPage);
		}
	},
	headlines: {
		page:{
			next: function(){
				var offset = parseInt(locStore.get('ttrss.last.headlines.offset'), 10); offset++;
				locStore.set('ttrss.last.headlines.offset', offset);
				ttrss.loadLastHeadlines();
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
		toggle: {
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
		next: function(){
			var id = $('#messagelist-content tr.selected').next('tr').attr('id');
			if(id===undefined) id = $('#messagelist-content tbody tr:first').attr('id');
			id = id.substring(5);
			ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
		},
		previous: function(){
			var id = $('#messagelist-content tr.selected').prev('tr').attr('id');
			if(id===undefined) id = $('#messagelist-content tr:last').attr('id');
			id = id.substring(5);
			ttrss.load.article(id, locStore.get('ttrss.last.article.feed_ids'));
		}
	}
};

$(function(){
	ttrss.refresh();
	ttrss.refreshLabels();
});