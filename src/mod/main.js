"use strict";

exports.start = onLoad;



var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Splash = require("soin.splash");
var Dialog = require("soin.dialog");
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


function loginFailure( result ) {
  console.info("[main] result=", result);
  var errorId = result && result.error ? result.error.id : 0;
  if( errorId === -5 ) {
    // Bad login/password.
    Dialog.confirm({
      title: _('login'),
      confirm: _('send-pwd'),
      content: _('forgotten-password'),
      action: askForPassword.bind( null, result.login ),
      cancel: login
    });
  }
  else {
    alert(JSON.stringify(result, null, "  "));
    location.reload();
  }
}


function askForPassword( login ) {
  Dialog.wait( _('asking-pwd'), WS.get('soin.AskPwd', { login: login }) ).then(
    function( result ) {
      console.info("[main] result=", result);
      switch( result ) {
      case -2: badLogin(_('unknown-login', login)); break;
      case -3: badLogin(_('missing-email')); break;
      default: location.reload();
      }
    },
    function( error ) {
      console.error(error);
    }
  );
}


function badLogin( msg ) {
  Dialog.alert({
    content: msg,
    action: function() {
      window.setTimeout(function() {
        location.reload();        
      }, 500);
    }
  });
}
