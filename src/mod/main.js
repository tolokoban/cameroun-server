"use strict";

var $ = require("dom");
var Splash = require("soin.splash");
var Install = require("tfw.install");
var ViewLogin = require("soin.view.login");


exports.start = function() {
  $.registerTheme( "soin", {
    white: "#fda", black: "#420",
    bg0: "#fee7c1",
    bg1: "#feedd0",
    bg2: "#fef2e0",
    bg3: "#fef8ef",
    bgP: "#961", bgS: "#724e10"
  });
  $.applyTheme( "soin" );
  $.addClass( document.body, "thm-bg0" );
  
  Install.check("soin", login);
};


function login() {
  ViewLogin.connect();
}

function start() {
  Splash.open();
}
