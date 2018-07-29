"use strict";

var CODE_BEHIND = {
  connect: connect,
  onClick: onClick
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
  var pm = PM( view );
  pm.on( "actionSuccess", function() { view.show = false; });
  pm.on( "actionFailure", function() { view.show = false; });
  return view;
}


function onClick() {
  var that = this;

  WS.login( this.login, this.password ).then(
    function( user ) {
      that.actionSuccess = user;      
    },
    function( err ) {
      that.actionFailure = err;
    }
  );
}
