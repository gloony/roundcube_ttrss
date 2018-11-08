var rcmsg = {
  counter: 0,
  render: function(text, mode){
    var type = 'notice', type2 = 'info';
    switch(mode){
      case 'success': type = 'confirmation'; type2 = 'success'; break;
      case 'loading': type = 'loading'; type2 = 'loading'; break;
      case 'warning': type = 'warning'; type2 = 'warning'; break;
    }
    this.counter++;
    var html = '<div id="' + this.counter + '" class="' + type + ' content ui alert alert-' + type2 + '" role="alert">';
    html += '<i class="icon"></i>';
    html += '<span>' + text + '</span>';
    html += '</div>';
    $('#messagestack').append(html);
    return this.counter;
  },
  remove: function(id){
    $('#messagestack #' + id).remove();
    if($('#messagestack').html()==='') this.counter = 0;
  }
};