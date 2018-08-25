"use strict";

/**
 * @param {string} args.title
 * @param {object} args.content
 * @param {function} args.action
 * @param {string} args.icon
 */
exports.edit = edit;
/**
 * @param {string} args
 * @param {function} args.action
 * @param {string} args.content
 */
exports.alert = alert;
/**
 * @param {string} message
 * @param {promise=undefined} promise
 */
exports.wait = wait;

//############################################################

var $ = require("dom");
var Wait = require("wdg.wait");
var Button = require("tfw.view.button");
var Dialog = require("wdg.modal");


function edit(args) {
  if( typeof args !== 'object' || Array.isArray(args)) args = {};
  if( typeof args.content === 'undefined' ) throw "[soin.dialog/edit] Missing argument { content:... }!";
  if( typeof args.title !== 'string' ) throw "[soin.dialog/edit] Missing argument { title:<string> }!";
  if( typeof args.action !== 'function' ) throw "[soin.dialog/edit] Missing argument { action:<function> }!";
  
  var btnOk = new Button({ text: args.title, type: 'secondary', icon: args.icon, enabled: false });
  var btnCancel = new Button({ text: _("cancel"), flat: true });
  var dialog = new Dialog({
    header: args.title,
    content: args.content,
    footer: [btnCancel, btnOk]
  });
  btnOk.on(function() {
    dialog.detach();
    try {
      if( typeof args.action === 'function' ) args.action( args.content );
    }
    catch( ex ) { console.error( "Failure in confirmation function!", ex ); }
  });
  btnCancel.on(function() {
    dialog.detach();
    try {
      if( typeof args.cancel === 'function' ) args.cancel( args.content );
    }
    catch( ex ) { console.error( "Failure in cancel function!", ex ); }
  });
  dialog.attach();
  window.setTimeout(function() {
    btnOk.enabled = true;
  }, 200);
  return dialog;
}


function alert( args ) {
  if( typeof args === 'string' ) args = { content: args };

  var btnCancel = new Button({ text: _("gotit"), flat: true });
  var dialog = new Dialog({
    content: args.content,
    footer: [btnCancel]
  });
  dialog.attach();
  btnCancel.on(function() {
    dialog.detach();
    if( typeof args.action === 'function' ) args.action( args.content );
  });
  return dialog;
}


function wait( message, promise ) {
  var dialog = new Dialog({
    content: $.div('soin-dialog-wait', [
      new Wait({ size: 32 }),
      message
    ])
  });
  dialog.attach();
  
  if( promise && typeof promise.then === 'function' ) {
    return new Promise(function (resolve, reject) {
      promise.then(function() {
        var args = Array.prototype.slice.call( arguments );
        dialog.detach();
        resolve.apply(null, args);
      }, function() {
        var args = Array.prototype.slice.call( arguments );
        dialog.detach();
        reject.apply(null, args);
      });
    });
  }
  return dialog;
}
