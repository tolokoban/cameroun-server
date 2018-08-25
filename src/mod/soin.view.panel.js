// Code behind.
"use strict";

var CODE_BEHIND = {
  refresh: refresh,
  onContentChanged: onContentChanged
};


var $ = require("dom");
var PM = require("tfw.binding.property-manager");


function onContentChanged( content ) {
  var that = this;

  PM( content ).on('actionUptodate', function() {
    $.removeClass( that, 'hide' );
  });
  this.refresh();
}


function refresh() {
  $.addClass( this, "hide" );
  this.content.actionRefresh = 1;
}
