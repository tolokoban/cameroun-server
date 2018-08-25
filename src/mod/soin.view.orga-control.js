// Code behind.
"use strict";

var CODE_BEHIND = {
  onRename: onRename,
  onDelete: onDelete
};


var Dialog = require("soin.dialog");
var SvcOrga = require("soin.svc-orga");
var OrgaView = require("soin.view.orga");

function onRename() {
  var view = new OrgaView({ name: this.name });
  Dialog.edit({
    title: _("rename"),
    content: view,
    action: doRename.bind( this, this.id )
  });
}


function onDelete() {
  Dialog.edit({
    title: _("delete"),
    content: _("confirm-delete", this.name),
    action: doDelete.bind( this, this.id )
  });
}


function doRename( id, orga ) {
  var that = this;

  var oldName = this.name;
  this.name = orga.name;
  SvcOrga.rename( id, orga.name ).then(function() {}, function( errcode ) {
    that.name = oldName;
    Dialog.alert(_('not-editable'));
  });
}


function doDelete( id ) {
  var that = this;

  Dialog.wait(_('deleting'), SvcOrga.delete( id )).then(function() {
    that.actionDelete = id;
  }, function( errcode ) {
    Dialog.alert(_('not-editable'));
  });
}
