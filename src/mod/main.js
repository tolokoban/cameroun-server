"use strict";

var $ = require("dom");
var Splash = require("soin.splash");
var Install = require("tfw.install");


exports.start = function() {
  $.registerTheme( "soin", {
    bgP: "#630", bgS: "#420"
  });
  $.applyTheme( "soin" );
  
  Install.check("cameroun", start);
};


function start() {
  close();
}



function open() {
  Splash.open();
  window.setTimeout( close, 3000 );
}

function close() {
  Splash.close();
  window.setTimeout( open, 5000 );
}
