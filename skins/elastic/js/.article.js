var article = {
  onKeyDown: function(event){
    var chCode = ('charCode' in event) ? event.charCode : event.keyCode;
    chCode = event.which ? event.which : event.keyCode ? event.keyCode : 0;
    var valuetoreturn = false;
    var OnElement = 'none';
    var el = document.activeElement;
    if(el&&((el.tagName.toLowerCase()=='input'&&el.type=='text')||el.tagName.toLowerCase()=='textarea')){ OnElement = el.tagName.toLowerCase(); }
    if(chCode==27&&OnElement!='none'){
      if((OnElement=='text'||OnElement=='input')) document.activeElement.blur();
    }else if(OnElement=='none'){
      if(event.ctrlKey&&event.shiftKey){
        switch(chCode){
          default: valuetoreturn = true;
        }
      }else if(event.shiftKey){
        switch(chCode){
          // case 33: ttrss.article.select.upper.pageUp(); break //PageUP
          // case 34: ttrss.article.select.upper.pageDown(); break; //PageDown
          // case 35: ttrss.article.select.upper.last(); break; //end
          // case 36: ttrss.article.select.upper.first(); break; //home
          // case 38: ttrss.article.select.upper.previous(); break; //up
          // case 40: ttrss.article.select.upper.next(); break; //down
          // case 65: ttrss.article.select.all(); break; //a
          // case 82: ttrss.refresh(); break; //r
          // case 83: ttrss.article.toggle.star(null, null, true)(); break; //s
          // case 85: ttrss.article.toggle.read(null, 0, true); break; //u
          default: valuetoreturn = true;
        }
      }else if(event.ctrlKey){
        switch(chCode){
          // case 32: ttrss.article.focus.select(); break; //space
          // case 33: ttrss.article.focus.pageUp(); break //PageUP
          // case 34: ttrss.article.focus.pageDown(); break; //PageDown
          // case 35: ttrss.article.focus.last(); break; //end
          // case 36: ttrss.article.focus.first(); break; //home
          // case 38: ttrss.article.focus.previous(); break; //up
          // case 40: ttrss.article.focus.next(); break; //down
          // case 65: ttrss.article.select.all(); break; //a
          // case 82: ttrss.refresh(); break; //r
          // case 83: ttrss.article.toggle.star(null, null, true)(); break; //s
          // case 85: ttrss.article.toggle.read(null, 1, true); break; //u
          default: valuetoreturn = true;
        }
      }else{
        switch(chCode){
          case 27: //escape
            if(top.$('body>#layout>div.content .iframe-wrapper').css('position')=='fixed') top.ttrss.article.toggle.fullscreen(false);
            else{
              if(top.ttrss.iswater) top.ttrss.article.select.none();
              else top.ttrss.watermark();
            }
            break;
          // case 32: ttrss.article.focus.open(); break; //space
          // case 33: ttrss.article.pageUp(); break //PageUP
          // case 34: ttrss.article.pageDown(); break; //PageDown
          // case 35: ttrss.article.last(); break; //end
          // case 36: ttrss.article.first(); break; //home
          case 37: top.ttrss.article.previous(); break; //left
          // case 38: ttrss.article.previous(); break; //up
          case 39: top.ttrss.article.next(); break; //right
          // case 40: ttrss.article.next(); break; //down
          // case 65: ttrss.article.select.all(); break; //a
          // case 82: ttrss.refresh(); break; //r
          // case 83: ttrss.article.toggle.star(null, null, true)(); break; //s
          // case 85: ttrss.article.toggle.read(null, 2, true); break; //u
          case 122: top.ttrss.article.toggle.fullscreen(); break; //F11
          default: valuetoreturn = true;
        }
      }
    }else{
      valuetoreturn = true;
    } return valuetoreturn;
  }
};

// window.addEventListener('keydown', article.onKeyDown, false);