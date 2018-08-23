"use strict";

/**
 * @param {string} args.title
 * @param {object} args.content
 * @param {function} args.action
 * @param {string} args.icon
 */
exports.edit = edit;
/**
 * @param {string} message
 */
exports.alert = alert;

//############################################################

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


function alert( message ) {
  var btnCancel = new Button({ text: _("gotit"), flat: true });
  var dialog = new Dialog({
    content: message,
    footer: [btnCancel]
  });
  dialog.attach();
  return dialog;
}
