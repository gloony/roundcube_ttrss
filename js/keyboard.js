var keyboard = {
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
          default: valuetoreturn = true;
        }
      }else if(event.ctrlKey){
        switch(chCode){
          default: valuetoreturn = true;
        }
      }else{
        switch(chCode){
          case 33: ttrss.article.pageUp(); break //PageUP
          case 34: ttrss.article.pageDown(); break; //PageDown
          case 35: ttrss.article.last(); break; //end
          case 36: ttrss.article.first(); break; //home
          case 37: ttrss.headlines.page.previous(); break; //left
          case 38: ttrss.article.previous(); break; //up
          case 39: ttrss.headlines.page.next(); break; //right
          case 40: ttrss.article.next(); break; //down
          case 82: ttrss.refresh(); break; //r
          default:
            valuetoreturn = true;
        }
      }
    }else{
      valuetoreturn = true;
    } return valuetoreturn;
  }
};

$(function(){
  $('body').on('keydown', function(e){ if(keyboard.onKeyDown(e)){ return true; }else{ e.preventDefault(); return false; } });
});