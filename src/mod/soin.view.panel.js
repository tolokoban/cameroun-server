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
    var lastTime = that._refreshTime;
    if( typeof lastTime !== 'number' ) lastTime = 0;
    var now = Date.now();
    var delay = 200 + lastTime - now;
    // If the refresh take less than  200 ms, we wait. This is because
    // we won't interrupt the animation.
    window.setTimeout( $.removeClass.bind( $, that, 'hide' ), Math.max( 0, delay ) );
  });
}


function refresh() {
  this._refreshTime = Date.now();
  $.addClass( this, "hide" );
  this.content.actionRefresh = 1;
}
