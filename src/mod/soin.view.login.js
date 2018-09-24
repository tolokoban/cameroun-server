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
  var close = function() { view.show = false; };
  pm.on( "actionSuccess", close );
  pm.on( "actionFailure", close );
  return view;
}


function onClick() {
  var that = this;

  WS.login( this.login, this.password ).then(
    function( user ) {
      that.actionSuccess = user;
    },
    function( err ) {
      that.actionFailure = { login: that.login, error: err };
    }
  );
}
