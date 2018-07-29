// Code behind.
"use strict";

var CODE_BEHIND = {
  init: init,
  logout: logout
};


var WS = require("tfw.web-service");


function init() {
  this.userName = WS.userName;
}

function logout() {
  this.actionLogout = 1;
  WS.logout();
}
