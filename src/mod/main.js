"use strict";

var Splash = require("soin.splash");
var Install = require("tfw.install");


exports.start = function() {
  close();
  Install.check("cameroun", start);
};


function start() {

}



function open() {
  Splash.open();
  window.setTimeout( close, 3000 );
}

function close() {
  Splash.close();
  window.setTimeout( open, 5000 );
}
