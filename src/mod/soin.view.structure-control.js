"use strict";

var CODE_BEHIND = {
  onRename: onRename,
  onDelete: onDelete,
  onPatient: onPatient,
  onTypes: onTypes,
  onForms: onForms,
  onVaccins: onVaccins,
  onExams: onExams
};



var $ = require("dom");
var Dialog = require("soin.dialog");
var Textarea = require("tfw.view.textarea");
var SvcStructure = require("soin.svc-structure");

function onRename() {

}


function onDelete() {
  
}


function onPatient() {
  editDefinition.call( this, "patient" );
}


function onTypes() {
  editDefinition.call( this, "types" );
}


function onForms() {
  editDefinition.call( this, "forms" );
}


function onVaccins() {
  editDefinition.call( this, "vaccins" );
}


function onExams() {
  editDefinition.call( this, "exams" );
}


function editDefinition( name ) {
  var view = new Textarea({ label: _(name), width: '100%', height: '100%' });
  $.addClass( view, "soin-view-structure-control" );
  Dialog.edit({
    title: _(name),
    confirm: _('ok'),
    content: view,
    action: saveDefinition.bind( this, name )
  });
  Dialog.wait(_('loading'), SvcStructure.getDef( name, this.id )).then(
    function( value ) {
      view.value = value;
      view.focus = true;
    }
  );
}


function saveDefinition( name, textarea ) {
  SvcStructure.setDef( name, this.id, textarea.value );
}
