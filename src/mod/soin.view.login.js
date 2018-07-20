"use strict";

var CODE_BEHIND = {
  connect: connect
};

var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");

/**
 * Static method.
 * `this` is the current class.
 */
function connect() {
  var view = new this();
  $.add( document.body, view );
  window.setTimeout(function() {
    view.show = true;
  }, 300);
  PM( view ).on( "action", function() {
    view.wait = true;
  });
}
