"use strict";

// Start animation to reveal body content.
exports.open = open;
// Start animation to hide body content.
exports.close = close;


//################################################################################

var $ = require("dom");


function open() {
  $.addClass( document.body, "soin-splash-open" );
}


function close() {
  $.removeClass( document.body, "soin-splash-open" );
}
