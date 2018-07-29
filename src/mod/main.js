"use strict";

var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Panel = require("soin.view.panel");
var Logout = require("soin.view.panel.logout");
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
  var view = ViewLogin.connect();
  var pm = PM( view );
  pm.on( "actionSuccess", loginSuccess );
  pm.on( "actionFailure", loginFailure );
}

function start() {
  removeAllPanels();
  addLogout();
  Splash.open();  
}


function loginSuccess( user ) {
  console.info("[main] user=", user);
  start();
}


function loginFailure( err ) {
  console.info("[main] err=", err);
  alert(err);
}


function addLogout() {
  var logout = new Logout();
  PM( logout ).on( "actionLogout", function() {
    Splash.close();
    window.setTimeout(function() {
      login();
    }, 1000);
  });
  var panel = new Panel({ content: logout });
  $.add( document.body, panel );
}


function removeAllPanels() {
  var panels = document.querySelectorAll("div.soin-view-panel");
  for( var i=0; i<panels.length; i++ ) {
    $.detach( panels[i] );
  }
}
