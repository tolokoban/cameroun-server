"use strict";

exports.start = onLoad;



var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Splash = require("soin.splash");
var Install = require("tfw.install");
var Dashboard = require("soin.dashboard");
var ViewLogin = require("soin.view.login");


function onLoad() {
  $.registerTheme( "soin", {
    white: "#fda", black: "#420",
    bg0: "#fee7c1",
    bg1: "#feedd0",
    bg2: "#fef2e0",
    bg3: "#fef8ef",
    bgP: "#961", bgS: "#ffa100"  //"#724e10"
  });
  $.applyTheme( "soin" );
  $.addClass( document.body, "thm-bg0" );  
  Install.check("soin", login);
};


function login() {
  var view = ViewLogin.connect();
  var pm = PM( view );
  pm.on( "actionSuccess", loginSuccess );
  pm.on( "actionFailure", loginFailure );
}

function start() {
  Dashboard.refresh().then(function() {
    Splash.open();      
  });
}


function loginSuccess( user ) {
  console.info("[main] user=", user);
  start();
}


function loginFailure( err ) {
  console.info("[main] err=", err);
  alert(err);
}
