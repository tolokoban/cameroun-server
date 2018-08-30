// Code behind.
"use strict";

var CODE_BEHIND = {
  onEdit: onEdit,
  onDelete: onDelete,
  onCode: onCode,
  prependId: prependId,
  onCodeTap: onCodeTap
};


function onEdit() {

}


function onDelete() {
  
}


function onCode() {
  
}


function prependId( v ) {
  return this.id + "-" + v;
}


function onCodeTap() {
  var WebService = require("tfw.web-service");
  WebService.get("synchro", { cmd: 'status', code: this.id + '-' + this.code }).then(
    function( value ) {
      console.info("[soin.view.carecenter-control] value=", value);
    },
    function( err ) {
      console.error(err);
    }
  );
}
