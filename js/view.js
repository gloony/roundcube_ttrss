var trs = {
	loadLastFeeds: function(){
		if(locStore.get('trs.last.feeds')!==null) trs.load.feeds(locStore.get('trs.last.feeds'));
		else trs.load.folder();
	},
	loadLastHeadlines: function(){
		if(locStore.get('trs.last.headlines')!==null) trs.load.headlines(locStore.get('trs.last.headlines'));
	},
	load: {
		folder: function(){
			$('#mailboxlist').load('./?_task=ttrss&_action=getTree');
			locStore.set('trs.last.feeds', null);
		},
		feeds: function(id){
			$('#mailboxlist').load('./?_task=ttrss&_action=getFeeds&id=' + id);
			locStore.set('trs.last.feeds', id);
		},
		headlines: function(id, view_mode){
			if(view_mode===undefined) view_mode = '';
			$('#messagelist-content').load('./?_task=ttrss&_action=getHeadlines&id=' + id + '&view_mode=' + view_mode);
			locStore.set('trs.last.headlines', id);
		},
		article: function(id, feed_ids){
			$('#messagecontframe').attr('src', './?_task=ttrss&_action=getArticle&id=' + id);
			$('#trsHL' + id).removeClass('unread');
			$('#messagecontframe').on('load', function(){ trs.loadLastFeeds(); });
		}
	}
};

$(function(){
	// $('.header-title.username').html(rcmail.env.ttrss_username);
	trs.loadLastFeeds();
	trs.loadLastHeadlines();
});